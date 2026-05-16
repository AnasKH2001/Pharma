<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserRegisterController extends Controller
{
    protected UserService $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function register(UserRegisterRequest $request)
    {
        $result = $this->userService->register($request->validated());
        
        return response()->json($result, 201);
    }
    
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);
        
        $result = $this->userService->verifyOtp($request->email, $request->otp);
        
        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }
        
        return response()->json(['message' => $result['message']]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        $result = $this->userService->resendOtp($request->email);
        
        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }
        
        return response()->json(['message' => $result['message']]);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $result = $this->userService->forgotPassword($request->email);
        
        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }
        
        return response()->json(['message' => $result['message']]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $result = $this->userService->resetPassword(
            $request->email,
            $request->otp,
            $request->password
        );
        
        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 400);
        }
        
        return response()->json(['message' => $result['message']]);
    }

}