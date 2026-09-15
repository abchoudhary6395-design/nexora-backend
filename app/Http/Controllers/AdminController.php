<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->role, fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('slug', $request->role)))
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });

        return $this->paginated($query->latest()->paginate($request->per_page ?? 15));
    }

    /** Invites a new team member with a temporary password and a chosen role. */
    public function invite(Request $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(24)), // reset via forgot-password flow
            'status' => 'active',
        ]);

        $user->roles()->attach($data['role_id']);

        // TODO: dispatch an invitation email/notification with a password-set link.

        return $this->success($user->load('roles'), 'User invited', 201);
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate(['role_id' => ['required', 'exists:roles,id']]);
        $user->roles()->sync([$request->role_id]);

        return $this->success($user->fresh('roles'), 'Role updated');
    }

    public function updateStatus(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate(['status' => ['required', 'in:active,inactive,suspended']]);
        $user->update(['status' => $request->status]);

        return $this->success($user->fresh(), 'Status updated');
    }

    public function roles()
    {
        return $this->success(Role::withCount('users')->get());
    }
}
