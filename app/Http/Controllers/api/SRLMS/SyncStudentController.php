<?php

namespace App\Http\Controllers\api\SRLMS;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyncStudentController extends Controller
{
    /**
     * send students to srlms
     * through api request
     */
    public function sync_students(Request $request)
    {
        $secretKey = $request->secret_key;

        if ($secretKey !== 'erp_sync_students') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: Invalid secret key.'
            ], 401);
        }

        $page = (int) $request->input('page', 1);
        $perPage = $request->page_size ?? 100;
        $offset = ($page - 1) * $perPage;

        $total = Student::count();

        if ($offset >= $total) {
            return response()->json([
                'status' => false,
                'message' => 'No students found for the requested page.'
            ], 404);
        }

        $students = Student::offset($offset)->limit($perPage)->latest()->get();
        $totalPages = (int) ceil($total / $perPage);
        $hasNextPage = $page < $totalPages;
        $nextPage = $hasNextPage ? $page + 1 : null;

        return response()->json([
            'status' => true,
            'message' => 'Students fetched successfully!',
            'data' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'hasNextPage' => $hasNextPage,
                'next_page' => $nextPage,
                'students' => $students,
            ]
        ], 200);
    }

    public function redirect_srlms(){
        $user = Auth::user();
        $credential = $user->email ? $user->email : $user->mobile;

        if (!$credential) {
            return back()->with('error', 'User credentials not found');
        }

        $timestamp = time();
        $source = 'erp';
        $payload = "$credential|$timestamp|$source";
        $signature = hash_hmac('sha256', $payload, env('ERP_SHARED_SECRET_KEY'));

        $nextFrontendUrl = env('SRLMS_SSO_LOGIN_URL');
        $ssoUrl = $nextFrontendUrl . '?' . http_build_query([
            'credential' => $credential,
            'time' => $timestamp,
            'source' => $source,
            'signature' => $signature,
        ]);

        return redirect()->away($ssoUrl);
    }
}


