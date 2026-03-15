<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdmissionFeeSetupController extends Controller
{
    private function baseQuery(Request $request)
    {
        $query = DB::table('admission_fee_setups as afs')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'afs.account_head_id');

        $field = (string) $request->input('field_name');
        $value = $request->input('value');

        if ($field !== '' && $value !== null && $value !== '') {
            if ($field === 'store_id' && Schema::hasColumn('admission_fee_setups', 'store_id')) {
                $query->where('afs.store_id', 'like', '%' . $value . '%');
            }
        }

        return $query;
    }

    private function selectRow($query)
    {
        $select = [
            'afs.id',
            'afs.account_head_id',
            'afs.amount',
            'afs.store_id',
            'afs.store_password',
            'afs.account_no',
            'ah.id as head_id',
            'ah.name as head_name',
        ];

        foreach ([
            'enable_date',
            'enable_roll',
            'start_date',
            'expire_date',
            'additional_date',
            'session',
            'academic_session_ids',
            'admission_roll_min_value',
            'admission_roll_max_value',
        ] as $col) {
            if (Schema::hasColumn('admission_fee_setups', $col)) {
                $select[] = 'afs.' . $col;
            }
        }

        if (Schema::hasColumn('admission_fee_setups', 'created_at')) {
            $select[] = 'afs.created_at';
        }

        return $query->select($select);
    }

    private function mapRow($r)
    {
        $row = [
            'id' => (int) ($r->id ?? 0),
            'account_head_id' => $r->account_head_id ?? null,
            'amount' => $r->amount ?? null,
            'store_id' => $r->store_id ?? null,
            'store_password' => $r->store_password ?? null,
            'account_no' => $r->account_no ?? null,
            'head' => $r->head_id ? ['id' => (int) $r->head_id, 'name' => $r->head_name] : null,
        ];

        foreach ([
            'enable_date',
            'enable_roll',
            'start_date',
            'expire_date',
            'additional_date',
            'session',
            'admission_roll_min_value',
            'admission_roll_max_value',
        ] as $k) {
            if (property_exists($r, $k)) {
                $row[$k] = $r->{$k};
            }
        }

        if (property_exists($r, 'academic_session_ids')) {
            $v = $r->academic_session_ids;
            if (is_string($v)) {
                $decoded = json_decode($v, true);
                $row['academic_session_ids'] = is_array($decoded) ? $decoded : $v;
            } else {
                $row['academic_session_ids'] = $v;
            }
        }

        return $row;
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('admission_fee_setups')) {
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate((int) ($request->input('pagination') ?? 10)));
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $query = $this->baseQuery($request);
        $query = $this->selectRow($query);
        $query->orderByDesc('afs.id');

        $datas = $query->paginate($perPage);
        $datas->getCollection()->transform(function ($r) {
            return $this->mapRow($r);
        });

        return response()->json($datas);
    }

    public function create()
    {
        return view('layouts.backend_app');
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('admission_fee_setups')) {
            return response()->json([], 404);
        }

        $query = $this->selectRow(DB::table('admission_fee_setups as afs')->leftJoin('account_heads as ah', 'ah.id', '=', 'afs.account_head_id'))
            ->where('afs.id', (int) $id);

        $row = $query->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json($this->mapRow($row));
    }

    public function edit($id)
    {
        return view('layouts.backend_app');
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_head_id' => ['required'],
            'amount' => ['required'],
            'store_id' => ['required'],
            'store_password' => ['required'],
        ]);

        if (!Schema::hasTable('admission_fee_setups')) {
            return response()->json(['message' => 'Admission fee setup module not ready'], 422);
        }

        $payload = [];
        foreach ([
            'account_head_id',
            'amount',
            'store_id',
            'store_password',
            'account_no',
            'enable_date',
            'enable_roll',
            'start_date',
            'expire_date',
            'additional_date',
            'session',
            'admission_roll_min_value',
            'admission_roll_max_value',
        ] as $col) {
            if ($request->has($col) && Schema::hasColumn('admission_fee_setups', $col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if ($request->has('academic_session_ids') && Schema::hasColumn('admission_fee_setups', 'academic_session_ids')) {
            $v = $request->input('academic_session_ids');
            $payload['academic_session_ids'] = is_array($v) ? json_encode(array_values($v)) : $v;
        }

        if (Schema::hasColumn('admission_fee_setups', 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::hasColumn('admission_fee_setups', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $id = DB::table('admission_fee_setups')->insertGetId($payload);

        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'account_head_id' => ['required'],
            'amount' => ['required'],
            'store_id' => ['required'],
            'store_password' => ['required'],
        ]);

        if (!Schema::hasTable('admission_fee_setups')) {
            return response()->json(['message' => 'Admission fee setup module not ready'], 422);
        }

        $row = DB::table('admission_fee_setups')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Admission fee setup not found'], 404);
        }

        $payload = [];
        foreach ([
            'account_head_id',
            'amount',
            'store_id',
            'store_password',
            'account_no',
            'enable_date',
            'enable_roll',
            'start_date',
            'expire_date',
            'additional_date',
            'session',
            'admission_roll_min_value',
            'admission_roll_max_value',
        ] as $col) {
            if ($request->has($col) && Schema::hasColumn('admission_fee_setups', $col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if ($request->has('academic_session_ids') && Schema::hasColumn('admission_fee_setups', 'academic_session_ids')) {
            $v = $request->input('academic_session_ids');
            $payload['academic_session_ids'] = is_array($v) ? json_encode(array_values($v)) : $v;
        }

        if (Schema::hasColumn('admission_fee_setups', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('admission_fee_setups')->where('id', (int) $id)->update($payload);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy(Request $request, $id)
    {
        if (!Schema::hasTable('admission_fee_setups')) {
            return response()->json(['message' => 'Admission fee setup module not ready'], 422);
        }

        $row = DB::table('admission_fee_setups')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Admission fee setup not found'], 404);
        }

        DB::table('admission_fee_setups')->where('id', (int) $id)->delete();

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }
}
