<?php

namespace App\Traits\Lib;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait DynamicDataTrait
{
    protected function dbCacheSuffix(): string
    {
        $conn = (string) (config('database.default') ?? 'default');
        $db = (string) (config("database.connections.{$conn}.database") ?? '');
        $suffix = $conn . '_' . $db;
        return preg_replace('/[^A-Za-z0-9_]+/', '_', $suffix) ?: 'default';
    }

    protected function hasTableCached(string $table): bool
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }

        $key = 'schema_has_table_' . $this->dbCacheSuffix() . '_' . $table;
        return $cache[$table] = Cache::rememberForever($key, function () use ($table) {
            return Schema::hasTable($table);
        });
    }

    protected function columnsCached(string $table): array
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }

        $key = 'schema_cols_' . $this->dbCacheSuffix() . '_' . $table;
        return $cache[$table] = Cache::rememberForever($key, function () use ($table) {
            if (!Schema::hasTable($table)) {
                return [];
            }

            try {
                return Schema::getColumnListing($table);
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    protected function hasColumnCached(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $cols = $this->columnsCached($table);
        $exists = in_array($column, $cols, true);

        return $cache[$key] = $exists;
    }

    protected function dynamicData()
    {
        $key = 'dynamic_data_cache_v2_' . $this->dbCacheSuffix();
        return Cache::rememberForever($key, function () {
            $exams = [];
            if ($this->hasTableCached('exams')) {
                $examCols = ['id', 'name'];
                if ($this->hasColumnCached('exams', 'status')) {
                    $examCols[] = 'status';
                }

                $exams = DB::table('exams')
                    ->select($examCols)
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            $accountHeads = [];
            if ($this->hasTableCached('account_heads')) {
                $q = DB::table('account_heads');
                if ($this->hasColumnCached('account_heads', 'status')) {
                    $q->where('status', 'active');
                }
                $accountHeads = $q->orderBy('id')->pluck('name', 'id')->toArray();
            }

            $hostelHeads = [];
            if ($this->hasTableCached('account_heads') && $this->hasColumnCached('account_heads', 'type')) {
                $q = DB::table('account_heads')->where('type', 'hostel');
                if ($this->hasColumnCached('account_heads', 'status')) {
                    $q->where('status', 'active');
                }
                $hostelHeads = $q->orderBy('id')->pluck('name', 'id')->toArray();
            }

            $admissionHeads = [];
            if ($this->hasTableCached('account_heads')) {
                $q = DB::table('account_heads');
                if ($this->hasColumnCached('account_heads', 'status')) {
                    $q->where('status', 'active');
                }
                if ($this->hasColumnCached('account_heads', 'type')) {
                    $q->whereIn('type', ['admission', 'certificate']);
                }
                $admissionHeads = $q->orderBy('id')->pluck('name', 'id')->toArray();
            }

            $accountsCommons = [];
            if ($this->hasTableCached('payment_gateways') && $this->hasColumnCached('payment_gateways', 'store_id')) {
                $cols = ['id', 'store_id'];
                if ($this->hasColumnCached('payment_gateways', 'account_no')) {
                    $cols[] = 'account_no';
                }
                if ($this->hasColumnCached('payment_gateways', 'title')) {
                    $cols[] = 'title';
                }
                if ($this->hasColumnCached('payment_gateways', 'status')) {
                    $cols[] = 'status';
                }

                $q = DB::table('payment_gateways')->select($cols)->orderByDesc('id');
                if ($this->hasColumnCached('payment_gateways', 'status')) {
                    $q->where('status', 'active');
                }
                if ($this->hasColumnCached('payment_gateways', 'account_no')) {
                    $q->whereNotNull('account_no');
                }

                $accountsCommons = $q->get()->unique('store_id')->values()->toArray();
            }

            $accountsAdmissions = [];
            if ($this->hasTableCached('admission_fee_setups') && $this->hasColumnCached('admission_fee_setups', 'store_id')) {
                $cols = ['id', 'store_id'];
                if ($this->hasColumnCached('admission_fee_setups', 'account_no')) {
                    $cols[] = 'account_no';
                }
                $accountsAdmissions = DB::table('admission_fee_setups')
                    ->select($cols)
                    ->orderByDesc('id')
                    ->whereNotNull('account_no')
                    ->get()
                    ->unique('store_id')
                    ->values()
                    ->toArray();
            }

            $roles = [];
            if ($this->hasTableCached('roles')) {
                $cols = ['id', 'name'];
                if ($this->hasColumnCached('roles', 'status')) {
                    $cols[] = 'status';
                }

                $q = DB::table('roles')->select($cols)->orderBy('id');
                if ($this->hasColumnCached('roles', 'status')) {
                    $q->where('status', 'active');
                }
                $roles = $q->get()->toArray();
            }

            $academicClasses = [];
            if ($this->hasTableCached('academic_classes')) {
                $academicClasses = DB::table('academic_classes')
                    ->select('id', 'name', 'academic_qualification_id', 'status')
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            $academicQualifications = [];
            if ($this->hasTableCached('academic_qualifications')) {
                $q = DB::table('academic_qualifications');

                $select = ['id', 'name'];
                if ($this->hasColumnCached('academic_qualifications', 'status')) {
                    $select[] = 'status';
                }
                foreach (['application_fees', 'admission_roll_verify', 'subject_choose', 'migration', 'registration', 'online_admission', 'application_fee'] as $col) {
                    if ($this->hasColumnCached('academic_qualifications', $col)) {
                        $select[] = $col;
                    }
                }
                foreach (['commitment', 'admission_files'] as $col) {
                    if ($this->hasColumnCached('academic_qualifications', $col)) {
                        $select[] = $col;
                    }
                }

                $academicQualifications = $q
                    ->select($select)
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            $departments = [];
            if ($this->hasTableCached('departments')) {
                $departments = DB::table('departments')
                    ->select('id', 'name', 'status')
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            $departmentQualidactions = [];
            if ($this->hasTableCached('department_qualidactions')) {
                $departmentQualidactions = DB::table('department_qualidactions')
                    ->select('id', 'department_id', 'academic_qualification_id', 'department_code')
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            return [
                'academic_classes' => $academicClasses,
                'academic_sessions' => $this->hasTableCached('academic_sessions')
                    ? DB::table('academic_sessions')
                        ->select(array_values(array_filter([
                            'id',
                            'name',
                            $this->hasColumnCached('academic_sessions', 'status') ? 'status' : null,
                            $this->hasColumnCached('academic_sessions', 'online_admission') ? 'online_admission' : null,
                        ])))
                        ->orderBy('id')
                        ->get()
                        ->toArray()
                    : [],
                'academic_qualifications' => $academicQualifications,
                'departments' => $departments,
                'department_qualidactions' => $departmentQualidactions,
                'hostels' => DB::table('hostels')
                    ->select('id', 'name', 'status')
                    ->orderBy('id')
                    ->get()
                    ->toArray(),
                'exams' => $exams,
                'account_heads' => $accountHeads,
                'hostel_heads' => $hostelHeads,
                'admission_heads' => $admissionHeads,
                'accounts_commons' => $accountsCommons,
                'accounts_admissions' => $accountsAdmissions,
                'roles' => $roles,
            ];
        });
    }
}
