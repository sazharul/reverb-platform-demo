<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tokens = $user->tokens()->latest()->get();
        $usage = app(PlanLimitService::class)->usageSummary($user);
        return view('dashboard.settings.index', compact('user', 'tokens', 'usage'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($data);
        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully.');
    }

    public function createToken(Request $request)
    {
        $data = $request->validate([
            'token_name' => 'required|string|max:100',
        ]);

        $token = auth()->user()->createToken($data['token_name']);

        return back()->with('success', 'Token created: ' . $token->plainTextToken)
                     ->with('new_token', $token->plainTextToken);
    }

    public function revokeToken(Request $request, $tokenId)
    {
        auth()->user()->tokens()->where('id', $tokenId)->delete();
        return back()->with('success', 'Token revoked.');
    }
}
