<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function dashboardInfo(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $departmentId = $admin->department_id ?? null;

        $date = date('Y-m-d');
        $deptKey = !empty($departmentId) ? (int) $departmentId : 0;
        $cacheKey = "dashboard_info_{$deptKey}_{$date}";

        return Cache::remember($cacheKey, 60, function () use ($departmentId) {
            $year = (int) date('Y');
            $date = date('Y-m-d');
            $prevDate = date('Y-m-d', strtotime('-1 day'));
            $prev7Date = date('Y-m-d', strtotime('-7 day'));
            $currentMonthStart = date('Y-m-01');
            $currentMonthEnd = date('Y-m-t');
            $prevMonthStart = date('Y-m-01', strtotime('first day of last month'));
            $prevMonthEnd = date('Y-m-t', strtotime('last day of last month'));

            $invoiceTableOk = Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'payment_date') && Schema::hasColumn('invoices', 'status') && Schema::hasColumn('invoices', 'amount');
            $hostelTableOk = Schema::hasTable('hostel_payments') && Schema::hasColumn('hostel_payments', 'payment_date') && Schema::hasColumn('hostel_payments', 'status') && Schema::hasColumn('hostel_payments', 'amount');
            $admissionTableOk = Schema::hasTable('admissions') && Schema::hasColumn('admissions', 'payment_date') && Schema::hasColumn('admissions', 'status') && Schema::hasColumn('admissions', 'amount');

        $successBase = function (string $table) use ($departmentId) {
            $q = DB::table($table)->where('status', 'success');
            if (!empty($departmentId) && Schema::hasColumn($table, 'department_id')) {
                $q->where('department_id', $departmentId);
            }
            return $q;
        };

        $invBase = function () use ($invoiceTableOk, $successBase) {
            return $invoiceTableOk ? $successBase('invoices') : null;
        };

        $hpBase = function () use ($hostelTableOk, $successBase) {
            return $hostelTableOk ? $successBase('hostel_payments') : null;
        };

        $admBase = function () use ($admissionTableOk, $successBase) {
            return $admissionTableOk ? $successBase('admissions') : null;
        };

        $sumFor = function ($query) {
            return $query ? (float) $query->sum('amount') : 0.0;
        };

        $countFor = function ($query) {
            return $query ? (int) $query->count() : 0;
        };

        $todayInvQuery = ($q = $invBase()) ? (clone $q)->whereDate('payment_date', $date) : null;
        $prevInvQuery = ($q = $invBase()) ? (clone $q)->whereDate('payment_date', $prevDate) : null;
        $prev7InvQuery = ($q = $invBase()) ? (clone $q)->whereBetween('payment_date', [$prev7Date, $date]) : null;
        $prevMonthInvQuery = ($q = $invBase()) ? (clone $q)->whereBetween('payment_date', [$prevMonthStart, $prevMonthEnd]) : null;
        $currentMonthInvQuery = ($q = $invBase()) ? (clone $q)->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd]) : null;
        $yearInvQuery = ($q = $invBase()) ? (clone $q)->whereYear('payment_date', $year) : null;
        $prevYearInvQuery = ($q = $invBase()) ? (clone $q)->whereYear('payment_date', $year - 1) : null;

        $todayHpQuery = ($q = $hpBase()) ? (clone $q)->whereDate('payment_date', $date) : null;
        $prevHpQuery = ($q = $hpBase()) ? (clone $q)->whereDate('payment_date', $prevDate) : null;
        $prev7HpQuery = ($q = $hpBase()) ? (clone $q)->whereBetween('payment_date', [$prev7Date, $date]) : null;
        $prevMonthHpQuery = ($q = $hpBase()) ? (clone $q)->whereBetween('payment_date', [$prevMonthStart, $prevMonthEnd]) : null;
        $currentMonthHpQuery = ($q = $hpBase()) ? (clone $q)->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd]) : null;
        $yearHpQuery = ($q = $hpBase()) ? (clone $q)->whereYear('payment_date', $year) : null;
        $prevYearHpQuery = ($q = $hpBase()) ? (clone $q)->whereYear('payment_date', $year - 1) : null;

        $todayAdmQuery = ($q = $admBase()) ? (clone $q)->whereDate('payment_date', $date) : null;
        $prevAdmQuery = ($q = $admBase()) ? (clone $q)->whereDate('payment_date', $prevDate) : null;
        $prev7AdmQuery = ($q = $admBase()) ? (clone $q)->whereBetween('payment_date', [$prev7Date, $date]) : null;
        $prevMonthAdmQuery = ($q = $admBase()) ? (clone $q)->whereBetween('payment_date', [$prevMonthStart, $prevMonthEnd]) : null;
        $currentMonthAdmQuery = ($q = $admBase()) ? (clone $q)->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd]) : null;
        $yearAdmQuery = ($q = $admBase()) ? (clone $q)->whereYear('payment_date', $year) : null;
        $prevYearAdmQuery = ($q = $admBase()) ? (clone $q)->whereYear('payment_date', $year - 1) : null;

        $data = [];
        $data['todays_trans'] = $countFor($todayInvQuery) + $countFor($todayHpQuery) + $countFor($todayAdmQuery);
        $data['prev_day_trans'] = $countFor($prevInvQuery) + $countFor($prevHpQuery) + $countFor($prevAdmQuery);
        $data['todays_payment'] = $sumFor($todayInvQuery) + $sumFor($todayHpQuery) + $sumFor($todayAdmQuery);
        $data['prev_day_payment'] = $sumFor($prevInvQuery) + $sumFor($prevHpQuery) + $sumFor($prevAdmQuery);
        $data['prev7_day_payment'] = $sumFor($prev7InvQuery) + $sumFor($prev7HpQuery) + $sumFor($prev7AdmQuery);
        $data['current_month_payment'] = $sumFor($currentMonthInvQuery) + $sumFor($currentMonthHpQuery) + $sumFor($currentMonthAdmQuery);
        $data['previous_month_payment'] = $sumFor($prevMonthInvQuery) + $sumFor($prevMonthHpQuery) + $sumFor($prevMonthAdmQuery);
        $data['current_year_payment'] = $sumFor($yearInvQuery) + $sumFor($yearHpQuery) + $sumFor($yearAdmQuery);
        $data['prev_year_payment'] = $sumFor($prevYearInvQuery) + $sumFor($prevYearHpQuery) + $sumFor($prevYearAdmQuery);

        $deptCount = 0;
        if (Schema::hasTable('departments')) {
            $dq = DB::table('departments');
            if (Schema::hasColumn('departments', 'status')) {
                $dq->where('status', 'active');
            }
            $deptCount = (int) $dq->count();
        }
        $data['total_dept'] = $deptCount;

        $stdCount = 0;
        if (Schema::hasTable('students')) {
            $sq = DB::table('students');
            if (!empty($departmentId) && Schema::hasColumn('students', 'department_id')) {
                $sq->where('department_id', $departmentId);
            }
            if (Schema::hasColumn('students', 'status')) {
                $sq->where('status', 'active');
            }
            $stdCount = (int) $sq->count();
        }
        $data['total_student'] = $stdCount;

        $data['todays_wallet_recharge'] = 0.0;
        $data['todays_wallet_trans'] = 0;
        $data['remaining_balance'] = 0.0;
        $data['total_wallet_recharge'] = 0.0;

        if (Schema::hasTable('wallet_transactions')) {
            $wq = DB::table('wallet_transactions');
            if (Schema::hasColumn('wallet_transactions', 'invoice_date')) {
                $wq->whereDate('invoice_date', $date);
            }
            if (Schema::hasColumn('wallet_transactions', 'status')) {
                $wq->where('status', 'success');
            }
            if (!empty($departmentId) && Schema::hasColumn('wallet_transactions', 'department_id')) {
                $wq->where('department_id', $departmentId);
            }

            if (Schema::hasColumn('wallet_transactions', 'paid_amount')) {
                $data['todays_wallet_recharge'] = (float) (clone $wq)->sum('paid_amount');
                $data['total_wallet_recharge'] = (float) DB::table('wallet_transactions')->sum('paid_amount');
            }
            $data['todays_wallet_trans'] = (int) $wq->count();
        }

        if (Schema::hasTable('wallet_balances') && Schema::hasColumn('wallet_balances', 'amount')) {
            $data['remaining_balance'] = (float) DB::table('wallet_balances')->sum('amount');
        }

        $data['popup'] = null;
        if (Schema::hasTable('popups')) {
            $pq = DB::table('popups');
            if (Schema::hasColumn('popups', 'status')) {
                $pq->where('status', 'active');
            }
            if (Schema::hasColumn('popups', 'type')) {
                $pq->where('type', 'admin');
            }
            $popup = $pq->first();
            $data['popup'] = $popup ?: null;
        }

            return response()->json($data);
        });
    }
}
