<?php

namespace App\Http\Controllers\Backend\MasterSetup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubjectClusterController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('subject_clusters') ? 'subject_clusters' : null;
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
            return $this->emptyPaginator($perPage);
        }

        $cols = Schema::getColumnListing($table);

        $q = DB::table("{$table} as sc")
            ->leftJoin('academic_qualifications as aq', 'aq.id', '=', 'sc.academic_qualification_id')
            ->leftJoin('departments as d', 'd.id', '=', 'sc.department_id')
            ->orderByDesc('sc.id');

        $select = ['sc.id'];
        foreach (['academic_qualification_id', 'department_id', 'name_en', 'name_bn', 'minimum_select', 'fixed_subject', 'subjects_json', 'status'] as $c) {
            if (in_array($c, $cols, true)) {
                $select[] = "sc.{$c}";
            }
        }
        $select[] = 'aq.name as qualification_name';
        $select[] = 'd.name as department_name';

        $q->select($select);

        $field = (string) ($request->input('field_name') ?? 'name_en');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['name_en', 'name_bn'], true) && in_array($field, $cols, true)) {
            $q->where("sc.{$field}", 'like', "%{$value}%");
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Subject Cluster module not ready'], 422);
        }

        $request->validate([
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'name_en' => ['required'],
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

        if (in_array('subjects_json', $columns, true)) {
            $v = $request->input('subjects_json');
            $payload['subjects_json'] = is_array($v) ? json_encode($v) : ($v ?: null);
        }

        foreach (['fixed_subject'] as $flag) {
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

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        return response()->json(['subject_cluster' => $row], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Subject Cluster module not ready'], 422);
        }

        $request->validate([
            'academic_qualification_id' => ['required'],
            'department_id' => ['required'],
            'name_en' => ['required'],
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

        if (in_array('subjects_json', $columns, true)) {
            $v = $request->input('subjects_json');
            $payload['subjects_json'] = is_array($v) ? json_encode($v) : ($v ?: null);
        }

        foreach (['fixed_subject'] as $flag) {
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
            return response()->json(['message' => 'Subject Cluster module not ready'], 422);
        }

        $ok = DB::table($table)->where('id', (int) $id)->delete();
        if ($ok) {
            $this->flushCaches();
        }

        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }
}
