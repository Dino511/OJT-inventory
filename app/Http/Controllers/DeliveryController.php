<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Support\DeliveryFeed;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = DeliveryFeed::all();

        return view('deliveries.index', compact('deliveries'));
    }

    /**
     * Same feed as index(), streamed as a CSV (opens directly in Excel) —
     * matches the export pattern already used by Activity Log.
     */
    public function export()
    {
        $deliveries = DeliveryFeed::all();
        $filename = 'delivery-tracking-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($deliveries) {
            $handle = fopen('php://output', 'w');

            // Excel needs a UTF-8 BOM to render special characters correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Type', 'Reference', 'Supplier / Source', 'Company', 'Deliver To', 'Date', 'Expected Delivery', 'Status'], ',', '"', '\\');

            foreach ($deliveries as $delivery) {
                fputcsv($handle, [
                    $delivery['type_label'],
                    $delivery['reference'],
                    $delivery['source_or_supplier'],
                    $delivery['company'],
                    $delivery['destination'],
                    $delivery['date']?->format('Y-m-d') ?? '',
                    $delivery['expected_delivery_date']?->format('Y-m-d') ?? '',
                    ucfirst($delivery['status']),
                ], ',', '"', '\\');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        return view('deliveries.edit', compact('purchaseOrder'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
        ]);

        $purchaseOrder->update($validated);

        return redirect()->route('deliveries.index')
            ->with('success', 'Delivery dates updated successfully.');
    }
}
