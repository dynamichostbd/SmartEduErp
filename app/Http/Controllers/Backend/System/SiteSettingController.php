<?php

namespace App\Http\Controllers\Backend\System;

use App\Http\Controllers\Controller;
use App\Models\System\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

class SiteSettingController extends Controller
{
    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        // Prefer cached site settings for speed (also includes accessor-resolved image URLs)
        try {
            $cached = App::make('siteSettingObj');
            return response()->json(is_array($cached) ? $cached : []);
        } catch (\Throwable $e) {
            // fallback below
        }

        if (!Schema::hasTable('site_settings')) {
            return response()->json([], 404);
        }

        $site = SiteSetting::query()->where('id', (int) $id)->first() ?: SiteSetting::query()->first();
        return response()->json($site ? $site->toArray() : []);
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
                    $disk = config('filesystems.default');
                    if (config('filesystems.disks.spaces') && env('DO_ASSET_URL')) {
                        $disk = 'spaces';
                    }

                    $collegeName = (string) (config('app.college') ?: env('COLLEGE_NAME', 'default_college'));
                    $collegeName = trim($collegeName);
                    $dir = trim($collegeName, '/');
                    $dir = ($dir !== '' ? $dir : 'default_college') . '/conf';

                    $path = $file->storePublicly($dir, ['disk' => $disk]);
                    $payload[$field] = ltrim((string) $path, '/');

                    // Best-effort delete old file from default disk when possible
                    $old = $row->{$field} ?? null;
                    $old = is_string($old) ? trim($old) : '';
                    if ($old !== '') {
                        $old = ltrim($old, '/');
                        try {
                            Storage::disk($disk)->delete($old);
                        } catch (\Throwable $e) {
                            // ignore
                        }

                        if (str_starts_with($old, 'conf/')) {
                            try {
                                Storage::disk($disk)->delete('upload/' . $old);
                            } catch (\Throwable $e) {
                                // ignore
                            }
                        }
                    }
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
