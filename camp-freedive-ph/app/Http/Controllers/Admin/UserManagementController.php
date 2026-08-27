<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display a listing of internal users.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();

        $query = User::query()->latest();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = max(5, min(100, (int) $request->input('per_page', 10)));
        $users = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total' => User::count(),
            'coaches' => User::where('role', 'coach')->count(),
            'admins' => User::whereIn('role', ['admin', 'owner'])->count(),
            'active' => User::where('status', 'active')->count(),
        ];

        return view('admin.users.index', compact('users', 'currentUser', 'stats'));

    }

    /**
     * Provision and store a new internal user account.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();

        // Only Owner can create Admin accounts; Admin can only create Coach accounts
        $allowedRoles = $currentUser->isOwner() ? ['admin', 'coach'] : ['coach'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in($allowedRoles)],
            'temp_password' => ['nullable', 'string', 'min:8'],
        ]);

        $tempPassword = $validated['temp_password'] ?: ('TempPass' . mt_rand(1000, 9999) . '!');

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => 'active',
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        AuditLogger::log(
            'USER_CREATED',
            "New {$user->role} provisioned: {$user->email} (Name: {$user->name}) by {$currentUser->name} ({$currentUser->role})",
            $user,
            $currentUser->name,
            $request
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Account for {$user->name} created successfully! Temporary password: {$tempPassword}");
    }

    /**
     * Show the profile edit form for a user (Admin/Owner-managed only).
     */
    public function edit(User $user): View
    {
        $currentUser = Auth::user();

        // Admins cannot edit other Admins or the Owner
        if (!$currentUser->isOwner() && ($user->isAdmin() || $user->isOwner())) {
            abort(403, 'Admins can only manage Freediving Coach profiles.');
        }

        return view('admin.users.edit', compact('user', 'currentUser'));
    }

    /**
     * Update an internal user's profile details.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        // Admins cannot update other Admins or Owner
        if (!$currentUser->isOwner() && ($user->isAdmin() || $user->isOwner())) {
            abort(403, 'Admins can only manage Freediving Coach profiles.');
        }

        $allowedRoles = $currentUser->isOwner() ? ['owner', 'admin', 'coach'] : ['coach'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in($allowedRoles)],
            'status' => ['required', 'in:active,inactive'],
            'new_password' => ['nullable', 'string', 'min:8'],
        ]);

        $changes = [];
        if ($user->name !== $validated['name']) $changes[] = "Name: {$user->name} → {$validated['name']}";
        if ($user->email !== $validated['email']) $changes[] = "Email: {$user->email} → {$validated['email']}";
        if ($user->role !== $validated['role']) $changes[] = "Role: {$user->role} → {$validated['role']}";
        if ($user->status !== $validated['status']) $changes[] = "Status: {$user->status} → {$validated['status']}";

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => $validated['status'],
        ];

        if (!empty($validated['new_password'])) {
            $updateData['password'] = Hash::make($validated['new_password']);
            $updateData['must_change_password'] = true;
            $changes[] = "Password reset by admin (temporary password assigned)";
        }

        $user->update($updateData);

        $changeSummary = !empty($changes) ? implode(', ', $changes) : 'No field changes';

        AuditLogger::log(
            'USER_UPDATED',
            "Profile updated for {$user->email} by {$currentUser->name}: {$changeSummary}",
            $user,
            $currentUser->name,
            $request
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Profile for {$user->name} updated successfully.");
    }

    /**
     * Toggle active/inactive account status.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        // Prevent modifying self status
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        // Admins cannot toggle status of other Admins or Owner
        if (!$currentUser->isOwner() && ($user->isAdmin() || $user->isOwner())) {
            abort(403, 'Admins can only toggle status of Coach accounts.');
        }

        $newStatus = $user->isActive() ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        AuditLogger::log(
            'USER_STATUS_TOGGLED',
            "Status changed to {$newStatus} for user: {$user->email} by {$currentUser->name}",
            $user,
            $currentUser->name,
            $request
        );

        return back()->with('success', "Account {$user->name} is now {$newStatus}.");
    }

    /**
     * Delete an internal user account (Owner only).
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        // Only Owner can delete accounts
        if (!$currentUser->isOwner()) {
            abort(403, 'Only the Camp Owner can delete user accounts.');
        }

        // Prevent self-deletion
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $userEmail = $user->email;
        $userName = $user->name;
        $userRole = $user->role;

        $user->delete();

        AuditLogger::log(
            'USER_DELETED',
            "User account permanently deleted: {$userEmail} ({$userName}, Role: {$userRole}) by {$currentUser->name}",
            null,
            $currentUser->name,
            $request
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Account for {$userName} ({$userEmail}) has been deleted.");
    }
}
