<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeacherAttendanceController extends Controller
{
    private function table(): ?string
    {
        return Schema::hasTable('teacher_attendances') ? 'teacher_attendances' : null;
    }

    private function detailsTable(): ?string
    {
        return Schema::hasTable('teacher_attendance_details') ? 'teacher_attendance_details' : null;
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

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $table = $this->table();
        if (!$table) {
            return $this->emptyPaginator($perPage);
        }

        $q = DB::table($table);
        if (Schema::hasColumn($table, 'date')) {
            $from = trim((string) ($request->input('from_date') ?? ''));
            $to = trim((string) ($request->input('to_date') ?? ''));
            if ($from !== '') {
                $q->whereDate('date', '>=', $from);
            }
            if ($to !== '') {
                $q->whereDate('date', '<=', $to);
            }
            $q->orderByDesc('date');
        } else {
            $q->orderByDesc('id');
        }

        return response()->json($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $table = $this->table();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Teacher Attendance module not ready'], 422);
        }

        $request->validate([
            'date' => ['required'],
            'details' => ['required', 'array'],
        ]);

        $data = [];
        foreach (['date', 'total_teacher', 'total_present'] as $f) {
            if ($request->has($f) && Schema::hasColumn($table, $f)) {
                $data[$f] = $request->input($f);
            }
        }
        if (Schema::hasColumn($table, 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::beginTransaction();
        try {
            $id = DB::table($table)->insertGetId($data);

            $rows = [];
            foreach (($request->input('details') ?? []) as $d) {
                if (!is_array($d)) {
                    continue;
                }
                $row = [];
                foreach (['admin_id', 'in_time', 'out_time', 'status'] as $f) {
                    if (array_key_exists($f, $d) && Schema::hasColumn($detailsTable, $f)) {
                        $row[$f] = $d[$f];
                    }
                }
                if (Schema::hasColumn($detailsTable, 'teacher_attendance_id')) {
                    $row['teacher_attendance_id'] = $id;
                }
                if (Schema::hasColumn($detailsTable, 'created_at')) {
                    $row['created_at'] = now();
                }
                if (Schema::hasColumn($detailsTable, 'updated_at')) {
                    $row['updated_at'] = now();
                }
                $rows[] = $row;
            }

            if (!empty($rows)) {
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
            return response()->json([], 404);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json([], 404);
        }

        $details = DB::table($detailsTable)->where('teacher_attendance_id', (int) $id)->get();

        return response()->json(['attendance' => $row, 'details' => $details], 200);
    }

    public function update(Request $request, $id)
    {
        $table = $this->table();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Teacher Attendance module not ready'], 422);
        }

        $row = DB::table($table)->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }

        $request->validate([
            'date' => ['required'],
            'details' => ['required', 'array'],
        ]);

        $data = [];
        foreach (['date', 'total_teacher', 'total_present'] as $f) {
            if ($request->has($f) && Schema::hasColumn($table, $f)) {
                $data[$f] = $request->input($f);
            }
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::beginTransaction();
        try {
            DB::table($table)->where('id', (int) $id)->update($data);

            DB::table($detailsTable)->where('teacher_attendance_id', (int) $id)->delete();

            $rows = [];
            foreach (($request->input('details') ?? []) as $d) {
                if (!is_array($d)) {
                    continue;
                }
                $rowData = [];
                foreach (['admin_id', 'in_time', 'out_time', 'status'] as $f) {
                    if (array_key_exists($f, $d) && Schema::hasColumn($detailsTable, $f)) {
                        $rowData[$f] = $d[$f];
                    }
                }
                if (Schema::hasColumn($detailsTable, 'teacher_attendance_id')) {
                    $rowData['teacher_attendance_id'] = (int) $id;
                }
                if (Schema::hasColumn($detailsTable, 'created_at')) {
                    $rowData['created_at'] = now();
                }
                if (Schema::hasColumn($detailsTable, 'updated_at')) {
                    $rowData['updated_at'] = now();
                }
                $rows[] = $rowData;
            }

            if (!empty($rows)) {
                DB::table($detailsTable)->insert($rows);
            }

            DB::commit();
            return response()->json(['message' => 'Update Successfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update', 'exception' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        $table = $this->table();
        $detailsTable = $this->detailsTable();
        if (!$table || !$detailsTable) {
            return response()->json(['message' => 'Teacher Attendance module not ready'], 422);
        }

        DB::beginTransaction();
        try {
            DB::table($detailsTable)->where('teacher_attendance_id', (int) $id)->delete();
            $ok = (bool) DB::table($table)->where('id', (int) $id)->delete();
            DB::commit();
            return response()->json(['message' => $ok ? 'Delete Successfully!' : 'Delete Unsuccessfully!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Delete failed', 'exception' => $e->getMessage()], 422);
        }
    }
}
