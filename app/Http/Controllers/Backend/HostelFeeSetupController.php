<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HostelFeeSetupController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('hostel_fee_setups') ? 'hostel_fee_setups' : null;
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

        $q = DB::table("{$table} as hfs")
            ->orderByDesc('hfs.id');

        if (Schema::hasTable('hostels')) {
            $q->leftJoin('hostels as h', 'h.id', '=', 'hfs.hostel_id');
        }
        if (Schema::hasTable('account_heads')) {
            $q->leftJoin('account_heads as ah', 'ah.id', '=', 'hfs.account_head_id');
        }

        $status = $request->input('status');
        if ($status !== null && $status !== '' && Schema::hasColumn($table, 'status')) {
            $q->where('hfs.status', $status);
        }

        $field = (string) ($request->input('field_name') ?? 'hostel_name');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '') {
            if ($field === 'store_id' && Schema::hasColumn($table, 'store_id')) {
                $q->where('hfs.store_id', 'like', "%{$value}%");
            } elseif ($field === 'hostel_name' && Schema::hasTable('hostels')) {
                $q->where('h.name', 'like', "%{$value}%");
            } elseif ($field === 'purpose' && Schema::hasTable('account_heads')) {
                $q->where('ah.name', 'like', "%{$value}%");
            }
        }

        $select = [
            'hfs.id',
            'hfs.hostel_id',
            'hfs.account_head_id',
            'hfs.amount',
            'hfs.store_id',
            'hfs.store_password',
        ];

        if (Schema::hasColumn($table, 'status')) {
            $select[] = 'hfs.status';
        } else {
            $select[] = DB::raw("'active' as status");
        }

        if (Schema::hasTable('hostels')) {
            $select[] = DB::raw('h.name as hostel_name');
        } else {
            $select[] = DB::raw("'' as hostel_name");
        }

        if (Schema::hasTable('account_heads')) {
            $select[] = DB::raw('ah.name as head_name');
        } else {
            $select[] = DB::raw("'' as head_name");
        }

        $rows = $q->select($select)->paginate($perPage);

        // map nested like old ERP (hostel.name, head.name)
        $rows->getCollection()->transform(function ($r) {
            $r->hostel = ['name' => $r->hostel_name ?? null];
            $r->head = ['name' => $r->head_name ?? null];
            unset($r->hostel_name, $r->head_name);
            return $r;
        });

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Hostel Fee Setup module not ready'], 422);
        }

        $request->validate([
            'hostel_id' => ['required'],
            'account_head_id' => ['required'],
            'amount' => ['required'],
        ]);

        $data = [
            'hostel_id' => (int) $request->input('hostel_id'),
            'account_head_id' => (int) $request->input('account_head_id'),
            'amount' => (int) $request->input('amount'),
            'store_id' => $request->input('store_id'),
            'store_password' => $request->input('store_password'),
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if (Schema::hasColumn($table, 'status')) {
            $data['status'] = (string) ($request->input('status') ?? 'active');
        }

        $id = DB::table($table)->insertGetId($data);

        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Hostel Fee Setup module not ready'], 422);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Hostel Fee Setup module not ready'], 422);
        }

        $request->validate([
            'hostel_id' => ['required'],
            'account_head_id' => ['required'],
            'amount' => ['required'],
        ]);

        $data = [
            'hostel_id' => (int) $request->input('hostel_id'),
            'account_head_id' => (int) $request->input('account_head_id'),
            'amount' => (int) $request->input('amount'),
            'store_id' => $request->input('store_id'),
            'store_password' => $request->input('store_password'),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn($table, 'status')) {
            $data['status'] = (string) ($request->input('status') ?? 'active');
        }

        DB::table($table)->where('id', (int) $id)->update($data);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Hostel Fee Setup module not ready'], 422);
        }

        // soft delete if column exists
        if (Schema::hasColumn($table, 'deleted_at')) {
            DB::table($table)->where('id', (int) $id)->update(['deleted_at' => now(), 'updated_at' => now()]);
        } else {
            DB::table($table)->where('id', (int) $id)->delete();
        }

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }
}
