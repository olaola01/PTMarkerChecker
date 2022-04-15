<?php

namespace App\Http\Controllers;

use App\Enum\ApiStatusMessageResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token
        ];

        return $this->successCall(ApiStatusMessageResponse::SUCCESS, 201, $data, 'Registered Successful');
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->badCall(401, ApiStatusMessageResponse::ERROR, 'Incorrect email or password!');
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token
        ];

        return $this->successCall(ApiStatusMessageResponse::SUCCESS, 200, $data, 'Logged In Successfully');
    }


    public function logout(Request $request): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return $this->successCall(ApiStatusMessageResponse::SUCCESS, 200, [], 'Logged Out Successfully');

    }
}
