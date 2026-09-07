<?php

namespace App\Support;

use App\Models\PurchaseOrder;
use App\Models\StockTransfer;
use Illuminate\Support\Collection;

class DeliveryFeed
{
    /**
     * Every purchase order and warehouse→store stock transfer, normalized
     * into one common shape so both show up together in Delivery Tracking
     * (and the Dashboard's "Pending Deliveries" panel) regardless of which
     * kind of "incoming stock" they represent.
     */
    public static function all(): Collection
    {
        $purchaseOrders = PurchaseOrder::with(['company', 'location'])->get()
            ->map(fn ($order) => [
                'type' => 'purchase_order',
                'type_label' => 'Purchase Order',
                'id' => $order->id,
                'reference' => 'PO #' . $order->id,
                'source_or_supplier' => $order->supplier_name,
                'company' => $order->company->name ?? 'N/A',
                'destination' => $order->location->name ?? 'N/A',
                'date' => $order->order_date,
                'expected_delivery_date' => $order->expected_delivery_date,
                'status' => $order->status,
                'show_route' => route('purchase-orders.show', $order),
                'edit_route' => route('deliveries.edit', $order),
                'model' => $order,
            ]);

        $stockTransfers = StockTransfer::with(['company', 'toLocation', 'items.fromLocation'])->get()
            ->map(function ($transfer) {
                $sourceNames = $transfer->items->pluck('fromLocation.name')->filter()->unique();

                return [
                    'type' => 'stock_transfer',
                    'type_label' => 'Stock Transfer',
                    'id' => $transfer->id,
                    'reference' => 'TRF #' . $transfer->id,
                    'source_or_supplier' => $sourceNames->count() === 1 ? $sourceNames->first() : $sourceNames->implode(', '),
                    'company' => $transfer->company->name ?? 'N/A',
                    'destination' => $transfer->toLocation->name ?? 'N/A',
                    'date' => $transfer->dispatched_at,
                    'expected_delivery_date' => null,
                    'status' => $transfer->status,
                    'show_route' => route('stock-transfers.show', $transfer),
                    'edit_route' => null,
                    'model' => $transfer,
                ];
            });

        // Pending deliveries need action, so they surface first regardless
        // of date — otherwise a same-day pending order gets buried under
        // already-resolved history and looks like it's missing.
        $statusRank = ['pending' => 0, 'received' => 1, 'cancelled' => 2];

        return $purchaseOrders->concat($stockTransfers)
            ->sortBy(fn ($delivery) => [
                $statusRank[$delivery['status']] ?? 99,
                $delivery['date'] ? -$delivery['date']->timestamp : 0,
            ])
            ->values();
    }
}
