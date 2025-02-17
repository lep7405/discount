<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(UserService $userService, RegisterRequest $request)
    {
        $userService->create($request->validationData());

        return redirect()->route('login');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(UserService $userService, LoginRequest $request)
    {
        $userService->login($request->validationData());

        return redirect()->route('admin.dashboard.index');

    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        auth()->logout();

        return redirect('/auth/login')->with('message', 'Bạn đã đăng xuất thành công!');
    }

    public function me() {}

    public function changePassword(Request $request) {}
}
