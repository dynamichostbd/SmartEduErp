<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\ExamRoutine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ExamRoutineController extends BaseController
{
    public function index(Request $request)
    {
        $query = ExamRoutine::latest('id');
        $datas = $query->paginate($request->pagination);
        return new Resource($datas);
    }

    public function store(Request $request)
    {
        $this->validateCheck($request);
        // dd($request->all());

        try {
            $data = $request->only('title', 'file_type');

            $collegeName = env('COLLEGE_NAME', 'default_college');
            $path = $collegeName . "/exam_routine";

            if ($request->hasFile('file')) {
                $data['file'] = $this->upload($request->file('file'), $path, null);
            }

            $class = ExamRoutine::create($data);

            Artisan::call('cache:clear');

            return response()->json(['message' => 'Exam Routine created successfully!', 'id' => $class->id], 200);
        } catch (\Exception $ex) {
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    public function show(Request $request, ExamRoutine $examR)
    {
        // if ($request->format() == 'html') {
        //     return view('layouts.backend_app');
        // }

        $data = $examR;
        // dd($data);

        return $data;
    }

    public function edit(ExamRoutine $ExamRoutine)
    {
        // return view('layouts.backend_app');
        return $ExamRoutine;
    }

    public function update(Request $request, ExamRoutine $examR)
    {
        if ($this->validateCheck($request, $examR->id)) {
            try {
                $data = $request->all();
                $collegeName = env('COLLEGE_NAME', 'default_college');
                $path = $collegeName . "/exam_routine";

                if ($request->hasFile('file')) {
                    // New file uploaded
                    $data['file'] = $this->upload($request->file('file'), $path, $examR->file);
                } elseif ($request->has('existing_file')) {
                    $data['file'] = $request->existing_file;
                } else {
                    $data['file'] = null;
                }

                $examR->update($data);

                Artisan::call('cache:clear');
                return response()->json(['message' => 'Update Successfully!'], 200);
            } catch (\Exception $ex) {
                return response()->json(['exception' => $ex->getMessage()], 422);
            }
        }
    }


    public function destroy(ExamRoutine $examR)
    {
        try {
            $old = $this->oldFile($examR->file);

            if (Storage::disk('public')->exists($old)) {
                Storage::delete($old);
            }

            $examR->delete();

            Artisan::call('cache:clear');
            return response()->json(['message' => 'Delete Successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Delete Unsuccessful!',
                'exception' => $e->getMessage()
            ], 422);
        }
    }

    public function validateCheck($request, $id = null)
    {
        $rules = [
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];

        if ($id) {
            $rules['file'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048';
        }

        return $request->validate($rules);
    }
}


