<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\ErrorException;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout', 'loginCheck');
    }

    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);

            $email = trim((string) $request->email);

            $arr = [
                'email' => $email,
                'password' => $request->password,
                'status' => 'active',
            ];

            $admin = Admin::where('email', $email)->first();

            if (!$admin) {
                throw new ErrorException('Email does not match our records!', 404);
            }

            if (($admin->status ?? null) !== 'active') {
                throw new ErrorException('Your account is not active.', 403);
            }

            if ((int) ($admin->block ?? 0) !== 0) {
                Session::forget($email);
                throw new ErrorException('Your account is blocked, please contact your administrator.', 403);
            }

            if (!Hash::check((string) $request->password, (string) $admin->password)) {
                throw new ErrorException('Your Login Information is Wrong', 403);
            }

            if (!empty($admin->otp_expired_at) && (int) ($admin->is_two_factor_auth ?? 0) === 1) {
                $expiredTime = Carbon::parse($admin->otp_expired_at)->diffInMinutes(Carbon::now());

                if ($expiredTime >= 5) {
                    throw new ErrorException('Your OTP has expired. Please refresh the page and request a new OTP to verify your identity.', 429);
                }
            }

            Auth::guard('admin')->login($admin, (bool) $request->remember);

            $admin->update(['otp_expired_at' => null]);
            Session::forget($email);

            return response([
                'message' => 'Login Successfully',
                'id' => Auth::guard('admin')->user()->id ?? '',
                'name' => Auth::guard('admin')->user()->name ?? '',
                'role' => Auth::guard('admin')->user()->role->name ?? '',
                'role_id' => Auth::guard('admin')->user()->role_id ?? '',
                'type' => Auth::guard('admin')->user()->type ?? '',
            ], 200);
        }

        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('layouts.login_app');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();

        return response()->json(true);
    }

    public function loginCheck()
    {
        if (Auth::guard('admin')->user()) {
            return response([
                'message' => 'Login Successfully',
                'id' => Auth::guard('admin')->user()->id ?? '',
                'name' => Auth::guard('admin')->user()->name ?? '',
                'role' => Auth::guard('admin')->user()->role->name ?? '',
                'role_id' => Auth::guard('admin')->user()->role_id ?? '',
                'type' => Auth::guard('admin')->user()->type ?? '',
            ], 200);
        }

        return response([
            'message' => 'Unauthorized',
        ], 201);
    }
}
