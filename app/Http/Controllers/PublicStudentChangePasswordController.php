<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PublicStudentChangePasswordController extends Controller
{
    public function checkOldPassword(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(false, 200);
        }

        $request->validate([
            'old_password' => ['required'],
        ]);

        return response()->json(Hash::check((string) $request->old_password, (string) ($student->password ?? '')), 200);
    }

    public function changePassword(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $oldPass = Hash::check((string) ($request->old_password ?? ''), (string) ($student->password ?? ''));
        if (!$oldPass) {
            return response()->json(['message' => "Sorry!! Old password doesn't match our records"], 201);
        }

        $request->validate([
            'new_password' => ['required', 'min:6', 'required_with:confirm_password', 'same:confirm_password'],
            'confirm_password' => ['required', 'min:6'],
        ]);

        if (Schema::hasTable('students')) {
            DB::table('students')->where('id', (int) ($student->id ?? 0))->update([
                'password' => Hash::make((string) $request->new_password),
                'updated_at' => now(),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Password change successfully!!'], 200);
    }
}
