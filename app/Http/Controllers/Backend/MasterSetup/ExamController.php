<?php

namespace App\Http\Controllers\Backend\MasterSetup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExamController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('exams') ? 'exams' : null;
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

        $q = DB::table("{$table} as e")
            ->leftJoin("{$table} as ct", 'ct.id', '=', 'e.class_test_exam_id')
            ->orderByDesc('e.id');

        $select = ['e.id'];
        foreach (['name', 'exam_type', 'class_test_exam_id', 'description', 'status'] as $c) {
            if (in_array($c, $cols, true)) {
                $select[] = "e.{$c}";
            }
        }
        $select[] = 'ct.name as class_test_exam_name';

        $q->select($select);

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['name', 'exam_type'], true) && in_array($field, $cols, true)) {
            $q->where("e.{$field}", 'like', "%{$value}%");
        }

        if ($request->has('exam_type') && in_array('exam_type', $cols, true)) {
            $type = trim((string) $request->input('exam_type'));
            if ($type !== '') {
                $q->where('e.exam_type', $type);
            }
        }

        if ($request->boolean('allData')) {
            if ($request->boolean('pluck')) {
                return response()->json($q->pluck('e.name', 'e.id'));
            }
            return response()->json($q->get());
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Exam module not ready'], 422);
        }

        $request->validate([
            'name' => ['required'],
            'exam_type' => ['required'],
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

        return response()->json(['exam' => $row], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Exam module not ready'], 422);
        }

        $request->validate([
            'name' => ['required'],
            'exam_type' => ['required'],
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
            return response()->json(['message' => 'Exam module not ready'], 422);
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
