<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\ClassRoutine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ClassRoutineController extends BaseController
{
    public function index(Request $request)
    {
        $query = ClassRoutine::latest('id');
        $datas = $query->paginate($request->pagination);
        return new Resource($datas);
    }

    public function store(Request $request)
    {
        $this->validateCheck($request);
        // dd('heat');

        try {
            $data = $request->only('title', 'file_type');

            $collegeName = env('COLLEGE_NAME', 'default_college');
            $path = $collegeName . "/class_routine";

            if ($request->hasFile('file')) {
                $data['file'] = $this->upload($request->file('file'), $path, null);
            }

            $class = ClassRoutine::create($data);

            Artisan::call('cache:clear');

            return response()->json(['message' => 'Class Routine created successfully!', 'id' => $class->id], 200);
        } catch (\Exception $ex) {
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    public function show(Request $request, ClassRoutine $class)
    {
        // if ($request->format() == 'html') {
        //     return view('layouts.backend_app');
        // }

        $data = $class;
        return $data;
    }

    public function edit(ClassRoutine $ClassRoutine)
    {
        // return view('layouts.backend_app');
        return $ClassRoutine;
    }

    public function update(Request $request, ClassRoutine $class)
    {
        if ($this->validateCheck($request, $class->id)) {
            try {
                $data = $request->all();

                $collegeName = env('COLLEGE_NAME', 'default_college');
                $path = $collegeName . "/class_routine";

                if ($request->hasFile('file')) {
                    // New file uploaded
                    $data['file'] = $this->upload($request->file('file'), $path, $class->file);
                } elseif ($request->has('existing_file')) {
                    $data['file'] = $request->existing_file;
                } else {
                    $data['file'] = null;
                }

                $class->update($data);

                Artisan::call('cache:clear');
                return response()->json(['message' => 'Update Successfully!'], 200);
            } catch (\Exception $ex) {
                return response()->json(['exception' => $ex->getMessage()], 422);
            }
        }
    }


    public function destroy(ClassRoutine $class)
    {
        try {
            $old = $this->oldFile($class->file);

            if (Storage::disk('public')->exists($old)) {
                Storage::delete($old);
            }

            if ($class->delete()) {
                Artisan::call('cache:clear');
                return response()->json(['message' => 'Delete Successfully!'], 200);
            } else {
                return response()->json(['error' => 'Delete Unsuccessfully!'], 200);
            }
        } catch (\Exception $ex) {
            return response()->json([
                'error' => 'Delete Unsuccessful!',
                'exception' => $ex->getMessage()
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


