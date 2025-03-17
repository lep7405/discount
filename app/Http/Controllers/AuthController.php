<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\User\UserService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        //        return redirect()->route('login');
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
        try {
            Auth::logout();
        } catch (AuthenticationException $exception) {
            return $exception->getMessage();
        }

        return redirect('/login')->with('message', 'Bạn đã đăng xuất thành công!');
    }

    public function me()
    {
        $user = Auth::user();

        return view('admin.user.index', compact('user'));
    }

    public function changePassword(ChangePasswordRequest $request, UserService $userService)
    {
        $user = Auth::user();
        $userService->changePassword($request->validationData(), $user->id);

        return redirect()->back()->with('success', 'Change password successfully!');
    }
}
