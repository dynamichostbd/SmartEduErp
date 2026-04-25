<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Base\BaseController;
use App\Http\Resources\Resource;
use App\Models\BusSchedule;
use App\Traits\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class BusController extends Controller
{
    use ImageUpload;

    public function index(Request $request)
    {
        $query = BusSchedule::latest('id');
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
            $path = $collegeName . "/buses";

            if ($request->hasFile('file')) {
                $data['file'] = $this->upload($request->file('file'), $path, null);
            }

            $bus = BusSchedule::create($data);

            Artisan::call('cache:clear');

            return response()->json(['message' => 'Bus created successfully!', 'id' => $bus->id], 200);
        } catch (\Exception $ex) {
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    public function show(Request $request, BusSchedule $bus)
    {
        // if ($request->format() == 'html') {
        //     return view('layouts.backend_app');
        // }

        $data = $bus;
        return $data;
    }

    public function edit(BusSchedule $busSchedule)
    {
        // return view('layouts.backend_app');
        return $busSchedule;
    }

    public function update(Request $request, BusSchedule $bus)
    {
        if ($this->validateCheck($request, $bus->id)) {
            try {
                $data = $request->all();

                $collegeName = env('COLLEGE_NAME', 'default_college');
                $path = $collegeName . "/buses";

                if ($request->hasFile('file')) {
                    // New file uploaded
                    $data['file'] = $this->upload($request->file('file'), $path, $bus->file);
                } elseif ($request->has('existing_file')) {
                    $data['file'] = $request->existing_file;
                } else {
                    $data['file'] = null;
                }

                $bus->update($data);

                Artisan::call('cache:clear');
                return response()->json(['message' => 'Update Successfully!'], 200);
            } catch (\Exception $ex) {
                return response()->json(['exception' => $ex->getMessage()], 422);
            }
        }
    }


    public function destroy(BusSchedule $bus)
    {
        try {
            $old = $this->oldFile($bus->file);

            if (Storage::disk('public')->exists($old)) {
                Storage::delete($old);
            }

            if ($bus->delete()) {
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


