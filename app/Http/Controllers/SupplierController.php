<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Country calling codes offered in the phone number dropdowns, PH first
     * since that's this system's home market.
     */
    public const COUNTRY_CODES = [
        '+63' => 'Philippines (+63)',
        '+1' => 'US / Canada (+1)',
        '+44' => 'United Kingdom (+44)',
        '+61' => 'Australia (+61)',
        '+65' => 'Singapore (+65)',
        '+60' => 'Malaysia (+60)',
        '+66' => 'Thailand (+66)',
        '+84' => 'Vietnam (+84)',
        '+62' => 'Indonesia (+62)',
        '+852' => 'Hong Kong (+852)',
        '+81' => 'Japan (+81)',
        '+82' => 'South Korea (+82)',
        '+86' => 'China (+86)',
        '+91' => 'India (+91)',
        '+971' => 'UAE (+971)',
        '+966' => 'Saudi Arabia (+966)',
        '+974' => 'Qatar (+974)',
    ];

    public function index()
    {
        $suppliers = Supplier::orderBy('name')->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create', ['countryCodes' => self::COUNTRY_CODES]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', [
            'supplier' => $supplier,
            'countryCodes' => self::COUNTRY_CODES,
            'phone' => self::splitPhone($supplier->phone),
            'secondaryPhone' => self::splitPhone($supplier->secondary_phone),
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $this->validatePayload($request);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();

            return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'REFERENCE constraint')) {
                return redirect()->route('suppliers.index')
                                 ->with('error', 'Cannot delete this supplier because it is still referenced elsewhere.');
            }

            throw $e;
        }
    }

    /**
     * Validates the shared create/edit payload and combines each phone
     * number's country code + local number into the single string column
     * the database stores (e.g. "+63 9171234567").
     */
    private function validatePayload(Request $request): array
    {
        $codes = array_keys(self::COUNTRY_CODES);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'contact_person' => 'required|string|max:150',
            'secondary_contact_person' => 'nullable|string|max:150',
            'phone_country_code' => ['required', 'in:' . implode(',', $codes)],
            'phone_number' => 'required|string|max:20|regex:/^[0-9\-\s]{6,20}$/',
            'secondary_phone_country_code' => ['nullable', 'in:' . implode(',', $codes)],
            'secondary_phone_number' => 'nullable|string|max:20|regex:/^[0-9\-\s]{6,20}$/',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:500',
        ]);

        $validated['phone'] = trim($validated['phone_country_code'] . ' ' . $validated['phone_number']);

        $validated['secondary_phone'] = ! empty($validated['secondary_phone_number'])
            ? trim(($validated['secondary_phone_country_code'] ?? '+63') . ' ' . $validated['secondary_phone_number'])
            : null;

        return collect($validated)->except([
            'phone_country_code', 'phone_number', 'secondary_phone_country_code', 'secondary_phone_number',
        ])->toArray();
    }

    /**
     * Splits a stored "+<code> <number>" string back into its two parts so
     * the edit form can repopulate the country-code dropdown and the plain
     * number input separately. Falls back to +63 for legacy values saved
     * before the country-code dropdown existed.
     */
    private static function splitPhone(?string $stored): array
    {
        if (! $stored) {
            return ['code' => '+63', 'number' => ''];
        }

        foreach (array_keys(self::COUNTRY_CODES) as $code) {
            if (str_starts_with($stored, $code . ' ')) {
                return ['code' => $code, 'number' => substr($stored, strlen($code) + 1)];
            }
        }

        return ['code' => '+63', 'number' => $stored];
    }
}
