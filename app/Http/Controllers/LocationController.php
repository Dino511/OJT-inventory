<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Company;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::with('company')->latest()->get();
        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('locations.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'  => 'required|exists:companies,company_id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500', // <-- Added here
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')->with('success', 'Location created successfully.');
    }

    public function edit(Location $location)
    {
        $companies = Company::all();
        return view('locations.edit', compact('location', 'companies'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'company_id'  => 'required|exists:companies,company_id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500', // <-- Added here
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }
}