<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Inventory;
use App\Models\InventoryActivityLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['company', 'location', 'items'])
            ->orderByDesc('created_at')
            ->get();

        return view('purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $locations = Location::where('type', Location::TYPE_WAREHOUSE)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchase_orders.create', compact('companies', 'locations', 'products', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', Rule::exists('companies', 'company_id')],
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')
                    ->where('company_id', $request->input('company_id'))
                    ->where('type', Location::TYPE_WAREHOUSE),
            ],
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')],
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'product_id' => 'required|array|min:1',
            'product_id.*' => ['required', Rule::exists('products', 'id')->where('company_id', $request->input('company_id'))],
            'quantity' => 'required|array|size:' . count($request->input('product_id', [])),
            'quantity.*' => 'required|integer|min:1',
            'unit_cost' => 'required|array|size:' . count($request->input('product_id', [])),
            'unit_cost.*' => 'nullable|numeric|min:0',
        ]);

        $purchaseOrder = DB::transaction(function () use ($validated) {
            $supplier = Supplier::findOrFail($validated['supplier_id']);

            $purchaseOrder = PurchaseOrder::create([
                'company_id' => $validated['company_id'],
                'location_id' => $validated['location_id'],
                'supplier_id' => $supplier->id,
                // Kept in sync for the existing list/badge views that read
                // this plain text column directly.
                'supplier_name' => $supplier->name,
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['product_id'] as $i => $productId) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $productId,
                    'quantity' => $validated['quantity'][$i],
                    'unit_cost' => $validated['unit_cost'][$i] ?? null,
                ]);
            }

            return $purchaseOrder;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['company', 'location', 'creator', 'receiver', 'supplier', 'items.product']);

        // The stock-history entries this order produced when it was
        // received (one per line item) — filtered in PHP rather than a JSON
        // DB query, since `changes` is a small denormalized blob and this
        // keeps the query portable across the app's sqlsrv connection.
        $activityLogs = InventoryActivityLog::where('action', 'purchase_received')
            ->get()
            ->filter(fn ($log) => ($log->changes['purchase_order_id'] ?? null) == $purchaseOrder->id)
            ->sortByDesc('created_at');

        return view('purchase_orders.show', compact('purchaseOrder', 'activityLogs'));
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        if (! $purchaseOrder->isPending()) {
            return back()->with('error', 'Only a pending purchase order can be received.');
        }

        if (! $purchaseOrder->location?->isWarehouse()) {
            return back()->with('error', 'This order\'s delivery location is not a warehouse — stock cannot be received into it.');
        }

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->load('items.product');

            foreach ($purchaseOrder->items as $item) {
                $destination = Inventory::where('product_id', $item->product_id)
                    ->where('location_id', $purchaseOrder->location_id)
                    ->lockForUpdate()
                    ->first();

                $destinationQuantityBefore = $destination->quantity ?? 0;

                // Quiet mutation here: this receipt is logged as a single
                // clear "purchase_received" entry below, not as a generic
                // quantity-changed entry from the normal update observer.
                if ($destination) {
                    $destination->incrementQuietly('quantity', $item->quantity);
                } else {
                    $destination = new Inventory([
                        'product_id' => $item->product_id,
                        'location_id' => $purchaseOrder->location_id,
                        'quantity' => $item->quantity,
                    ]);
                    $destination->saveQuietly();
                }

                InventoryActivityLog::create([
                    'inventory_id' => $destination->id,
                    'product_name' => $item->product->name ?? 'N/A',
                    'product_code' => $item->product->code ?? null,
                    'location_name' => $purchaseOrder->location->name ?? 'N/A',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()?->name ?? 'System',
                    'action' => 'purchase_received',
                    'changes' => [
                        'supplier' => $purchaseOrder->supplier_name,
                        'purchase_order_id' => $purchaseOrder->id,
                        'quantity' => $item->quantity,
                        'destination_quantity_before' => $destinationQuantityBefore,
                        'destination_quantity_after' => $destination->quantity,
                    ],
                ]);
            }

            $purchaseOrder->update([
                'status' => PurchaseOrder::STATUS_RECEIVED,
                'received_by' => Auth::id(),
                'received_at' => now(),
            ]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order received — stock has been updated.');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (! $purchaseOrder->isPending()) {
            return back()->with('error', 'Only a pending purchase order can be cancelled.');
        }

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order cancelled.');
    }

    /**
     * Super Admin only (route-gated) — deleting a received order does not
     * touch any stock it already moved, it just removes the order record
     * itself; the InventoryActivityLog entries it produced stay intact.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order deleted successfully.');
    }
}
