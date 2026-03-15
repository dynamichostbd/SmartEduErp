<?php

namespace App\Http\Controllers\Auth;

use App\Classes\OTP;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OTPController extends Controller
{
    public function sendOTP(Request $request, OTP $otp)
    {
        $request->validate(['email' => 'required', 'password' => 'required']);

        $admin = Admin::where('email', $request->email)->where('status', 'active')->first();

        if (!$admin) {
            return $this->sendError('Email not found.', 404);
        }

        if (!Hash::check($request->password, $admin->password)) {
            return $this->sendError('Your Login Information is Wrong', 403);
        }

        if ((int) ($admin->is_two_factor_auth ?? 0) === 0) {
            return $this->sendResponse(['verified_otp' => true], 200);
        }

        $otp->generateAndSend($admin);

        return $this->sendResponse([], 200, 'Please check your mobile for OTP code.');
    }

    public function verifyOTP(Request $request, OTP $otp)
    {
        $request->validate([
            'email' => 'required',
            'otp' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->where('status', 'active')->first();

        if (!$admin) {
            return $this->sendError('Email not found.', 404);
        }

        $otp->verify($admin, $request->otp);

        return $this->sendResponse([], 200, 'OTP verified successfully.');
    }
}
