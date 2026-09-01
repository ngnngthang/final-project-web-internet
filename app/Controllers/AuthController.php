<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class AuthController
{
    public function login(Request $request): Response
    {
        $user = Auth::attempt($request->input('username', ''), $request->input('password', ''));
        if (!$user) {
            return Response::json(['success' => false, 'error' => ['code' => 'INVALID_CREDENTIALS', 'message' => 'Invalid username or password']], 401);
        }
        unset($user['password']);
        return Response::json(['success' => true, 'data' => $user]);
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        return Response::json(['success' => true, 'data' => ['message' => 'Logged out']]);
    }

    public function me(Request $request): Response
    {
        Auth::require();
        $user = Auth::user();
        unset($user['password']);
        return Response::json(['success' => true, 'data' => $user]);
    }
}
