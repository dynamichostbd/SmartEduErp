<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\MobileDevice;
use Laravel\Sanctum\PersonalAccessToken;


class StudentLoginController extends Controller
{

    /**
     * Login The Student
     * @param Request $request
     * @return Student
     */
    public function login(Request $request)
    {
        $vd = $this->validateCheck($request->all());
        if ($vd->fails()) {
            return $this->sendError("Validation errors", 401, $vd->errors());
        }

        try {
            if (!Auth::attempt($request->only(['mobile', 'password']))) {
                return $this->sendError('Mobile / Password does not match with our record.', 401);
            }

            $std = Student::where('mobile', $request->mobile)->where('status', 'active')->first();

            if (!empty($std)) {
                // existing token delete
                $std->tokens()->where('tokenable_id', $std->id)->delete();

                // Device Token
                if (!empty($request->device_token)) {
                    $mobileDevice = MobileDevice::where('device_token', $request->device_token)->first();
                    if (!empty($mobileDevice)) {
                        $mobileDevice->update(['student_id' => $std->id]);
                    } else {
                        MobileDevice::create(['student_id' => $std->id, 'device_token' => $request->device_token]);
                    }
                }

                $data = [
                    "name"  => "Bearer",
                    'token' => $std->createToken("TOKEN-{$request->mobile}")->plainTextToken,
                ];
                return $this->sendResponse($data, 200, "Login Successfully");
            } else {
                return $this->sendError('Mobile / Password does not match with our record.', 401);
            }
        } catch (\Throwable $th) {
            return $this->sendError("Exceptions errors", 500, $th->getMessage());
        }
    }

    /**
     * Validation check====
     */
    public function validateCheck($data)
    {
        return Validator::make($data, [
            'mobile'   => 'required|min:11|max:11',
            'password' => 'required|min:6',
        ]);
    }

    /**
     * Logged out
     */
    public function logout()
    {
        $std = Student::where('mobile', auth()->user()->mobile)->first();
        $std->tokens()->where('tokenable_id', $std->id)->delete();
        session()->flush();

        Artisan::call('optimize:clear');
        return $this->sendResponse([], 200, "Logout Successfully");
    }

    /**
     * Verify Student Token
     * Token must be sent as: Authorization: Bearer <token>
     * Returns authenticated or unauthenticated — ignores session cookies.
     */
    public function verifyToken(Request $request)
    {
        $bearerToken = $request->bearerToken();

        if (empty($bearerToken)) {
            return $this->sendError('Unauthenticated', 401);
        }

        // Sanctum tokens format: "<id>|<plainHash>"
        $parts = explode('|', $bearerToken, 2);

        if (count($parts) !== 2) {
            return $this->sendError('Unauthenticated', 401);
        }

        [$id, $plainHash] = $parts;

        $accessToken = PersonalAccessToken::find($id);

        if (!$accessToken || !hash_equals($accessToken->token, hash('sha256', $plainHash))) {
            return $this->sendError('Unauthenticated', 401);
        }

        // Check Sanctum config expiration (minutes from created_at)
        $expirationMinutes = config('sanctum.expiration');
        if ($expirationMinutes && $accessToken->created_at->addMinutes($expirationMinutes)->isPast()) {
            return $this->sendError('Unauthenticated', 401);
        }

        // Check explicit expires_at column
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return $this->sendError('Unauthenticated', 401);
        }

        return $this->sendResponse(['authenticated' => true], 200, 'Authenticated');
    }

    /**
     * Store Device ID====
     */
    public function storeDeviceID(Request $request)
    {
        MobileDevice::updateOrCreate(
            ['device_token' => $request->device_token],
            ['device_token' => $request->device_token]
        );
        return $this->sendResponse([], 200, "Device token store Successfully");
    }
}


