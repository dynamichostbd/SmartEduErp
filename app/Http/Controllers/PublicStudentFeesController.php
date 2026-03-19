<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicStudentFeesController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('web')->user();
        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!Schema::hasTable('fee_setups') || !Schema::hasTable('fee_setup_details')) {
            return response()->json(['fees' => []], 200);
        }

        $feeSetup = DB::table('fee_setups')->where([
            'academic_qualification_id' => (int) ($student->academic_qualification_id ?? 0),
            'department_id' => (int) ($student->department_id ?? 0),
            'academic_class_id' => (int) ($student->academic_class_id ?? 0),
        ])->first();

        if (!$feeSetup) {
            return response()->json(['fees' => []], 200);
        }

        $rows = DB::table('fee_setup_details as d')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'd.account_head_id')
            ->where('d.fee_setup_id', (int) $feeSetup->id)
            ->orderBy('d.id', 'asc')
            ->select([
                'd.id',
                'd.account_head_id',
                'd.amount',
                Schema::hasColumn('fee_setup_details', 'start_date') ? 'd.start_date' : DB::raw('NULL as start_date'),
                Schema::hasColumn('fee_setup_details', 'expire_date') ? 'd.expire_date' : DB::raw('NULL as expire_date'),
                Schema::hasColumn('fee_setup_details', 'additional_date') ? 'd.additional_date' : DB::raw('NULL as additional_date'),
                'ah.id as ah_id',
                'ah.name as ah_name',
            ])
            ->get();

        $out = $rows->map(function ($r) {
            return [
                'id' => (int) ($r->id ?? 0),
                'account_head_id' => (int) ($r->account_head_id ?? 0),
                'amount' => (float) ($r->amount ?? 0),
                'start_date' => $r->start_date ?? null,
                'expire_date' => $r->expire_date ?? null,
                'additional_date' => $r->additional_date ?? null,
                'account_head' => [
                    'id' => (int) ($r->ah_id ?? 0),
                    'name' => (string) ($r->ah_name ?? ''),
                ],
            ];
        })->values()->all();

        return response()->json(['fees' => $out], 200);
    }
}
