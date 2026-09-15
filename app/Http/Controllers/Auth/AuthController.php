<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return $this->error('Invalid email or password', 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return $this->error('This account has been deactivated. Contact your administrator.', 403);
        }

        $tokenName = $request->boolean('remember') ? 'nexora-remember' : 'nexora-session';
        $token = $user->createToken($tokenName)->plainTextToken;

        $user->update(['last_active_at' => now()]);

        return $this->success([
            'token' => $token,
            'user' => $user->load('roles'),
        ], 'Logged in successfully');
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => trim($request->first_name.' '.$request->last_name),
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Every new self-registered user starts as an Employee by default;
        // an admin can promote them later via the Admin module.
        $employeeRole = Role::firstOrCreate(
            ['slug' => 'employee'],
            ['name' => 'Employee', 'is_system' => true]
        );
        $user->roles()->attach($employeeRole);

        $token = $user->createToken('nexora-session')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $user->load('roles'),
        ], 'Account created successfully', 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully');
    }

    public function profile(Request $request)
    {
        return $this->success($request->user()->load('roles.permissions'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? $this->success(null, 'Password reset link sent to your email')
            : $this->error('Unable to send reset link', 422);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->update([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ]);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->success(null, 'Password reset successfully')
            : $this->error('Invalid or expired reset token', 422);
    }
}
