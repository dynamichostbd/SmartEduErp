<?php

namespace App\Http\Controllers\Backend\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('notices') ? 'notices' : null;
    }

    private function assignTable(): ?string
    {
        return Schema::hasTable('notice_assignables') ? 'notice_assignables' : null;
    }

    private function uploadDir(): string
    {
        $collegeName = env('COLLEGE_NAME', 'default_college');
        return "upload/{$collegeName}/notice";
    }

    private function normalizeStoredPath(?string $path): ?string
    {
        if (!$path) return null;
        return preg_replace('/^upload\//', '', (string) $path);
    }

    private function deleteFileIfExists(?string $storedPath): void
    {
        if (!$storedPath) return;
        $candidates = [$storedPath, "upload/{$storedPath}"];
        foreach ($candidates as $c) {
            try {
                if (Storage::disk('public')->exists($c)) {
                    Storage::disk('public')->delete($c);
                }
            } catch (\Throwable $e) {
            }
        }
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

        $q = DB::table($table)->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'title');
        $value = trim((string) ($request->input('value') ?? ''));
        if ($value !== '' && in_array($field, ['title'], true) && Schema::hasColumn($table, $field)) {
            $q->where($field, 'like', "%{$value}%");
        }

        $type = trim((string) ($request->input('type') ?? ''));
        if ($type !== '' && Schema::hasColumn($table, 'type')) {
            $q->where('type', $type);
        }

        return response()->json($q->paginate($perPage));
    }

    public function officeOrder(Request $request)
    {
        $request->merge(['type' => 'office']);
        return $this->index($request);
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Notice module not ready'], 422);
        }

        $columns = Schema::getColumnListing($table);

        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) continue;
            if ($col === 'image') continue;
            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if (!isset($payload['date']) && in_array('date', $columns, true)) {
            $payload['date'] = now()->toDateString();
        }

        if ($request->hasFile('image') && in_array('image', $columns, true)) {
            $file = $request->file('image');
            if ($file && $file->isValid()) {
                $path = $file->store($this->uploadDir(), 'public');
                $payload['image'] = $this->normalizeStoredPath($path);
            }
        }

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }
        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        $id = DB::table($table)->insertGetId($payload);

        $assignTable = $this->assignTable();
        if ($assignTable && empty($payload['all_dept'])) {
            $assignables = $request->input('assignables');
            $arr = [];
            if (is_string($assignables)) {
                $decoded = json_decode($assignables, true);
                $arr = is_array($decoded) ? $decoded : [];
            } elseif (is_array($assignables)) {
                $arr = $assignables;
            }

            foreach ($arr as $row) {
                if (!is_array($row)) continue;
                $ins = ['notice_id' => (int) $id];
                foreach (['department_id', 'academic_qualification_id', 'academic_class_id'] as $k) {
                    if (Schema::hasColumn($assignTable, $k)) {
                        $ins[$k] = $row[$k] ?? null;
                    }
                }
                DB::table($assignTable)->insert($ins);
            }
        }

        return response()->json(['message' => 'Create Successfully!', 'id' => $id], 200);
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        $table = $this->table();
        if (!$table) return response()->json([], 404);

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) return response()->json([], 404);

        $out = (array) $row;

        $assignTable = $this->assignTable();
        if ($assignTable) {
            $out['assignables'] = DB::table($assignTable)
                ->where('notice_id', (int) $id)
                ->get(['department_id', 'academic_qualification_id', 'academic_class_id'])
                ->map(function ($r) {
                    return [
                        'department_id' => $r->department_id ?? null,
                        'academic_qualification_id' => $r->academic_qualification_id ?? null,
                        'academic_class_id' => $r->academic_class_id ?? null,
                    ];
                })
                ->toArray();
        } else {
            $out['assignables'] = [];
        }

        return response()->json($out);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Notice module not ready'], 422);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $columns = Schema::getColumnListing($table);

        $payload = [];
        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) continue;
            if ($col === 'image') continue;
            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        if ($request->hasFile('image') && in_array('image', $columns, true)) {
            $file = $request->file('image');
            if ($file && $file->isValid()) {
                if (property_exists($row, 'image')) {
                    $this->deleteFileIfExists($row->image);
                }
                $path = $file->store($this->uploadDir(), 'public');
                $payload['image'] = $this->normalizeStoredPath($path);
            }
        } elseif ($request->has('existing_file') && in_array('image', $columns, true)) {
            $payload['image'] = $request->input('existing_file');
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        DB::table($table)->where('id', (int) $id)->update($payload);

        $assignTable = $this->assignTable();
        if ($assignTable) {
            DB::table($assignTable)->where('notice_id', (int) $id)->delete();

            if (empty($payload['all_dept'])) {
                $assignables = $request->input('assignables');
                $arr = [];
                if (is_string($assignables)) {
                    $decoded = json_decode($assignables, true);
                    $arr = is_array($decoded) ? $decoded : [];
                } elseif (is_array($assignables)) {
                    $arr = $assignables;
                }

                foreach ($arr as $row2) {
                    if (!is_array($row2)) continue;
                    $ins = ['notice_id' => (int) $id];
                    foreach (['department_id', 'academic_qualification_id', 'academic_class_id'] as $k) {
                        if (Schema::hasColumn($assignTable, $k)) {
                            $ins[$k] = $row2[$k] ?? null;
                        }
                    }
                    DB::table($assignTable)->insert($ins);
                }
            }
        }

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Notice module not ready'], 422);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (property_exists($row, 'image')) {
            $this->deleteFileIfExists($row->image);
        }

        $assignTable = $this->assignTable();
        if ($assignTable) {
            DB::table($assignTable)->where('notice_id', (int) $id)->delete();
        }

        DB::table($table)->where('id', (int) $id)->delete();

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }
}
