<?php

namespace App\Http\Controllers;

use App\Models\CategoryActivityLog;
use App\Models\CompanyActivityLog;
use App\Models\Inventory;
use App\Models\InventoryActivityLog;
use App\Models\LocationActivityLog;
use App\Models\Product;
use App\Models\ProductActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ActivityLogController extends Controller
{
    /**
     * System-wide activity feed: companies, categories, locations, products,
     * and stock — everything the individual per-module history pages track,
     * merged into one timeline with a daily/weekly/all-time filter.
     */
    public function index(Request $request)
    {
        $period = $this->normalizePeriod($request->query('period'));
        $logs = $this->collectLogs($period);

        $existingProductIds = Product::whereIn(
            'id',
            $logs->where('source', 'product')->pluck('subject_id')->filter()->unique()
        )->pluck('id')->all();

        $existingInventoryIds = Inventory::whereIn(
            'id',
            $logs->where('source', 'inventory')->pluck('subject_id')->filter()->unique()
        )->pluck('id')->all();

        return view('activity_log.index', compact('logs', 'period', 'existingProductIds', 'existingInventoryIds'));
    }

    /**
     * Same feed as index(), streamed as a CSV (opens directly in Excel).
     */
    public function export(Request $request)
    {
        $period = $this->normalizePeriod($request->query('period'));
        $logs = $this->collectLogs($period);

        $label = match ($period) {
            'daily' => 'daily',
            'weekly' => 'weekly',
            default => 'all-time',
        };
        $filename = "activity-log-{$label}-" . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // Excel needs a UTF-8 BOM to render special characters correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Date & Time', 'Type', 'Name', 'Action', 'User', 'Details'], ',', '"', '\\');

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    ucfirst($log->source),
                    $log->subject_name,
                    ucfirst($log->action),
                    $log->user_name ?? 'System',
                    $this->describeChanges($log),
                ], ',', '"', '\\');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function normalizePeriod(?string $period): string
    {
        return in_array($period, ['daily', 'weekly'], true) ? $period : 'all';
    }

    /**
     * Pull every activity-log table, normalize each row to a common shape,
     * apply the period filter, and return one timeline sorted newest first.
     */
    private function collectLogs(string $period): Collection
    {
        $scope = function ($query) use ($period) {
            return match ($period) {
                'daily' => $query->whereDate('created_at', now()->toDateString()),
                'weekly' => $query->where('created_at', '>=', now()->subDays(7)),
                default => $query,
            };
        };

        $companies = $scope(CompanyActivityLog::query())->get()->map(fn ($l) => (object) [
            'created_at' => $l->created_at,
            'source' => 'company',
            'subject_id' => $l->company_id,
            'subject_name' => $l->company_name,
            'user_name' => $l->user_name,
            'action' => $l->action,
            'changes' => $l->changes,
        ]);

        $categories = $scope(CategoryActivityLog::query())->get()->map(fn ($l) => (object) [
            'created_at' => $l->created_at,
            'source' => 'category',
            'subject_id' => $l->category_id,
            'subject_name' => $l->category_name,
            'user_name' => $l->user_name,
            'action' => $l->action,
            'changes' => $l->changes,
        ]);

        $locations = $scope(LocationActivityLog::query())->get()->map(fn ($l) => (object) [
            'created_at' => $l->created_at,
            'source' => 'location',
            'subject_id' => $l->location_id,
            'subject_name' => $l->location_name,
            'user_name' => $l->user_name,
            'action' => $l->action,
            'changes' => $l->changes,
        ]);

        $products = $scope(ProductActivityLog::query())->get()->map(fn ($l) => (object) [
            'created_at' => $l->created_at,
            'source' => 'product',
            'subject_id' => $l->product_id,
            'subject_name' => $l->product_name,
            'user_name' => $l->user_name,
            'action' => $l->action,
            'changes' => $l->changes,
        ]);

        $inventories = $scope(InventoryActivityLog::query())->get()->map(fn ($l) => (object) [
            'created_at' => $l->created_at,
            'source' => 'inventory',
            'subject_id' => $l->inventory_id,
            'subject_name' => "{$l->product_name} @ {$l->location_name}",
            'user_name' => $l->user_name,
            'action' => $l->action,
            'changes' => $l->changes,
        ]);

        return $companies->concat($categories)->concat($locations)->concat($products)->concat($inventories)
            ->sortByDesc('created_at')
            ->values();
    }

    private function describeChanges(object $log): string
    {
        $label = match ($log->source) {
            'company' => 'Company',
            'category' => 'Category',
            'location' => 'Location',
            'product' => 'Product',
            'inventory' => 'Stock record',
            default => 'Item',
        };

        if ($log->action === 'created') {
            return "{$label} \"{$log->subject_name}\" was added.";
        }

        if ($log->action === 'deleted') {
            return "{$label} \"{$log->subject_name}\" was deleted.";
        }

        if (empty($log->changes)) {
            return 'No details recorded.';
        }

        if ($log->action === 'transferred') {
            $c = $log->changes;
            $summary = "Transferred {$c['quantity']} unit(s) from {$c['from_location']} to {$c['to_location']}.";

            if (array_key_exists('source_quantity_before', $c)) {
                $summary .= " {$c['from_location']}: {$c['source_quantity_before']} -> {$c['source_quantity_after']};"
                    . " {$c['to_location']}: {$c['destination_quantity_before']} -> {$c['destination_quantity_after']}.";
            }

            return $summary;
        }

        $parts = [];
        foreach ($log->changes as $field => $values) {
            $fieldLabel = ucwords(str_replace('_', ' ', $field));
            $parts[] = "{$fieldLabel}: {$values['old']} -> {$values['new']}";
        }

        return implode('; ', $parts);
    }
}
