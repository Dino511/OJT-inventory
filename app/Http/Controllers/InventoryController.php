<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryActivityLog;
use App\Models\Product;
use App\Models\ProductActivityLog;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $sort = in_array($request->query('sort'), ['product', 'location', 'quantity']) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $query = Inventory::with(['product', 'location']);

        switch ($sort) {
            case 'product':
                $query->orderBy(
                    Product::select('name')->whereColumn('products.id', 'inventories.product_id'),
                    $direction
                );
                break;
            case 'location':
                $query->orderBy(
                    Location::select('name')->whereColumn('locations.id', 'inventories.location_id'),
                    $direction
                );
                break;
            case 'quantity':
                $query->orderBy('quantity', $direction);
                break;
            default:
                $query->orderBy('created_at', $direction);
                break;
        }

        $inventories = $query->get();

        return view('inventory.index', compact('inventories', 'sort', 'direction'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('inventory.create', compact('products', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('inventories')->where(fn ($query) => $query->where('location_id', $request->location_id)),
            ],
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:0',
        ], [
            'product_id.unique' => 'This product already has a stock record at the selected location. Edit that record instead.',
        ]);

        Inventory::create($validated);

        return redirect()->route('inventory.index')->with('success', 'Inventory record added successfully.');
    }

    public function edit(Inventory $inventory)
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('inventory.edit', compact('inventory', 'products', 'locations'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('inventories')
                    ->where(fn ($query) => $query->where('location_id', $request->location_id))
                    ->ignore($inventory->id),
            ],
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:0',
        ], [
            'product_id.unique' => 'This product already has a stock record at the selected location. Edit that record instead.',
        ]);

        $inventory->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Inventory record updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()->route('inventory.index')->with('success', 'Inventory record deleted successfully.');
    }

    public function showTransfer(Inventory $inventory)
    {
        $inventory->load(['product', 'location']);
        $companyId = $inventory->product->company_id;

        // Every location (in the same company) that currently holds stock of
        // this product — these are the valid "From Location" choices.
        $sourceOptions = Inventory::where('product_id', $inventory->product_id)
            ->where('quantity', '>', 0)
            ->whereHas('location', fn ($query) => $query->where('company_id', $companyId))
            ->with('location')
            ->get();

        $locations = Location::where('company_id', $companyId)->orderBy('name')->get();

        return view('inventory.transfer', compact('inventory', 'sourceOptions', 'locations'));
    }

    public function transfer(Request $request, Inventory $inventory)
    {
        $companyId = $inventory->product->company_id;

        $validated = $request->validate([
            'from_location_id' => [
                'required',
                Rule::exists('inventories', 'location_id')->where('product_id', $inventory->product_id),
            ],
            'to_location_id' => [
                'required',
                'exists:locations,id',
                'different:from_location_id',
            ],
            'quantity' => 'required|integer|min:1',
        ], [
            'to_location_id.different' => 'Choose a different location to transfer to.',
        ]);

        $source = Inventory::where('product_id', $inventory->product_id)
            ->where('location_id', $validated['from_location_id'])
            ->whereHas('location', fn ($query) => $query->where('company_id', $companyId))
            ->lockForUpdate()
            ->firstOrFail();

        if ($validated['quantity'] > $source->quantity) {
            return back()->withInput()->withErrors([
                'quantity' => "Only {$source->quantity} units available at the selected location.",
            ]);
        }

        $toLocation = Location::find($validated['to_location_id']);

        DB::transaction(function () use ($source, $validated, $toLocation) {
            $sourceQuantityBefore = $source->quantity;

            $destination = Inventory::where('product_id', $source->product_id)
                ->where('location_id', $validated['to_location_id'])
                ->lockForUpdate()
                ->first();

            $destinationQuantityBefore = $destination->quantity ?? 0;

            // Quiet mutations here: a transfer is logged as a single clear
            // "transferred" entry below, not as two generic quantity-changed
            // entries from the normal created/updated observer.
            $source->decrementQuietly('quantity', $validated['quantity']);

            if ($destination) {
                $destination->incrementQuietly('quantity', $validated['quantity']);
            } else {
                $destination = new Inventory([
                    'product_id' => $source->product_id,
                    'location_id' => $validated['to_location_id'],
                    'quantity' => $validated['quantity'],
                ]);
                $destination->saveQuietly();
            }

            InventoryActivityLog::create([
                'inventory_id' => $source->id,
                'product_name' => $source->product->name ?? 'N/A',
                'product_code' => $source->product->code ?? null,
                'location_name' => $source->location->name ?? 'N/A',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'System',
                'action' => 'transferred',
                'changes' => [
                    'from_location' => $source->location->name ?? 'N/A',
                    'to_location' => $toLocation->name ?? 'N/A',
                    'quantity' => $validated['quantity'],
                    'source_quantity_before' => $sourceQuantityBefore,
                    'source_quantity_after' => $source->quantity,
                    'destination_quantity_before' => $destinationQuantityBefore,
                    'destination_quantity_after' => $destination->quantity,
                ],
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Stock transferred successfully.');
    }

    /**
     * Full activity log across every stock record (including deleted ones),
     * plus every product creation/deletion — so "who added or deleted the
     * product" itself is visible here too, not just stock-quantity changes.
     */
    public function historyIndex()
    {
        $logs = $this->combinedActivityLogs();

        // A log row's own action says nothing about whether that stock
        // record was deleted *later* — check current existence directly so
        // "View record history" never links to a since-deleted record.
        $existingInventoryIds = Inventory::whereIn(
            'id',
            $logs->where('source', 'inventory')->pluck('inventory_id')->filter()->unique()
        )->pluck('id')->all();

        $existingProductIds = Product::whereIn(
            'id',
            $logs->where('source', 'product')->pluck('product_id')->filter()->unique()
        )->pluck('id')->all();

        return view('inventory.history_index', compact('logs', 'existingInventoryIds', 'existingProductIds'));
    }

    /**
     * Merge stock-record activity with product create/delete events into one
     * timeline, newest first. Both sides are normalized to the same shape
     * (source/inventory_id/product_id/product_name/product_code/location_name/
     * user_name/action/changes/created_at) so one Blade partial can render
     * either kind of row.
     */
    private function combinedActivityLogs()
    {
        $inventoryLogs = InventoryActivityLog::get()->map(fn ($log) => (object) [
            'created_at' => $log->created_at,
            'source' => 'inventory',
            'inventory_id' => $log->inventory_id,
            'product_id' => null,
            'product_name' => $log->product_name,
            'product_code' => $log->product_code,
            'location_name' => $log->location_name,
            'user_name' => $log->user_name,
            'action' => $log->action,
            'changes' => $log->changes,
        ]);

        $productLogs = ProductActivityLog::whereIn('action', ['created', 'deleted'])->get()->map(fn ($log) => (object) [
            'created_at' => $log->created_at,
            'source' => 'product',
            'inventory_id' => null,
            'product_id' => $log->product_id,
            'product_name' => $log->product_name,
            'product_code' => $log->product_code,
            'location_name' => null,
            'user_name' => $log->user_name,
            'action' => $log->action,
            'changes' => $log->changes,
        ]);

        return $inventoryLogs->concat($productLogs)->sortByDesc('created_at')->values();
    }

    /**
     * Activity log for a single, still-existing stock record.
     */
    public function history(Inventory $inventory)
    {
        $logs = InventoryActivityLog::where('inventory_id', $inventory->id)
            ->orderByDesc('created_at')
            ->get();

        return view('inventory.history', compact('inventory', 'logs'));
    }

    /**
     * Export the full activity log as a CSV file (opens directly in Excel).
     */
    public function exportHistoryCsv()
    {
        $logs = $this->combinedActivityLogs();

        $filename = 'inventory-activity-log-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // Excel needs a UTF-8 BOM to render special characters correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Date & Time', 'Product', 'Code', 'Location', 'Action', 'User', 'Details'], ',', '"', '\\');

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->product_name,
                    $log->product_code,
                    $log->location_name ?? 'All locations',
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

    private function describeChanges(object $log): string
    {
        if ($log->action === 'created') {
            return $log->source === 'product'
                ? "Product \"{$log->product_name}\" was added to the catalog."
                : "Stock record created for {$log->product_name} at {$log->location_name}.";
        }

        if ($log->action === 'deleted') {
            return $log->source === 'product'
                ? "Product \"{$log->product_name}\" was deleted from the catalog."
                : "Stock record for {$log->product_name} at {$log->location_name} was deleted.";
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
            $label = ucwords(str_replace('_', ' ', $field));
            $parts[] = "{$label}: {$values['old']} -> {$values['new']}";
        }

        return implode('; ', $parts);
    }
}
