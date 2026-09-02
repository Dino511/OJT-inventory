<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::orderBy('name')->get();
        $selectedCompanyId = $request->query('company_id') ? (int) $request->query('company_id') : null;

        $totals = [
            'companies' => $companies->count(),
            'products' => Product::count(),
            'stock_units' => (int) Inventory::sum('quantity'),
        ];

        // Every registered company, with its product count and total stock
        // units — a LEFT JOIN so companies with no products/stock yet still
        // show up (with zeros) instead of silently disappearing.
        $stockByCompany = DB::table('companies')
            ->leftJoin('products', 'products.company_id', '=', 'companies.company_id')
            ->leftJoin('inventories', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('companies.company_id as company_id, companies.name as label, '
                .'COUNT(DISTINCT products.id) as product_count, '
                .'COALESCE(SUM(inventories.quantity), 0) as total_quantity')
            ->groupBy('companies.company_id', 'companies.name')
            ->orderBy('companies.name')
            ->get();

        // Top products by stock quantity, scoped to the selected company (or all companies)
        $stockByProduct = DB::table('inventories')
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->when($selectedCompanyId, fn ($q) => $q->where('products.company_id', $selectedCompanyId))
            ->selectRaw('products.name as label, SUM(inventories.quantity) as total_quantity')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(15)
            ->get();

        // Product counts by category, scoped to the selected company (or all companies)
        $productsByCategory = DB::table('products')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->when($selectedCompanyId, fn ($q) => $q->where('products.company_id', $selectedCompanyId))
            ->selectRaw("COALESCE(product_categories.name, 'Uncategorized') as label, COUNT(*) as product_count")
            ->groupBy(DB::raw("COALESCE(product_categories.name, 'Uncategorized')"))
            ->orderByDesc('product_count')
            ->get();

        // --- Sales Report (reads the `sales` table written by the companion POS app) ---
        $locations = Location::orderBy('name')->get();

        $salesCompanyId = $request->query('sales_company_id') ? (int) $request->query('sales_company_id') : null;
        $salesLocationId = $request->query('sales_location_id') ? (int) $request->query('sales_location_id') : null;
        $salesPeriod = in_array($request->query('sales_period'), ['daily', 'monthly', 'yearly'], true)
            ? $request->query('sales_period')
            : 'monthly';

        $salesSeries = $this->buildSalesSeries($salesPeriod, $salesCompanyId, $salesLocationId);
        $salesTotals = [
            'transactions' => $salesSeries->sum('transactions'),
            'revenue' => $salesSeries->sum('revenue'),
        ];

        $periodStart = $this->salesPeriodStart($salesPeriod);

        // Revenue compared across every company, for the current period —
        // the main "which company is performing better" view an admin
        // actually needs, rather than only a single filtered trend line.
        $revenueByCompany = DB::table('companies')
            ->leftJoin('sales', function ($join) use ($periodStart) {
                $join->on('sales.company_id', '=', 'companies.company_id');
                if ($periodStart) {
                    $join->where('sales.created_at', '>=', $periodStart);
                }
            })
            ->selectRaw('companies.company_id as company_id, companies.name as label, '
                .'COUNT(sales.id) as transactions, COALESCE(SUM(sales.total - sales.refunded_amount), 0) as revenue')
            ->groupBy('companies.company_id', 'companies.name')
            ->orderBy('companies.name')
            ->get();

        // Revenue compared across every location — scoped to the selected
        // company (if any) so the comparison stays meaningful.
        $revenueByLocation = DB::table('locations')
            ->leftJoin('sales', function ($join) use ($periodStart) {
                $join->on('sales.location_id', '=', 'locations.id');
                if ($periodStart) {
                    $join->where('sales.created_at', '>=', $periodStart);
                }
            })
            ->when($salesCompanyId, fn ($q) => $q->where('locations.company_id', $salesCompanyId))
            ->selectRaw('locations.id as location_id, locations.name as label, '
                .'COUNT(sales.id) as transactions, COALESCE(SUM(sales.total - sales.refunded_amount), 0) as revenue')
            ->groupBy('locations.id', 'locations.name')
            ->orderBy('locations.name')
            ->get();

        return view('reports.index', compact(
            'companies',
            'selectedCompanyId',
            'totals',
            'stockByCompany',
            'stockByProduct',
            'productsByCategory',
            'locations',
            'salesCompanyId',
            'salesLocationId',
            'salesPeriod',
            'salesSeries',
            'salesTotals',
            'revenueByCompany',
            'revenueByLocation'
        ));
    }

    /**
     * The date cutoff implied by a sales period ('daily' = last 30 days,
     * 'monthly' = last 12 months, 'yearly' = no cutoff, all-time).
     */
    private function salesPeriodStart(string $period): ?Carbon
    {
        return match ($period) {
            'daily' => now()->subDays(29)->startOfDay(),
            'monthly' => now()->subMonths(11)->startOfMonth(),
            default => null,
        };
    }

    /**
     * Net revenue (total minus any refunded amount) and transaction counts
     * from the `sales` table, bucketed by day/month/year and optionally
     * scoped to one company and/or one location.
     */
    private function buildSalesSeries(string $period, ?int $companyId, ?int $locationId)
    {
        $periodStart = $this->salesPeriodStart($period);

        $query = DB::table('sales')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->when($periodStart, fn ($q) => $q->where('created_at', '>=', $periodStart));

        switch ($period) {
            case 'daily':
                $rows = (clone $query)
                    ->selectRaw('CAST(created_at AS DATE) as period_key, COUNT(*) as transactions, '
                        .'SUM(total - refunded_amount) as revenue')
                    ->groupBy(DB::raw('CAST(created_at AS DATE)'))
                    ->orderBy('period_key')
                    ->get();

                return $rows->map(fn ($row) => [
                    'label' => Carbon::parse($row->period_key)->format('M j'),
                    'transactions' => (int) $row->transactions,
                    'revenue' => (float) $row->revenue,
                ]);

            case 'yearly':
                $rows = (clone $query)
                    ->selectRaw('YEAR(created_at) as yr, COUNT(*) as transactions, '
                        .'SUM(total - refunded_amount) as revenue')
                    ->groupBy(DB::raw('YEAR(created_at)'))
                    ->orderBy('yr')
                    ->get();

                return $rows->map(fn ($row) => [
                    'label' => (string) $row->yr,
                    'transactions' => (int) $row->transactions,
                    'revenue' => (float) $row->revenue,
                ]);

            default: // monthly
                $rows = (clone $query)
                    ->selectRaw('YEAR(created_at) as yr, MONTH(created_at) as mo, COUNT(*) as transactions, '
                        .'SUM(total - refunded_amount) as revenue')
                    ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                    ->orderBy('yr')->orderBy('mo')
                    ->get();

                return $rows->map(fn ($row) => [
                    'label' => Carbon::createFromDate((int) $row->yr, (int) $row->mo, 1)->format('M Y'),
                    'transactions' => (int) $row->transactions,
                    'revenue' => (float) $row->revenue,
                ]);
        }
    }
}
