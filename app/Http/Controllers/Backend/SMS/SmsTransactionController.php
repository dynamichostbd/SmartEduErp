<?php

namespace App\Http\Controllers\Backend\SMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SmsTransactionController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('sms_transactions') ? 'sms_transactions' : null;
    }

    private function emptyPaginator(int $perPage)
    {
        return response()->json([
            'current_page' => 1,
            'data' => [],
            'from' => null,
            'last_page' => 1,
            'per_page' => $perPage,
            'to' => null,
            'total' => 0,
        ]);
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->table();
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        if (!$table) {
            return $this->emptyPaginator($perPage);
        }

        $cols = Schema::getColumnListing($table);
        $q = DB::table($table)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'invoice_number');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['invoice_number', 'status'], true) && in_array($field, $cols, true)) {
            $q->where($field, 'like', "%{$value}%");
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'SMS Transaction module not ready'], 422);
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $cols = Schema::getColumnListing($table);
        $invoiceDate = date('Y-m-d');
        $invoiceNumber = 'SMS-' . date('Ymd') . '-' . rand(1000, 9999);

        $row = [];
        if (in_array('invoice_date', $cols, true)) {
            $row['invoice_date'] = $invoiceDate;
        }
        if (in_array('invoice_number', $cols, true)) {
            $row['invoice_number'] = $invoiceNumber;
        }
        if (in_array('amount', $cols, true)) {
            $row['amount'] = (float) $request->input('amount');
        }
        if (in_array('status', $cols, true)) {
            $row['status'] = 'pending';
        }
        if (in_array('created_at', $cols, true)) {
            $row['created_at'] = now();
        }
        if (in_array('updated_at', $cols, true)) {
            $row['updated_at'] = now();
        }

        if (empty($row)) {
            return response()->json(['message' => 'Invalid sms_transactions schema'], 422);
        }

        $id = DB::table($table)->insertGetId($row);
        return response()->json(['message' => 'Invoice create successfully', 'id' => $id], 200);
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->table();
        if (!$table) {
            return response()->json([], 404);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json(['sms_transaction' => $row], 200);
    }

    public function destroy($id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'SMS Transaction module not ready'], 422);
        }

        $ok = DB::table($table)->where('id', (int) $id)->delete();
        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }
}
