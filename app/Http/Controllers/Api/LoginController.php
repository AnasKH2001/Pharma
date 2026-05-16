<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected UserService $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function login(LoginRequest $request)
    {
        $result = $this->userService->login(
            $request->email,
            $request->password
        );
        
        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 401);
        }
        
        return response()->json($result);
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Logged out successfully']);
    }
}