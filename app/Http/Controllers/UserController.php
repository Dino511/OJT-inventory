<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['company', 'companies'])->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $companies = Company::all();

        return view('users.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,company_id',
        ]);

        $companyIds = $validated['company_ids'] ?? [];

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $companyIds[0] ?? null,
        ]);

        $user->companies()->sync($companyIds);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $companies = Company::all();
        $user->load('companies');

        return view('users.edit', compact('user', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,company_id',
        ]);

        $companyIds = $validated['company_ids'] ?? [];
        unset($validated['company_ids']);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Keep the user's currently active company if it's still among the
        // granted ones, so editing their memberships doesn't silently kick
        // them out of the company they're mid-session in.
        $validated['company_id'] = in_array($user->company_id, $companyIds) ? $user->company_id : ($companyIds[0] ?? null);

        $user->update($validated);
        $user->companies()->sync($companyIds);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account while signed in.');
        }

        try {
            $user->delete();

            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000' || str_contains($e->getMessage(), 'REFERENCE constraint')) {
                return redirect()->route('users.index')->with('error', 'Cannot delete this user because they have sales or other records associated with their account.');
            }

            throw $e;
        }
    }
}
