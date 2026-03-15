<?php

namespace App\Http\Controllers\Backend\MasterSetup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubjectController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('subjects') ? 'subjects' : null;
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

    public function allSubjects(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json([]);
        }

        $q = DB::table($table);
        if (Schema::hasColumn($table, 'is_child')) {
            $q->where('is_child', 0);
        }

        $nameCol = Schema::hasColumn($table, 'name_en') ? 'name_en' : (Schema::hasColumn($table, 'name') ? 'name' : null);
        $select = ['id'];
        if ($nameCol) {
            $select[] = "{$nameCol} as name";
        }

        return response()->json($q->select($select)->orderBy('id')->get());
    }

    public function allChildSubjects(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json([]);
        }

        $q = DB::table($table);
        if (Schema::hasColumn($table, 'is_child')) {
            $q->where('is_child', 1);
        }

        $nameCol = Schema::hasColumn($table, 'name_en') ? 'name_en' : (Schema::hasColumn($table, 'name') ? 'name' : null);
        $select = ['id'];
        if ($nameCol) {
            $select[] = "{$nameCol} as name";
        }
        if (Schema::hasColumn($table, 'parent_id')) {
            $select[] = 'parent_id';
        }

        return response()->json($q->select($select)->orderBy('id')->get());
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
        $q = DB::table($table);

        if (in_array('sorting', $cols, true)) {
            $q->orderBy('sorting', 'asc');
        } else {
            $q->orderByDesc('id');
        }

        $field = (string) ($request->input('field_name') ?? 'name_en');
        $value = trim((string) ($request->input('value') ?? ''));
        $allowed = ['name_en', 'name_bn', 'name', 'sorting'];
        if ($value !== '' && in_array($field, $allowed, true) && in_array($field, $cols, true)) {
            $q->where($field, 'like', "%{$value}%");
        }

        if ($request->has('is_child') && in_array('is_child', $cols, true)) {
            $isChild = trim((string) $request->input('is_child'));
            if ($isChild !== '') {
                $q->where('is_child', (int) $isChild);
            }
        }

        if ($request->boolean('allData')) {
            $select = ['id'];
            if (in_array('name_en', $cols, true)) {
                $select[] = 'name_en as name';
            } elseif (in_array('name', $cols, true)) {
                $select[] = 'name';
            }
            if (in_array('parent_id', $cols, true)) {
                $select[] = 'parent_id';
            }
            if (in_array('is_child', $cols, true)) {
                $select[] = 'is_child';
            }
            return response()->json($q->select($select)->get());
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Subject module not ready'], 422);
        }

        $request->validate([
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

        if (in_array('is_child', $columns, true)) {
            $payload['is_child'] = (int) ($request->input('is_child') ? 1 : 0);
            if (!$payload['is_child'] && in_array('parent_id', $columns, true)) {
                $payload['parent_id'] = null;
            }
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

        if (!isset($payload['sorting']) && in_array('sorting', $columns, true)) {
            $payload['sorting'] = 1;
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

        return response()->json(['subject' => $row], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        if (!$table) {
            return response()->json(['message' => 'Subject module not ready'], 422);
        }

        $request->validate([
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

        if (in_array('is_child', $columns, true)) {
            $payload['is_child'] = (int) ($request->input('is_child') ? 1 : 0);
            if (!$payload['is_child'] && in_array('parent_id', $columns, true)) {
                $payload['parent_id'] = null;
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
            return response()->json(['message' => 'Subject module not ready'], 422);
        }

        $ok = DB::table($table)->where('id', (int) $id)->delete();
        if ($ok) {
            $this->flushCaches();
        }

        return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
    }
}
