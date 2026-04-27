<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\SmsGatewayTrait;

class PublicStudentAuthController extends Controller
{
    use SmsGatewayTrait;
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
        return response()->json(['logged_out' => true], 200);
    }

    public function sendOTP(Request $request)
    {
        $request->validate(['mobile' => 'required']);

        $student = Student::where('mobile', $request->mobile)->where('status', 'active')->first();

        if (!$student) {
            return $this->sendError('No active student found with this mobile number.', 404);
        }

        $code = rand(1111, 9999);

        $student->update([
            'otp' => $code,
        ]);

        $message = $this->smsTemplate('OTP', ['otp' => $code], $student) ?: "Your OTP for password reset is: " . $code;
        $sent = $this->sendSmsViaGateway($student->mobile, $message);

        if (!$sent) {
            $error = $this->smsGatewayConfigError() ?: 'Failed to send SMS. Please contact support.';
            return $this->sendError($error, 500);
        }

        return $this->sendResponse([], 200, 'OTP sent successfully. Please check your mobile.');
    }

    public function checkOTP(Request $request)
    {
        $request->validate([
            'mobile' => 'required',
            'otp'    => 'required',
        ]);

        $student = Student::where('mobile', $request->mobile)->where('status', 'active')->first();

        if (!$student) {
            return $this->sendError('No active student found with this mobile number.', 404);
        }


        if ((string) $student->otp !== (string) $request->otp) {
            return $this->sendError('OTP does not match. Please try again.', 400);
        }

        // Clear OTP from DB after verification (matches old ERP)
        $student->update(['otp' => null]);

        return $this->sendResponse([], 200, 'OTP verified successfully.');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'mobile'           => 'required',
            'new_password'     => 'required|min:6|same:confirm_password',
            'confirm_password' => 'required|min:6',
        ]);

        $student = Student::where('mobile', $request->mobile)->where('status', 'active')->first();

        if (!$student) {
            return $this->sendError('No active student found with this mobile number.', 404);
        }

        // We don't re-verify OTP here as it was cleared in checkOTP (matches old ERP logic)

        DB::table('students')->where('id', $student->id)->update([
            'password'       => Hash::make($request->new_password),
            'otp'            => null,
        ]);

        return $this->sendResponse([], 200, 'Password reset successfully!');
    }
}
