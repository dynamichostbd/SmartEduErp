<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeeSetupController extends Controller
{
    public function feesLists(Request $request)
    {
        if (!Schema::hasTable('fee_setups') || !Schema::hasTable('fee_setup_details') || !Schema::hasTable('account_heads')) {
            return response()->json([]);
        }

        $request->validate([
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'academic_class_id' => ['required'],
        ]);

        $feeSetup = DB::table('fee_setups')
            ->where([
                'academic_qualification_id' => $request->input('academic_qualification_id'),
                'department_id' => $request->input('department_id'),
                'academic_class_id' => $request->input('academic_class_id'),
            ])
            ->first();

        if (!$feeSetup) {
            return response()->json([]);
        }

        $details = DB::table('fee_setup_details as d')
            ->join('account_heads as ah', 'ah.id', '=', 'd.account_head_id')
            ->where('d.fee_setup_id', $feeSetup->id)
            ->select([
                'ah.name',
                'ah.id',
                'd.payment_gateway_id',
                'd.amount',
                'd.exam_id',
                'd.examination_year',
            ])
            ->orderBy('ah.id')
            ->get();

        return response()->json($details);
    }
}
