<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Calender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class CalenderController extends BaseController
{
    public function index(Request $request)
    {
        $query = Calender::latest('id');
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
            $path = $collegeName . "/calender";

            if ($request->hasFile('file')) {
                $data['file'] = $this->upload($request->file('file'), $path, null);
            }

            $bus = Calender::create($data);

            Artisan::call('cache:clear');

            return response()->json(['message' => 'Calender created successfully!', 'id' => $bus->id], 200);
        } catch (\Exception $ex) {
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    public function show(Request $request, Calender $calender)
    {
        // if ($request->format() == 'html') {
        //     return view('layouts.backend_app');
        // }

        $data = $calender;
        return $data;
    }

    public function edit(Calender $Calender)
    {
        return $Calender;
        // return view('layouts.backend_app');
    }

    public function update(Request $request, Calender $calender)
    {
        if ($this->validateCheck($request, $calender->id)) {
            try {
                $data = $request->all();

                $collegeName = env('COLLEGE_NAME', 'default_college');
                $path = $collegeName . "/calender";

                if ($request->hasFile('file')) {
                    // New file uploaded
                    $data['file'] = $this->upload($request->file('file'), $path, $calender->file);
                } elseif ($request->has('existing_file')) {
                    // Use existing file path
                    $data['file'] = $request->existing_file;
                } else {
                    $data['file'] = null;
                }

                $calender->update($data);

                Artisan::call('cache:clear');
                return response()->json(['message' => 'Update Successfully!'], 200);
            } catch (\Exception $ex) {
                return response()->json(['exception' => $ex->getMessage()], 422);
            }
        }
    }


    public function destroy(Calender $calender)
    {
        try {
            $old = $this->oldFile($calender->file);

            if (Storage::disk('public')->exists($old)) {
                Storage::delete($old);
            }

            if ($calender->delete()) {
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


