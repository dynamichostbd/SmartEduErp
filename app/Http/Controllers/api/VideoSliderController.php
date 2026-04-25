<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Base\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\VideoSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class VideoSliderController extends BaseController
{
    public function index(Request $request)
    {
        $query = VideoSlider::latest('id');
        $datas = $query->paginate($request->pagination);
        return new Resource($datas);
    }

    public function store(Request $request)
    {
        $this->validateCheck($request);
        // dd('heat');

        try {
            $data = $request->only('title', 'file_type');

            if ($request->hasFile('file')) {
                $data['file'] = $this->upload($request->file('file'), 'Videoslider', null);
            }

            $bus = VideoSlider::create($data);

            Artisan::call('cache:clear');

            return response()->json(['message' => 'VideoSlider created successfully!', 'id' => $bus->id], 200);
        } catch (\Exception $ex) {
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    public function show(Request $request, VideoSlider $VideoSlider)
    {
        // if ($request->format() == 'html') {
        //     return view('layouts.backend_app');
        // }

        $data = $VideoSlider;
        return $data;
    }

    public function edit(VideoSlider $VideoSlider)
    {
        // return view('layouts.backend_app');
        return $VideoSlider;
    }

    public function update(Request $request, VideoSlider $VideoSlider)
    {
        if ($this->validateCheck($request, $VideoSlider->id)) {
            try {
                $data = $request->all();

                if ($request->hasFile('file')) {
                    // New file uploaded
                    $data['file'] = $this->upload($request->file('file'), 'Videoslider', $VideoSlider->file);
                } elseif ($request->has('existing_file')) {
                    // Use existing file path
                    $data['file'] = $request->existing_file;
                } else {
                    $data['file'] = null;
                }

                $VideoSlider->update($data);

                Artisan::call('cache:clear');
                return response()->json(['message' => 'Update Successfully!'], 200);
            } catch (\Exception $ex) {
                return response()->json(['exception' => $ex->getMessage()], 422);
            }
        }
    }


    public function destroy(VideoSlider $VideoSlider)
    {
        try {
            $old = $this->oldFile($VideoSlider->file);

            if (Storage::disk('public')->exists($old)) {
                Storage::delete($old);
            }

            if ($VideoSlider->delete()) {
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


