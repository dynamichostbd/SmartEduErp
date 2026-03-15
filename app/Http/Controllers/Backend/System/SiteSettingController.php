<?php

namespace App\Http\Controllers\Backend\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiteSettingController extends Controller
{
    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('site_settings')) {
            return response()->json([], 404);
        }

        $row = DB::table('site_settings')->where('id', (int) $id)->first();
        if (!$row) {
            $row = DB::table('site_settings')->first();
        }

        return response()->json($row ?? []);
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('site_settings')) {
            return response()->json(['message' => 'Site Setting module not ready'], 422);
        }

        $row = DB::table('site_settings')->where('id', (int) $id)->first();
        if (!$row) {
            return response()->json(['message' => 'Site Setting not found'], 404);
        }

        $columns = Schema::getColumnListing('site_settings');

        $imageFields = [
            'logo',
            'logo_small',
            'favicon',
            'idcard_front_part',
            'idcard_back_part',
            'admit_card_image',
            'marksheet_image',
            'online_admission_form_image',
            'teacher_id_card_front',
            'teacher_id_card_back',
            'principle_signature',
            'admin_admit_card',
            'seat_card',
            'admin_admit_card_front',
            'admin_admit_card_back',
        ];

        $payload = [];

        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }

            if (in_array($col, $imageFields, true)) {
                continue;
            }

            if ($request->has($col)) {
                $payload[$col] = $request->input($col);
            }
        }

        foreach ($imageFields as $field) {
            if (!in_array($field, $columns, true)) {
                continue;
            }

            if ($request->hasFile($field)) {
                $file = $request->file($field);
                if ($file && $file->isValid()) {
                    $path = $file->store('upload/conf', 'public');
                    $payload[$field] = preg_replace('/^upload\//', '', $path);
                }
            }
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        DB::table('site_settings')->where('id', (int) $id)->update($payload);

        Cache::forget('site_setting_cache');

        return response()->json(['message' => 'Update Successfully!'], 200);
    }
}
