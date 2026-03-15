<?php

namespace App\Http\Controllers\Backend\MasterSetup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicClassController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('academic_classes') ? 'academic_classes' : null;
    }

    private function flushCaches(): void
    {
        Cache::forget('dynamic_data_cache');
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
            return response()->json(DB::table(DB::raw('(select 1) as t'))->paginate($perPage));
        }

        $cols = Schema::getColumnListing($table);

        $q = DB::table("{$table} as c");
        if (Schema::hasTable('academic_qualifications') && in_array('academic_qualification_id', $cols, true)) {
            $q->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'c.academic_qualification_id');
        }

        $select = [];
        foreach (['id', 'academic_qualification_id', 'name', 'description', 'status', 'online_admission', 'registration', 'application_fee'] as $c) {
            if (in_array($c, $cols, true)) {
                $select[] = "c.{$c}";
            }
        }
        if (!$select) {
            $select = ['c.id'];
        }
        if (Schema::hasTable('academic_qualifications')) {
            $select[] = 'aq.name as qualification_name';
        }

        $q->select($select)->orderByDesc('c.id');

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && $field === 'name' && in_array('name', $cols, true)) {
            $q->where('c.name', 'like', "%{$value}%");
        }

        if ($request->has('academic_qualification_id') && in_array('academic_qualification_id', $cols, true)) {
            $qualId = trim((string) $request->input('academic_qualification_id'));
            if ($qualId !== '') {
                $q->where('c.academic_qualification_id', (int) $qualId);
            }
        }

        if ($request->has('status') && in_array('status', $cols, true)) {
            $status = trim((string) $request->input('status'));
            if ($status !== '') {
                $q->where('c.status', $status);
            }
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Academic Class module not ready'], 422);
        }

        $request->validate([
            'name' => ['required'],
        ]);

        $columns = Schema::getColumnListing($table);
        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }

            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        foreach (['online_admission', 'registration', 'application_fee'] as $flag) {
            if (in_array($flag, $columns, true)) {
                $payload[$flag] = (int) ($request->input($flag) ? 1 : 0);
            }
        }

        if (!isset($payload['status']) && in_array('status', $columns, true)) {
            $payload['status'] = 'active';
        }

        if (in_array('created_by', $columns, true)) {
            $payload['created_by'] = $admin->name ?? null;
        }
        if (in_array('created_ip', $columns, true)) {
            $payload['created_ip'] = $ip;
        }
        if (in_array('updated_by', $columns, true)) {
            $payload['updated_by'] = $admin->name ?? null;
        }
        if (in_array('updated_ip', $columns, true)) {
            $payload['updated_ip'] = $ip;
        }

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        try {
            $id = DB::table($table)->insertGetId($payload);
            $this->flushCaches();
            return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to create',
                'exception' => $e->getMessage(),
            ], 422);
        }
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

        $cols = Schema::getColumnListing($table);

        $q = DB::table("{$table} as c");
        if (Schema::hasTable('academic_qualifications') && in_array('academic_qualification_id', $cols, true)) {
            $q->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'c.academic_qualification_id');
        }

        $select = [];
        foreach (['id', 'academic_qualification_id', 'name', 'description', 'status', 'online_admission', 'registration', 'application_fee'] as $c) {
            if (in_array($c, $cols, true)) {
                $select[] = "c.{$c}";
            }
        }
        if (!$select) {
            $select = ['c.id'];
        }
        if (Schema::hasTable('academic_qualifications')) {
            $select[] = 'aq.name as qualification_name';
        }

        $row = $q->select($select)->where('c.id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json(['academic_class' => $row], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Academic Class module not ready'], 422);
        }

        $request->validate([
            'name' => ['required'],
        ]);

        $columns = Schema::getColumnListing($table);
        $admin = Auth::guard('admin')->user();
        $ip = (string) $request->ip();

        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }

            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        foreach (['online_admission', 'registration', 'application_fee'] as $flag) {
            if (in_array($flag, $columns, true)) {
                $payload[$flag] = (int) ($request->input($flag) ? 1 : 0);
            }
        }

        if (in_array('updated_by', $columns, true)) {
            $payload['updated_by'] = $admin->name ?? null;
        }
        if (in_array('updated_ip', $columns, true)) {
            $payload['updated_ip'] = $ip;
        }
        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        try {
            DB::table($table)->where('id', (int) $id)->update($payload);
            $this->flushCaches();
            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update',
                'exception' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Academic Class module not ready'], 422);
        }

        $columns = Schema::getColumnListing($table);

        if (in_array('status', $columns, true)) {
            $ok = DB::table($table)->where('id', (int) $id)->update([
                'status' => 'deactive',
                'updated_at' => in_array('updated_at', $columns, true) ? now() : DB::raw('updated_at'),
            ]);

            if ($ok) {
                $this->flushCaches();
            }

            return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
        }

        $ok = DB::table($table)->where('id', (int) $id)->delete();
        if ($ok) {
            $this->flushCaches();
        }

        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }
}
