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

    /**
     * Name prefixes offered next to each Contact Person field.
     */
    public const NAME_PREFIXES = ['Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Engr.', 'Atty.'];

    public function index()
    {
        $suppliers = Supplier::withCount([
            'products as active_products_count' => fn ($query) => $query->where('is_active', true),
        ])->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create', [
            'countryCodes' => self::COUNTRY_CODES,
            'namePrefixes' => self::NAME_PREFIXES,
        ]);
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
            'namePrefixes' => self::NAME_PREFIXES,
            'phone' => self::splitPhone($supplier->phone),
            'secondaryPhone' => self::splitPhone($supplier->secondary_phone),
            'contactPerson' => self::splitName($supplier->contact_person),
            'secondaryContactPerson' => self::splitName($supplier->secondary_contact_person),
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
        $prefixes = self::NAME_PREFIXES;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'contact_person_prefix' => ['nullable', 'in:' . implode(',', $prefixes)],
            'contact_person_name' => 'required|string|max:150',
            'secondary_contact_person_prefix' => ['nullable', 'in:' . implode(',', $prefixes)],
            'secondary_contact_person_name' => 'nullable|string|max:150',
            'phone_country_code' => ['required', 'in:' . implode(',', $codes)],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{7,12}$/'],
            'secondary_phone_country_code' => ['nullable', 'in:' . implode(',', $codes)],
            'secondary_phone_number' => ['nullable', 'string', 'regex:/^[0-9]{7,12}$/'],
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:500',
        ], [
            // A flat 9-digit rule only fit the Philippines; the dropdown
            // above offers 16 other country codes (US, UK, Japan, etc.)
            // whose local numbers are commonly 7-12 digits, so the range
            // just needs to reject obvious junk, not enforce one country's
            // exact length on every country.
            'phone_number.regex' => 'The phone number must be 7 to 12 digits (excluding the country code).',
            'secondary_phone_number.regex' => 'The secondary phone number must be 7 to 12 digits (excluding the country code).',
        ]);

        $validated['contact_person'] = trim(($validated['contact_person_prefix'] ?? '') . ' ' . $validated['contact_person_name']);

        $validated['secondary_contact_person'] = ! empty($validated['secondary_contact_person_name'])
            ? trim(($validated['secondary_contact_person_prefix'] ?? '') . ' ' . $validated['secondary_contact_person_name'])
            : null;

        $validated['phone'] = trim($validated['phone_country_code'] . ' ' . $validated['phone_number']);

        $validated['secondary_phone'] = ! empty($validated['secondary_phone_number'])
            ? trim(($validated['secondary_phone_country_code'] ?? '+63') . ' ' . $validated['secondary_phone_number'])
            : null;

        return collect($validated)->except([
            'contact_person_prefix', 'contact_person_name', 'secondary_contact_person_prefix', 'secondary_contact_person_name',
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

    /**
     * Splits a stored "<Prefix> <Name>" string back into its two parts so
     * the edit form can repopulate the prefix dropdown and the plain name
     * input separately. Falls back to no prefix for legacy values saved
     * before the prefix dropdown existed.
     */
    private static function splitName(?string $stored): array
    {
        if (! $stored) {
            return ['prefix' => '', 'name' => ''];
        }

        foreach (self::NAME_PREFIXES as $prefix) {
            if (str_starts_with($stored, $prefix . ' ')) {
                return ['prefix' => $prefix, 'name' => substr($stored, strlen($prefix) + 1)];
            }
        }

        return ['prefix' => '', 'name' => $stored];
    }
}
