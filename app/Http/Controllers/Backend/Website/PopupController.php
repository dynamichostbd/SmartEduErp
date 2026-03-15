<?php

namespace App\Http\Controllers\Backend\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PopupController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('popups') ? 'popups' : null;
    }

    private function uploadDir(): string
    {
        $collegeName = env('COLLEGE_NAME', 'default_college');
        return "upload/{$collegeName}/popup";
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

        if ($request->has('type') && Schema::hasColumn($table, 'type')) {
            $type = trim((string) $request->input('type'));
            if ($type !== '') {
                $q->where('type', $type);
            }
        }

        if ($request->has('status') && Schema::hasColumn($table, 'status')) {
            $status = trim((string) $request->input('status'));
            if ($status !== '') {
                $q->where('status', $status);
            }
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Popup module not ready'], 422);
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

        if (!isset($payload['status']) && in_array('status', $columns, true)) {
            $payload['status'] = 'active';
        }

        if (in_array('status', $columns, true) && in_array('type', $columns, true)) {
            if (($payload['status'] ?? '') === 'active') {
                DB::table($table)
                    ->where('status', 'active')
                    ->where('type', $payload['type'] ?? 'admin')
                    ->update(['status' => 'deactive']);
            }
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

        return response()->json($row);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Popup module not ready'], 422);
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

        if (in_array('status', $columns, true) && in_array('type', $columns, true)) {
            if (($payload['status'] ?? '') === 'active') {
                DB::table($table)
                    ->where('status', 'active')
                    ->where('type', $payload['type'] ?? ($row->type ?? 'admin'))
                    ->where('id', '!=', (int) $id)
                    ->update(['status' => 'deactive']);
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
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        DB::table($table)->where('id', (int) $id)->update($payload);

        return response()->json(['message' => 'Update Successfully!'], 200);
    }

    public function destroy(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Popup module not ready'], 422);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (property_exists($row, 'image')) {
            $this->deleteFileIfExists($row->image);
        }

        DB::table($table)->where('id', (int) $id)->delete();

        return response()->json(['message' => 'Delete Successfully!'], 200);
    }
}
