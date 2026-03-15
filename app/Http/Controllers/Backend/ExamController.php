<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('exams')) {
            return response()->json([]);
        }

        $query = DB::table('exams')->select(['id', 'name', 'exam_type', 'class_test_exam_id']);

        $examType = $request->input('exam_type');
        if ($examType !== null && $examType !== '') {
            $query->where('exam_type', $examType);
        }

        $field = (string) ($request->input('field_name') ?? 'name');
        $value = $request->input('value');
        if ($value !== null && $value !== '') {
            if (in_array($field, ['name', 'exam_type'], true)) {
                $query->where($field, 'like', '%' . $value . '%');
            }
        }

        $query->orderByDesc('id');

        if ($request->boolean('allData')) {
            return response()->json($query->get());
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        return response()->json($query->paginate($perPage));
    }
}
