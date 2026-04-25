<?php

/**
 * @Kamrul Sakil
 */

namespace App\Http\Controllers\api;

use App\Helpers\GlobalHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\SubjectAssign;
use App\Models\SubjectAssignDetails;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectAssignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = SubjectAssign::with('qualification', 'department', 'academic_class', 'details')->latest();
        $query->whereLike($request->field_name, $request->value);

        $query->whereAny('academic_class_id', $request->academic_class_id);
        $query->whereAny('department_id', $request->department_id);
        $query->whereAny('academic_qualification_id', $request->academic_qualification_id);

        $datas = $query->paginate($request->pagination);
        return new Resource($datas);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('layouts.backend_app');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($this->validateCheck($request)) {
            try {
                DB::beginTransaction();

                $details = $request->details;
                $data = $request->except('details');

                // exists check
                $exists = SubjectAssign::where([
                    'department_id' => $data['department_id'],
                    'academic_class_id' => $data['academic_class_id'],
                    'academic_qualification_id' => $data['academic_qualification_id'],
                ])->first();
                if (!empty($exists)) {
                    return response()->json(['error' => 'Sorry!! Already subject assign'], 200);
                }

                $subjectAssign = SubjectAssign::create($data);
                if ($subjectAssign) {
                    $subjectAssign->details()->createMany($details);
                }
                DB::commit();
                return response()->json(['message' => 'Create Successfully!', 'id' => $subjectAssign->id ?? ''], 200);
            } catch (Exception $ex) {
                DB::rollBack();
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SubjectAssign  $subjectAssign
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, SubjectAssign $subjectAssign)
    {
        if ($request->format() == 'html') {
            return view('layouts.backend_app');
        }
        $subjectAssign->load('details');
        return $subjectAssign;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SubjectAssign  $subjectAssign
     * @return \Illuminate\Http\Response
     */
    public function edit(SubjectAssign $subjectAssign)
    {
        return view('layouts.backend_app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SubjectAssign  $subjectAssign
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubjectAssign $subjectAssign)
    {
        if ($this->validateCheck($request, $subjectAssign->id)) {
            try {
                DB::beginTransaction();
                $data = $request->except('details');

                $subjectAssign->update($data);

                // exists check
                $count = SubjectAssign::where([
                    'department_id' => $data['department_id'],
                    'academic_class_id' => $data['academic_class_id'],
                    'academic_qualification_id' => $data['academic_qualification_id'],
                ])->count();
                if ($count == 2) {
                    DB::rollBack();
                    return response()->json(['error' => 'Sorry!! Already subject assign'], 200);
                }

                // update, delete, insert
                GlobalHelper::updelsert(
                    'subject_assign_id',
                    $subjectAssign->id,
                    SubjectAssignDetails::class,
                    $request->details
                );

                DB::commit();
                return response()->json(['message' => 'Update Successfully!'], 200);
            } catch (Exception $ex) {
                DB::rollBack();
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SubjectAssign  $subjectAssign
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubjectAssign $subjectAssign)
    {
        if ($subjectAssign->delete()) {
            return response()->json(['message' => 'Delete Successfully!'], 200);
        } else {
            return response()->json(['error' => 'Delete Unsuccessfully!'], 200);
        }
    }

    /**
     * Validate form field.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateCheck($request, $id = null)
    {
        return $request->validate([
            'details.*.subject_id' => 'required',
        ]);
    }
}


