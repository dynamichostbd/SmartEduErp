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
    private function columnsCached(): array
    {
        return Cache::rememberForever('schema_cols_site_settings', function () {
            if (!Schema::hasTable('site_settings')) {
                return [];
            }

            try {
                return Schema::getColumnListing('site_settings');
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public function index(Request $request)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        if (!Schema::hasTable('site_settings')) {
            $perPage = (int) ($request->input('pagination') ?? 10);
            $perPage = $perPage > 0 ? min($perPage, 200) : 10;

            $p = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $perPage,
                1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return response()->json($p);
        }

        $perPage = (int) ($request->input('pagination') ?? 10);
        $perPage = $perPage > 0 ? min($perPage, 200) : 10;

        $cols = $this->columnsCached();
        $colSet = !empty($cols) ? array_fill_keys($cols, true) : [];
        $hasCol = function (string $c) use ($colSet): bool {
            return !empty($colSet) && isset($colSet[$c]);
        };

        $q = SiteSetting::query()->orderByDesc('id');

        $field = (string) ($request->input('field_name') ?? 'title');
        $value = trim((string) ($request->input('value') ?? ''));
        $allowed = ['title', 'short_title', 'contact_email', 'college_name'];
        if ($value !== '' && in_array($field, $allowed, true) && $hasCol($field)) {
            $q->where($field, 'like', "%{$value}%");
        }

        $select = ['id'];
        foreach (['title', 'short_title', 'logo', 'contact_email'] as $c) {
            if ($hasCol($c)) {
                $select[] = $c;
            }
        }
        if (count($select) === 1) {
            $select = ['*'];
        }

        return response()->json($q->select($select)->paginate($perPage));
    }

    public function show(Request $request, $id)
    {
        if (!$request->wantsJson() && $request->getRequestFormat() === 'html') {
            return view('layouts.backend_app');
        }

        // Always fetch fresh data to ensure correctness
        if (!Schema::hasTable('site_settings')) {
            return response()->json([], 404);
        }

        $site = SiteSetting::query()->where('id', (int) $id)->first() ?: SiteSetting::query()->first();
        $data = $site ? $site->toArray() : [];

        // Update cache with fresh data
        try {
            Cache::forever('site_setting_cache', $data);
            App::instance('siteSettingObj', $data);
        } catch (\Throwable $e) {
            // ignore cache errors
        }

        return response()->json($data);
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
