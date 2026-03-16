<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToCollection;

class TeachersImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        if (!Schema::hasTable('admins') || !Schema::hasTable('teachers')) {
            return;
        }

        $roleId = null;
        if (Schema::hasTable('roles')) {
            $r = DB::table('roles')->where('type', 'Teacher')->orderBy('id')->first();
            $roleId = $r?->id ? (int) $r->id : null;
        }

        $adminCols = Schema::hasTable('admins') ? Schema::getColumnListing('admins') : [];
        $teacherCols = Schema::hasTable('teachers') ? Schema::getColumnListing('teachers') : [];

        foreach ($rows as $key => $row) {
            if ($key === 0) {
                continue;
            }

            $type = isset($row[0]) ? trim((string) $row[0]) : 'Teacher';
            $name = isset($row[1]) ? trim((string) $row[1]) : '';
            $mobile = isset($row[2]) ? trim((string) $row[2]) : '';
            $email = isset($row[3]) ? trim((string) $row[3]) : '';

            if ($email === '' || $name === '') {
                continue;
            }

            $exists = DB::table('admins')->where('email', $email)->exists();
            if ($exists) {
                continue;
            }

            $passwordPlain = (string) random_int(111111, 999999);

            $payload = [];
            if (in_array('type', $adminCols, true)) {
                $payload['type'] = $type ?: 'Teacher';
            }
            if (in_array('name', $adminCols, true)) {
                $payload['name'] = $name;
            }
            if (in_array('mobile', $adminCols, true) && $mobile !== '') {
                $payload['mobile'] = $mobile;
            }
            if (in_array('email', $adminCols, true)) {
                $payload['email'] = $email;
            }
            if (in_array('status', $adminCols, true)) {
                $payload['status'] = 'active';
            }
            if ($roleId && in_array('role_id', $adminCols, true)) {
                $payload['role_id'] = $roleId;
            }
            if (in_array('password', $adminCols, true)) {
                $payload['password'] = Hash::make($passwordPlain);
            }
            if (in_array('created_at', $adminCols, true)) {
                $payload['created_at'] = now();
            }
            if (in_array('updated_at', $adminCols, true)) {
                $payload['updated_at'] = now();
            }

            $adminId = DB::table('admins')->insertGetId($payload);

            if ($adminId && in_array('admin_id', $teacherCols, true)) {
                $t = ['admin_id' => (int) $adminId];
                if (in_array('created_at', $teacherCols, true)) {
                    $t['created_at'] = now();
                }
                if (in_array('updated_at', $teacherCols, true)) {
                    $t['updated_at'] = now();
                }
                DB::table('teachers')->insert($t);
            }
        }
    }
}
