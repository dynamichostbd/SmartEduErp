<?php

namespace App\Traits\Lib;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait DynamicDataTrait
{
    protected function dynamicData()
    {
        return Cache::remember('dynamic_data_cache', 600, function () {
            $exams = [];
            if (Schema::hasTable('exams')) {
                $examCols = ['id', 'name'];
                if (Schema::hasColumn('exams', 'status')) {
                    $examCols[] = 'status';
                }

                $exams = DB::table('exams')
                    ->select($examCols)
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            $accountHeads = [];
            if (Schema::hasTable('account_heads')) {
                $q = DB::table('account_heads');
                if (Schema::hasColumn('account_heads', 'status')) {
                    $q->where('status', 'active');
                }
                $accountHeads = $q->orderBy('id')->pluck('name', 'id')->toArray();
            }

            $admissionHeads = [];
            if (Schema::hasTable('account_heads')) {
                $q = DB::table('account_heads');
                if (Schema::hasColumn('account_heads', 'status')) {
                    $q->where('status', 'active');
                }
                if (Schema::hasColumn('account_heads', 'type')) {
                    $q->whereIn('type', ['admission', 'certificate']);
                }
                $admissionHeads = $q->orderBy('id')->pluck('name', 'id')->toArray();
            }

            $accountsCommons = [];
            if (Schema::hasTable('payment_gateways') && Schema::hasColumn('payment_gateways', 'store_id')) {
                $cols = ['id', 'store_id'];
                if (Schema::hasColumn('payment_gateways', 'account_no')) {
                    $cols[] = 'account_no';
                }
                if (Schema::hasColumn('payment_gateways', 'title')) {
                    $cols[] = 'title';
                }
                if (Schema::hasColumn('payment_gateways', 'status')) {
                    $cols[] = 'status';
                }

                $q = DB::table('payment_gateways')->select($cols)->orderByDesc('id');
                if (Schema::hasColumn('payment_gateways', 'status')) {
                    $q->where('status', 'active');
                }
                if (Schema::hasColumn('payment_gateways', 'account_no')) {
                    $q->whereNotNull('account_no');
                }

                $accountsCommons = $q->get()->unique('store_id')->values()->toArray();
            }

            $accountsAdmissions = [];
            if (Schema::hasTable('admission_fee_setups') && Schema::hasColumn('admission_fee_setups', 'store_id')) {
                $cols = ['id', 'store_id'];
                if (Schema::hasColumn('admission_fee_setups', 'account_no')) {
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
            if (Schema::hasTable('roles')) {
                $cols = ['id', 'name'];
                if (Schema::hasColumn('roles', 'status')) {
                    $cols[] = 'status';
                }

                $q = DB::table('roles')->select($cols)->orderBy('id');
                if (Schema::hasColumn('roles', 'status')) {
                    $q->where('status', 'active');
                }
                $roles = $q->get()->toArray();
            }

            $academicClasses = [];
            if (Schema::hasTable('academic_classes')) {
                $academicClasses = DB::table('academic_classes')
                    ->select('id', 'name', 'academic_qualification_id', 'status')
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            $academicQualifications = [];
            if (Schema::hasTable('academic_qualifications')) {
                $academicQualifications = DB::table('academic_qualifications')
                    ->select('id', 'name', 'status')
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            $departments = [];
            if (Schema::hasTable('departments')) {
                $departments = DB::table('departments')
                    ->select('id', 'name', 'status')
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            $departmentQualidactions = [];
            if (Schema::hasTable('department_qualidactions')) {
                $departmentQualidactions = DB::table('department_qualidactions')
                    ->select('id', 'department_id', 'academic_qualification_id', 'department_code')
                    ->orderBy('id')
                    ->get()
                    ->toArray();
            }

            return [
                'academic_classes' => $academicClasses,
                'academic_sessions' => Schema::hasTable('academic_sessions')
                    ? DB::table('academic_sessions')
                        ->select('id', 'name', 'status')
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
                'admission_heads' => $admissionHeads,
                'accounts_commons' => $accountsCommons,
                'accounts_admissions' => $accountsAdmissions,
                'roles' => $roles,
            ];
        });
    }
}
