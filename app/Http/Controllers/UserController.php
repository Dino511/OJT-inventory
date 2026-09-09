<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['company', 'companies', 'location'])->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $companies = Company::all();
        $locations = Location::orderBy('name')->get();

        return view('users.create', compact('companies', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in([User::ROLE_SUPER_ADMIN, User::ROLE_BUSINESS_ADMIN, User::ROLE_MODERATOR])],
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,company_id',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        $companyIds = $validated['company_ids'] ?? [];

        if (! empty($validated['location_id']) && ! $this->locationBelongsToCompanies($validated['location_id'], $companyIds)) {
            return back()->withInput()->withErrors([
                'location_id' => 'The selected location must belong to one of the granted companies.',
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'company_id' => $companyIds[0] ?? null,
            'location_id' => $validated['location_id'] ?? null,
        ]);

        $user->companies()->sync($companyIds);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $companies = Company::all();
        $locations = Location::orderBy('name')->get();
        $user->load('companies');

        return view('users.edit', compact('user', 'companies', 'locations'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'alternative_email' => ['nullable', 'required_with:password', 'string', 'email', 'max:255', 'different:email'],
            'avatar' => 'nullable|image|max:4096',
            'remove_avatar' => 'nullable|boolean',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in([User::ROLE_SUPER_ADMIN, User::ROLE_BUSINESS_ADMIN, User::ROLE_MODERATOR])],
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,company_id',
            'location_id' => 'nullable|exists:locations,id',
        ], [
            'alternative_email.required_with' => 'An alternative email is required when setting a new password, so password reset codes have somewhere to go.',
        ]);

        if ($user->id === Auth::id() && $validated['role'] !== User::ROLE_SUPER_ADMIN) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot remove your own Super Admin role while signed in.');
        }

        $companyIds = $validated['company_ids'] ?? [];
        unset($validated['company_ids']);

        if (! empty($validated['location_id']) && ! $this->locationBelongsToCompanies($validated['location_id'], $companyIds)) {
            return back()->withInput()->withErrors([
                'location_id' => 'The selected location must belong to one of the granted companies.',
            ]);
        }

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['alternative_email'] = $validated['alternative_email'] ?? null;

        $removeAvatar = ! empty($validated['remove_avatar']);
        unset($validated['remove_avatar']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($removeAvatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = null;
        } else {
            unset($validated['avatar']);
        }

        // Keep the user's currently active company if it's still among the
        // granted ones, so editing their memberships doesn't silently kick
        // them out of the company they're mid-session in.
        $validated['company_id'] = in_array($user->company_id, $companyIds) ? $user->company_id : ($companyIds[0] ?? null);

        $user->update($validated);
        $user->companies()->sync($companyIds);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Moderators are locked to a single POS location (see User::location());
     * that location must actually belong to one of the companies being
     * granted, or a Super Admin could point them at a location no company
     * of theirs owns.
     */
    private function locationBelongsToCompanies(int $locationId, array $companyIds): bool
    {
        return Location::where('id', $locationId)->whereIn('company_id', $companyIds)->exists();
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'You cannot deactivate your own account while signed in.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return redirect()->route('users.index')
            ->with('success', 'User ' . ($user->is_active ? 'activated' : 'deactivated') . ' successfully.');
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
