<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HostelFeeGenerateController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('hostel_fee_generates') ? 'hostel_fee_generates' : null;
    }

    private function detailsTable(): ?string
    {
        return Schema::hasTable('hostel_fee_generate_details') ? 'hostel_fee_generate_details' : null;
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

        $q = DB::table("{$table} as g")
            ->orderByDesc('g.id');

        if (Schema::hasTable('hostels')) {
            $q->leftJoin('hostels as h', 'h.id', '=', 'g.hostel_id');
        }
        if (Schema::hasTable('academic_sessions')) {
            $q->leftJoin('academic_sessions as fy', 'fy.id', '=', 'g.financial_year_id');
        }

        $hostelId = $request->input('hostel_id');
        if ($hostelId !== null && $hostelId !== '') {
            $q->where('g.hostel_id', (int) $hostelId);
        }

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && $field === 'name' && Schema::hasTable('hostels')) {
            $q->where('h.name', 'like', "%{$value}%");
        }

        $select = [
            'g.id',
            'g.hostel_id',
            'g.financial_year_id',
            'g.created_at',
            'g.updated_at',
        ];
        if (Schema::hasTable('hostels')) $select[] = DB::raw('h.name as hostel_name');
        if (Schema::hasTable('academic_sessions')) $select[] = DB::raw('fy.name as financial_year_name');

        $q->select($select);

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        $detailsTable = $this->detailsTable();

        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Hostel Fee Generate module not ready'], 422);
        }

        $request->validate([
            'hostel_id' => ['required'],
            'financial_year_id' => ['required'],
        ]);

        $details = $request->input('details');
        if (!is_array($details)) {
            return response()->json(['message' => 'Invalid details'], 422);
        }

        foreach ($details as $idx => $d) {
            $status = data_get($d, 'status', 0);
            $active = (string) $status === '1' || $status === 1 || $status === true;
            if ($active) {
                $headId = data_get($d, 'account_head_id');
                $amount = data_get($d, 'amount');
                if (!$headId) {
                    return response()->json(['message' => "Purpose is required (row " . ($idx + 1) . ")"], 422);
                }
                if ($amount === null || $amount === '' || (float) $amount <= 0) {
                    return response()->json(['message' => "Amount is required (row " . ($idx + 1) . ")"], 422);
                }
            }
        }

        $exists = DB::table($table)
            ->where('hostel_id', (int) $request->input('hostel_id'))
            ->where('financial_year_id', (int) $request->input('financial_year_id'))
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already generate fees for this hostel !'], 422);
        }

        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        DB::beginTransaction();
        try {
            $id = DB::table($table)->insertGetId([
                'hostel_id' => (int) $request->input('hostel_id'),
                'financial_year_id' => (int) $request->input('financial_year_id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $rows = [];
            foreach ($details as $d) {
                $rows[] = [
                    'hostel_fee_generate_id' => (int) $id,
                    'account_head_id' => data_get($d, 'account_head_id'),
                    'date' => data_get($d, 'date'),
                    'amount' => data_get($d, 'amount'),
                    'is_establishment' => (int) (data_get($d, 'is_establishment', 0) ?? 0),
                    'sorting' => (int) (data_get($d, 'sorting', 0) ?? 0),
                    'status' => (int) (data_get($d, 'status', 1) ?? 1),
                    'created_by' => $admin->name ?? null,
                    'created_ip' => $ip,
                    'updated_by' => $admin->name ?? null,
                    'updated_ip' => $ip,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (count($rows)) {
                DB::table($detailsTable)->insert($rows);
            }

            DB::commit();
            return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create', 'exception' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->table();
        $detailsTable = $this->detailsTable();

        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Hostel Fee Generate module not ready'], 422);
        }

        $q = DB::table("{$table} as g")
            ->where('g.id', (int) $id);

        if (Schema::hasTable('hostels')) {
            $q->leftJoin('hostels as h', 'h.id', '=', 'g.hostel_id');
        }
        if (Schema::hasTable('academic_sessions')) {
            $q->leftJoin('academic_sessions as fy', 'fy.id', '=', 'g.financial_year_id');
        }

        $select = [
            'g.id',
            'g.hostel_id',
            'g.financial_year_id',
            'g.created_at',
            'g.updated_at',
        ];
        if (Schema::hasTable('hostels')) $select[] = DB::raw('h.name as hostel_name');
        if (Schema::hasTable('academic_sessions')) $select[] = DB::raw('fy.name as financial_year_name');

        $row = $q->select($select)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $dq = DB::table("{$detailsTable} as d")
            ->where('d.hostel_fee_generate_id', (int) $id);

        if (Schema::hasTable('account_heads')) {
            $dq->leftJoin('account_heads as ah', 'ah.id', '=', 'd.account_head_id');
        }

        $dSelect = [
            'd.id',
            'd.hostel_fee_generate_id',
            'd.account_head_id',
            'd.date',
            'd.amount',
            'd.is_establishment',
            'd.sorting',
            'd.status',
        ];
        if (Schema::hasTable('account_heads')) $dSelect[] = DB::raw('ah.name as purpose_name');

        $details = $dq
            ->select($dSelect)
            ->orderBy('d.sorting', 'asc')
            ->orderBy('d.id', 'asc')
            ->get();

        return response()->json([
            'data' => $row,
            'details' => $details,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        $detailsTable = $this->detailsTable();

        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Hostel Fee Generate module not ready'], 422);
        }

        $request->validate([
            'hostel_id' => ['required'],
            'financial_year_id' => ['required'],
        ]);

        $details = $request->input('details');
        if (!is_array($details)) {
            return response()->json(['message' => 'Invalid details'], 422);
        }

        foreach ($details as $idx => $d) {
            $status = data_get($d, 'status', 0);
            $active = (string) $status === '1' || $status === 1 || $status === true;
            if ($active) {
                $headId = data_get($d, 'account_head_id');
                $amount = data_get($d, 'amount');
                if (!$headId) {
                    return response()->json(['message' => "Purpose is required (row " . ($idx + 1) . ")"], 422);
                }
                if ($amount === null || $amount === '' || (float) $amount <= 0) {
                    return response()->json(['message' => "Amount is required (row " . ($idx + 1) . ")"], 422);
                }
            }
        }

        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        DB::beginTransaction();
        try {
            DB::table($table)->where('id', (int) $id)->update([
                'hostel_id' => (int) $request->input('hostel_id'),
                'financial_year_id' => (int) $request->input('financial_year_id'),
                'updated_at' => now(),
            ]);

            DB::table($detailsTable)->where('hostel_fee_generate_id', (int) $id)->delete();

            $rows = [];
            foreach ($details as $d) {
                $rows[] = [
                    'hostel_fee_generate_id' => (int) $id,
                    'account_head_id' => data_get($d, 'account_head_id'),
                    'date' => data_get($d, 'date'),
                    'amount' => data_get($d, 'amount'),
                    'is_establishment' => (int) (data_get($d, 'is_establishment', 0) ?? 0),
                    'sorting' => (int) (data_get($d, 'sorting', 0) ?? 0),
                    'status' => (int) (data_get($d, 'status', 1) ?? 1),
                    'created_by' => $admin->name ?? null,
                    'created_ip' => $ip,
                    'updated_by' => $admin->name ?? null,
                    'updated_ip' => $ip,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (count($rows)) {
                DB::table($detailsTable)->insert($rows);
            }

            DB::commit();
            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update', 'exception' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        $table = $this->table();
        $detailsTable = $this->detailsTable();

        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Hostel Fee Generate module not ready'], 422);
        }

        DB::beginTransaction();
        try {
            DB::table($detailsTable)->where('hostel_fee_generate_id', (int) $id)->delete();
            DB::table($table)->where('id', (int) $id)->delete();
            DB::commit();
            return response()->json(['message' => 'Delete Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Delete failed', 'exception' => $e->getMessage()], 422);
        }
    }
}
