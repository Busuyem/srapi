<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try{
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);
    
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
    
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'message' => 'Success!',
                'data' => $user,
                'token' => $token
                ], 201);

        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function login(Request $request)
    {
        try{
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 401);
            }

            return response()->json([
                'message' => 'Success!',
                'token' => $token
            ]);
            
        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
