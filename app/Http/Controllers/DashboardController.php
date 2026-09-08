<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryActivityLog;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockTransfer;
use App\Support\DeliveryFeed;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_products' => Product::where('is_active', true)->count(),
            'total_stock_units' => (int) Inventory::sum('quantity'),
            'low_stock_count' => $this->lowStockQuery()->count(),
            'pending_purchase_orders' => PurchaseOrder::where('status', PurchaseOrder::STATUS_PENDING)->count(),
            'pending_stock_transfers' => StockTransfer::where('status', StockTransfer::STATUS_PENDING)->count(),
        ];

        $lowStockItems = $this->lowStockQuery()
            ->with(['product', 'location'])
            ->orderByRaw('inventories.quantity - products.reorder_point asc')
            ->limit(8)
            ->get(['inventories.*']);

        $pendingDeliveries = DeliveryFeed::all()
            ->where('status', 'pending')
            ->take(6);

        $recentActivity = InventoryActivityLog::latest('created_at')->limit(8)->get();

        return view('dashboard.index', compact('stats', 'lowStockItems', 'pendingDeliveries', 'recentActivity'));
    }

    /**
     * Inventory rows sitting at or below their product's reorder point.
     * Products without a reorder point set (null or zero) opt out of this
     * check entirely, since "0" isn't a meaningful threshold to alert on.
     */
    private function lowStockQuery()
    {
        return Inventory::query()
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->whereNotNull('products.reorder_point')
            ->where('products.reorder_point', '>', 0)
            ->whereColumn('inventories.quantity', '<=', 'products.reorder_point');
    }
}
