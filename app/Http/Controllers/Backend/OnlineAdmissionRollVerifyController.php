<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Imports\OnlineAdmissionRollVerifiesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class OnlineAdmissionRollVerifyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $adminDeptId = Auth::guard('admin')->user()->department_id ?? null;
        $departmentId = $request->input('department_id') ?: $adminDeptId;

        $query = DB::table('online_admission_roll_verifies as v')
            ->leftJoin('academic_sessions as ses', 'ses.id', '=', 'v.academic_session_id')
            ->leftJoin('academic_qualifications as q', 'q.id', '=', 'v.academic_qualification_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'v.department_id')
            ->leftJoin('academic_classes as cls', 'cls.id', '=', 'v.academic_class_id')
            ->select([
                'v.id',
                'v.academic_session_id',
                'v.academic_qualification_id',
                'v.department_id',
                'v.academic_class_id',
                'v.created_at',
                'ses.name as academic_session_name',
                'q.name as academic_qualification_name',
                'dept.name as department_name',
                'cls.name as academic_class_name',
            ])
            ->orderByDesc('v.id');

        foreach (['academic_session_id', 'academic_qualification_id', 'academic_class_id'] as $f) {
            if ($request->filled($f)) {
                $query->where('v.' . $f, $request->input($f));
            }
        }

        if (!empty($departmentId)) {
            $query->where('v.department_id', $departmentId);
        }

        $datas = $query->paginate($perPage);

        return response()->json([
            'data' => $datas->items(),
            'meta' => [
                'current_page' => $datas->currentPage(),
                'from' => $datas->firstItem(),
                'to' => $datas->lastItem(),
                'per_page' => $datas->perPage(),
                'total' => $datas->total(),
                'last_page' => $datas->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_session_id' => ['required', 'integer'],
            'academic_qualification_id' => ['required', 'integer'],
            'department_id' => ['required', 'integer'],
            'academic_class_id' => ['required', 'integer'],
            'excel_file' => ['required', 'file'],
        ]);

        $import = new OnlineAdmissionRollVerifiesImport([
            'academic_session_id' => (int) $request->input('academic_session_id'),
            'academic_qualification_id' => (int) $request->input('academic_qualification_id'),
            'department_id' => (int) $request->input('department_id'),
            'academic_class_id' => (int) $request->input('academic_class_id'),
        ]);

        Excel::import($import, $request->file('excel_file'));

        return response()->json(['message' => 'Import Successfully'], 200);
    }

    public function show($id)
    {
        $row = DB::table('online_admission_roll_verifies')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $session = DB::table('academic_sessions')->select('id', 'name')->where('id', $row->academic_session_id)->first();
        $qualification = DB::table('academic_qualifications')->select('id', 'name')->where('id', $row->academic_qualification_id)->first();
        $department = DB::table('departments')->select('id', 'name')->where('id', $row->department_id)->first();
        $academicClass = DB::table('academic_classes')->select('id', 'name')->where('id', $row->academic_class_id)->first();

        $rolls = json_decode((string) ($row->roll_lists ?? '[]'), true);
        if (!is_array($rolls)) {
            $rolls = [];
        }

        $names = json_decode((string) ($row->name_lists ?? '[]'), true);
        if (!is_array($names)) {
            $names = [];
        }

        $list = collect($rolls)->map(function ($r, $idx) use ($names) {
            $name = $names[$idx] ?? null;
            return [
                'roll' => $r,
                'name' => $name,
            ];
        })->values();

        return response()->json([
            'id' => $row->id,
            'academic_session' => $session,
            'qualification' => $qualification,
            'department' => $department,
            'academic_class' => $academicClass,
            'roll_lists' => $rolls,
            'name_lists' => $names,
            'lists' => $list,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'roll_lists' => ['nullable', 'array'],
            'name_lists' => ['nullable', 'array'],
        ]);

        $row = DB::table('online_admission_roll_verifies')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $rolls = $request->input('roll_lists') ?? [];
        $names = $request->input('name_lists') ?? [];

        DB::table('online_admission_roll_verifies')->where('id', $id)->update([
            'roll_lists' => json_encode(array_values($rolls)),
            'name_lists' => json_encode(array_values($names)),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Updated Successfully'], 200);
    }

    public function destroy($id)
    {
        $row = DB::table('online_admission_roll_verifies')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        DB::table('online_admission_roll_verifies')->where('id', $id)->delete();

        return response()->json(['message' => 'Delete Successfully'], 200);
    }
}
