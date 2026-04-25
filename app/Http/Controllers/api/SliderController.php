<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SliderController extends BaseController
{
    public function index(Request $request)
    {
        $query = Slider::latest('id');
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
            $path = $collegeName . "/slider";

            if ($request->hasFile('file')) {
                $data['file'] = $this->upload($request->file('file'), $path, null);
            }

            $bus = Slider::create($data);

            Artisan::call('cache:clear');

            return response()->json(['message' => 'Slider created successfully!', 'id' => $bus->id], 200);
        } catch (\Exception $ex) {
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    public function show(Request $request, Slider $Slider)
    {
        // if ($request->format() == 'html') {
        //     return view('layouts.backend_app');
        // }

        $data = $Slider;
        return $data;
    }

    public function edit(Slider $Slider)
    {
        // return view('layouts.backend_app');
        return $Slider;
    }

    public function update(Request $request, Slider $Slider)
    {
        if ($this->validateCheck($request, $Slider->id)) {
            try {
                $data = $request->all();

                $collegeName = env('COLLEGE_NAME', 'default_college');
                $path = $collegeName . "/slider";

                if ($request->hasFile('file')) {
                    // New file uploaded
                    $data['file'] = $this->upload($request->file('file'), $path, $Slider->file);
                } elseif ($request->has('existing_file')) {
                    $data['file'] = $request->existing_file;
                } else {
                    $data['file'] = null;
                }

                $Slider->update($data);

                Artisan::call('cache:clear');
                return response()->json(['message' => 'Update Successfully!'], 200);
            } catch (\Exception $ex) {
                return response()->json(['exception' => $ex->getMessage()], 422);
            }
        }
    }


    public function destroy(Slider $Slider)
    {
        try {
            $old = $this->oldFile($Slider->file);

            if (Storage::disk('public')->exists($old)) {
                Storage::delete($old);
            }

            if ($Slider->delete()) {
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
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        if ($id) {
            $rules['file'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        return $request->validate($rules);
    }
}


