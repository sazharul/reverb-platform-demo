<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // List
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'users'); // 'users' | 'staff'

        $users = User::query()
            ->withCount('apps')
            ->with(['activeSubscription.plan', 'roles'])
            ->when($tab === 'staff',
                fn($q) => $q->whereIn('user_type', ['admin', 'superadmin']),
                fn($q) => $q->where('user_type', 'user')
            )
            ->when($request->search, fn($q, $s) => $q->where(function ($sq) use ($s) {
                $sq->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
            }))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'banned', fn($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'tab'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create
    // ─────────────────────────────────────────────────────────────────────────

    public function create()
    {
        $roles       = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0]);

        return view('admin.users.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|max:191|unique:users,email',
            'password'              => ['required', Password::min(8)->mixedCase()->numbers()],
            'password_confirmation' => 'required|same:password',
            'user_type'             => 'required|in:user,admin',
            'is_active'             => 'nullable|boolean',
            'roles'                 => 'nullable|array',
            'roles.*'               => 'exists:roles,name',
            'permissions'           => 'nullable|array',
            'permissions.*'         => 'exists:permissions,name',
        ]);

        // Privilege-escalation guard: only superadmin can create admin-type users
        if ($data['user_type'] === 'admin' && !$isSuperAdmin) {
            abort(403, 'Only superadmins may create staff accounts.');
        }

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'user_type' => $data['user_type'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Roles: only for admin-type accounts, requires superadmin or roles.assign
        if ($data['user_type'] === 'admin' && !empty($data['roles'])) {
            if ($isSuperAdmin || auth()->user()->can('roles.assign')) {
                $user->syncRoles($data['roles']);
            }
        }

        // Direct permissions: superadmin only
        if ($isSuperAdmin && !empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', "User \"{$user->name}\" created successfully.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Show
    // ─────────────────────────────────────────────────────────────────────────

    public function show(User $user)
    {
        $user->load([
            'apps'                  => fn($q) => $q->withCount('eventLogs')->latest(),
            'activeSubscription.plan',
            'roles',
            'permissions',
        ]);

        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.show', compact('user', 'plans', 'roles'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Edit / Update
    // ─────────────────────────────────────────────────────────────────────────

    public function edit(User $user)
    {
        $roles                = Role::orderBy('name')->get();
        $permissions          = Permission::orderBy('name')->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0]);
        $userRoles            = $user->roles->pluck('name')->toArray();
        $userDirectPermissions = $user->permissions->pluck('name')->toArray();

        return view('admin.users.edit', compact(
            'user', 'roles', 'permissions', 'userRoles', 'userDirectPermissions'
        ));
    }

    public function update(Request $request, User $user)
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        // Guard: nobody (except superadmin) may edit a superadmin
        if ($user->isSuperAdmin() && !$isSuperAdmin) {
            abort(403, 'Cannot edit a superadmin account.');
        }

        $rules = [
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|max:191|unique:users,email,' . $user->id,
            'is_active' => 'nullable|boolean',
            'roles'     => 'nullable|array',
            'roles.*'   => 'exists:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ];

        // Password change is optional — only validate if supplied
        if ($request->filled('password')) {
            $rules['password']              = ['string', Password::min(8)->mixedCase()->numbers()];
            $rules['password_confirmation'] = 'required|same:password';
        }

        // Only superadmin can change user_type
        if ($isSuperAdmin) {
            $rules['user_type'] = 'nullable|in:user,admin,superadmin';
        }

        $data = $request->validate($rules);

        $updateData = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($data['password']);
        }

        // Only superadmin may change user_type (and not demote another superadmin)
        if ($isSuperAdmin && !empty($data['user_type']) && !$user->isSuperAdmin()) {
            $updateData['user_type'] = $data['user_type'];
        }

        $user->update($updateData);

        // Sync roles — superadmin or roles.assign permission
        if ($isSuperAdmin || auth()->user()->can('roles.assign')) {
            $user->syncRoles($data['roles'] ?? []);
        }

        // Sync direct permissions — superadmin only
        if ($isSuperAdmin) {
            $user->syncPermissions($data['permissions'] ?? []);
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Misc actions
    // ─────────────────────────────────────────────────────────────────────────

    public function toggleBan(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot ban a superadmin.');
        }
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'banned';
        return back()->with('success', "User {$status} successfully.");
    }

    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot delete a superadmin.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function assignPlan(Request $request, User $user)
    {
        $data = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'expires_at'           => 'nullable|date|after:today',
            'notes'                => 'nullable|string|max:500',
        ]);

        // Cancel any existing active subscription
        $user->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

        UserSubscription::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $data['subscription_plan_id'],
            'status'               => 'active',
            'starts_at'            => now(),
            'expires_at'           => $data['expires_at'] ?? null,
            'assigned_by'          => auth()->id(),
            'notes'                => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Plan assigned to user successfully.');
    }
}
