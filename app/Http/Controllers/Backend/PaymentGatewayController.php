<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentGatewayController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('payment_gateways')) {
            return response()->json([]);
        }

        $query = DB::table('payment_gateways')
            ->select('id', 'account_no', 'status', 'store_id')
            ->orderBy('id');

        if ($request->boolean('allData')) {
            return response()->json($query->get());
        }

        return response()->json($query->paginate((int) ($request->input('pagination') ?? 10)));
    }

    public function allAccount()
    {
        if (!Schema::hasTable('payment_gateways') || !Schema::hasColumn('payment_gateways', 'account_no')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $all = DB::table('payment_gateways')
            ->select('account_no')
            ->whereNotNull('account_no')
            ->distinct()
            ->orderBy('account_no')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $all,
        ]);
    }
}
