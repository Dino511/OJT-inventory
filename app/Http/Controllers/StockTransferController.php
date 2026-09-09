<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryActivityLog;
use App\Models\StockTransfer;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['company', 'toLocation', 'creator', 'receiver', 'items.product', 'items.fromLocation']);

        $activityLogs = InventoryActivityLog::whereIn('action', ['transfer_dispatched', 'transfer_received', 'transfer_cancelled'])
            ->get()
            ->filter(fn ($log) => ($log->changes['stock_transfer_id'] ?? null) == $stockTransfer->id)
            ->sortByDesc('created_at');

        return view('stock_transfers.show', compact('stockTransfer', 'activityLogs'));
    }

    public function receive(StockTransfer $stockTransfer)
    {
        if (! $stockTransfer->isPending()) {
            return back()->with('error', 'Only a pending transfer can be received.');
        }

        DB::transaction(function () use ($stockTransfer) {
            $stockTransfer->load('items.product');

            foreach ($stockTransfer->items as $item) {
                $destination = Inventory::where('product_id', $item->product_id)
                    ->where('location_id', $stockTransfer->to_location_id)
                    ->lockForUpdate()
                    ->first();

                $destinationQuantityBefore = $destination->quantity ?? 0;

                // Quiet mutation: receiving is logged as a single clear
                // "transfer_received" entry below.
                if ($destination) {
                    $destination->incrementQuietly('quantity', $item->quantity);
                } else {
                    $destination = new Inventory([
                        'product_id' => $item->product_id,
                        'location_id' => $stockTransfer->to_location_id,
                        'quantity' => $item->quantity,
                    ]);
                    $destination->saveQuietly();
                }

                InventoryActivityLog::create([
                    'inventory_id' => $destination->id,
                    'product_name' => $item->product->name ?? 'N/A',
                    'product_code' => $item->product->code ?? null,
                    'location_name' => $stockTransfer->toLocation->name ?? 'N/A',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()?->name ?? 'System',
                    'action' => 'transfer_received',
                    'changes' => [
                        'stock_transfer_id' => $stockTransfer->id,
                        'quantity' => $item->quantity,
                        'destination_quantity_before' => $destinationQuantityBefore,
                        'destination_quantity_after' => $destination->quantity,
                    ],
                ]);
            }

            $stockTransfer->update([
                'status' => StockTransfer::STATUS_RECEIVED,
                'received_by' => Auth::id(),
                'received_at' => now(),
            ]);
        });

        return redirect()->route('stock-transfers.show', $stockTransfer)
            ->with('success', 'Transfer received — store stock has been updated.');
    }

    public function cancel(StockTransfer $stockTransfer)
    {
        if (! $stockTransfer->isPending()) {
            return back()->with('error', 'Only a pending transfer can be cancelled.');
        }

        DB::transaction(function () use ($stockTransfer) {
            $stockTransfer->load('items.product.company', 'items.fromLocation');

            foreach ($stockTransfer->items as $item) {
                $source = Inventory::where('product_id', $item->product_id)
                    ->where('location_id', $item->from_location_id)
                    ->lockForUpdate()
                    ->first();

                $sourceQuantityBefore = $source->quantity ?? 0;

                // Quiet mutation: returning the stock to the warehouse is
                // logged as a single clear "transfer_cancelled" entry below.
                if ($source) {
                    $source->incrementQuietly('quantity', $item->quantity);
                } else {
                    $source = new Inventory([
                        'product_id' => $item->product_id,
                        'location_id' => $item->from_location_id,
                        'quantity' => $item->quantity,
                    ]);
                    $source->saveQuietly();
                }

                InventoryActivityLog::create([
                    'inventory_id' => $source->id,
                    'product_name' => $item->product->name ?? 'N/A',
                    'product_code' => $item->product->code ?? null,
                    'location_name' => $item->fromLocation->name ?? 'N/A',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()?->name ?? 'System',
                    'action' => 'transfer_cancelled',
                    'changes' => [
                        'stock_transfer_id' => $stockTransfer->id,
                        'quantity' => $item->quantity,
                        'source_quantity_before' => $sourceQuantityBefore,
                        'source_quantity_after' => $source->quantity,
                    ],
                ]);
            }

            $stockTransfer->update(['status' => StockTransfer::STATUS_CANCELLED]);
        });

        return redirect()->route('stock-transfers.show', $stockTransfer)
            ->with('success', 'Transfer cancelled — stock has been returned to the warehouse.');
    }

    /**
     * Super Admin only (route-gated). A pending transfer must still be
     * cancelled first — that's not a permission rule but a data-integrity
     * one: its dispatch already decremented the warehouse, and deleting the
     * record directly would leave that stock permanently missing with no
     * way to reverse it. A received (or cancelled) transfer has nothing
     * left to reverse, so it can be deleted outright.
     */
    public function destroy(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->isPending()) {
            return back()->with('error', 'Cancel this transfer first, to return its stock to the warehouse, before deleting it.');
        }

        try {
            $stockTransfer->delete();

            return redirect()->route('deliveries.index')->with('success', 'Transfer deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'REFERENCE constraint')) {
                return back()->with('error', 'Cannot delete this transfer because it is still referenced elsewhere.');
            }

            throw $e;
        }
    }
}
