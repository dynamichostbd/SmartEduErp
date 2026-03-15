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

        $cols = Schema::getColumnListing('payment_gateways');
        $select = ['id'];

        foreach (['department_id', 'title', 'provider', 'store_id', 'store_password', 'account_no', 'description', 'status'] as $c) {
            if (in_array($c, $cols, true)) {
                $select[] = $c;
            }
        }

        $query = DB::table('payment_gateways')->select($select)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'store_id');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['store_id', 'title', 'account_no'], true) && in_array($field, $cols, true)) {
            $query->where($field, 'like', "%{$value}%");
        }

        if ($request->filled('status') && in_array('status', $cols, true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('department_id') && in_array('department_id', $cols, true)) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->boolean('allData')) {
            return response()->json($query->get());
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('payment_gateways')) {
            return response()->json(['message' => 'Payment Gateway module not ready'], 422);
        }

        $request->validate([
            'title' => ['required'],
            'store_id' => ['required'],
            'store_password' => ['required'],
        ]);

        $cols = Schema::getColumnListing('payment_gateways');
        $payload = [];

        foreach (['department_id', 'title', 'provider', 'store_id', 'store_password', 'account_no', 'description', 'status'] as $c) {
            if ($request->has($c) && in_array($c, $cols, true)) {
                $payload[$c] = $request->input($c);
            }
        }

        if (!isset($payload['status']) && in_array('status', $cols, true)) {
            $payload['status'] = 'active';
        }

        if (in_array('created_at', $cols, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $cols, true)) {
            $payload['updated_at'] = now();
        }

        try {
            $id = DB::table('payment_gateways')->insertGetId($payload);
            return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to create', 'exception' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('payment_gateways')) {
            return response()->json([], 404);
        }

        $row = DB::table('payment_gateways')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json(['payment_gateway' => $row], 200);
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('payment_gateways')) {
            return response()->json(['message' => 'Payment Gateway module not ready'], 422);
        }

        $request->validate([
            'title' => ['required'],
            'store_id' => ['required'],
            'store_password' => ['required'],
        ]);

        $cols = Schema::getColumnListing('payment_gateways');
        $payload = [];

        foreach (['department_id', 'title', 'provider', 'store_id', 'store_password', 'account_no', 'description', 'status'] as $c) {
            if ($request->has($c) && in_array($c, $cols, true)) {
                $payload[$c] = $request->input($c);
            }
        }

        if (in_array('updated_at', $cols, true)) {
            $payload['updated_at'] = now();
        }

        try {
            DB::table('payment_gateways')->where('id', (int) $id)->update($payload);
            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to update', 'exception' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('payment_gateways')) {
            return response()->json(['message' => 'Payment Gateway module not ready'], 422);
        }

        $cols = Schema::getColumnListing('payment_gateways');
        if (in_array('status', $cols, true)) {
            $ok = DB::table('payment_gateways')->where('id', (int) $id)->update(['status' => 'deactive']);
            return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
        }

        $ok = DB::table('payment_gateways')->where('id', (int) $id)->delete();
        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
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
