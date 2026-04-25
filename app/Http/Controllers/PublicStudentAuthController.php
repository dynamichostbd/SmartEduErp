<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PublicStudentAuthController extends Controller
{
    public function me(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'authenticated' => true,
            'student' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required'],
            'password' => ['required'],
        ]);

        $login = trim((string) $request->input('login'));
        $password = (string) $request->input('password');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $ok = Auth::guard('web')->attempt([
            $field => $login,
            'password' => $password,
            'status' => 'active',
        ]);

        if (!$ok) {
            $ok = Auth::guard('web')->attempt([
                $field => $login,
                'password' => $password,
            ]);
        }

        if (!$ok) {
            return response()->json(['message' => 'Invalid login or password.'], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'authenticated' => true,
            'student' => Auth::guard('web')->user(),
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        // Note: do NOT call regenerateToken() here — it changes the CSRF token
        // in the new session but the client's XSRF-TOKEN cookie is NOT updated
        // until the next full page request, so the next POST (e.g. login) gets a 419.
        // session()->invalidate() already destroys the old session; a fresh token
        // is generated automatically when the next session starts.

        return response()->json(['logged_out' => true], 200);
    }
}
