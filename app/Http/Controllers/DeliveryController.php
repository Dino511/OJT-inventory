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
