<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request, PointsService $pointsService)
    {
        $request->validate([
            'phone' => 'required|string',
            // In a real app, we would validate OTP here.
            // 'otp' => 'required|string',
        ]);

        // Simulation of OTP login: Find or Create user by phone
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => 'User ' . substr($request->phone, -4)]
        );

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Award daily login points
        $pointsService->awardDailyLoginPoints($user);

        // Generate token (using Sanctum)
        // Note: Sanctum is not installed in this environment simulation, but this is the standard code.
        // Assuming the user has Laravel Sanctum installed.
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
