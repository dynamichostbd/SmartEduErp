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

        return Cache::remember($cacheKey, now()->addSeconds(60), function () use ($departmentId) {
            $resolveFileUrl = function ($value) {
                if (empty($value)) {
                    return null;
                }

                $value = trim((string) $value);
                if ($value === '') {
                    return null;
                }

                if (preg_match('/^https?:\/\//i', $value)) {
                    return $value;
                }

                $value = ltrim($value, '/');

                $filePath1 = public_path($value);
                if (file_exists($filePath1)) {
                    return url($value);
                }

                $localCandidate = preg_replace('/^upload\//i', '', $value);
                $filePath2 = public_path('storage/upload/' . $localCandidate);
                if (file_exists($filePath2)) {
                    return url('storage/upload/' . $localCandidate);
                }

                $bucketUrl = env('DO_ASSET_URL', 'https://smartedubd.blr1.cdn.digitaloceanspaces.com/blr1_storage');
                $bucketUrl = rtrim((string) $bucketUrl, '/');
                return $bucketUrl . '/' . ltrim($value, '/');
            };

            $year = (int) date('Y');
            $date = date('Y-m-d');
            $prevDate = date('Y-m-d', strtotime('-1 day'));
            $prev7Date = date('Y-m-d', strtotime('-7 day'));
            $currentMonthStart = date('Y-m-01');
            $currentMonthEnd = date('Y-m-t');
            $prevMonthStart = date('Y-m-01', strtotime('first day of last month'));
            $prevMonthEnd = date('Y-m-t', strtotime('last day of last month'));

            $hasTable = function (string $table): bool {
                return Cache::rememberForever('schema_has_table_' . $table, function () use ($table) {
                    return Schema::hasTable($table);
                });
            };

            $hasCol = function (string $table, string $col): bool {
                return Cache::rememberForever('schema_has_col_' . $table . '_' . $col, function () use ($table, $col) {
                    return Schema::hasColumn($table, $col);
                });
            };

            $rangeAgg = function (string $table) use ($departmentId, $date, $prevDate, $prev7Date, $currentMonthStart, $currentMonthEnd, $prevMonthStart, $prevMonthEnd, $year, $hasTable, $hasCol) {
                if (!$hasTable($table)) {
                    return [
                        'todays_trans' => 0,
                        'prev_day_trans' => 0,
                        'todays_payment' => 0.0,
                        'prev_day_payment' => 0.0,
                        'prev7_day_payment' => 0.0,
                        'current_month_payment' => 0.0,
                        'previous_month_payment' => 0.0,
                        'current_year_payment' => 0.0,
                        'prev_year_payment' => 0.0,
                    ];
                }

                $dateCol = 'payment_date';
                $amountCol = 'amount';
                if (!$hasCol($table, $dateCol) || !$hasCol($table, $amountCol) || !$hasCol($table, 'status')) {
                    return [
                        'todays_trans' => 0,
                        'prev_day_trans' => 0,
                        'todays_payment' => 0.0,
                        'prev_day_payment' => 0.0,
                        'prev7_day_payment' => 0.0,
                        'current_month_payment' => 0.0,
                        'previous_month_payment' => 0.0,
                        'current_year_payment' => 0.0,
                        'prev_year_payment' => 0.0,
                    ];
                }

                $q = DB::table($table)->where('status', 'success');
                if (!empty($departmentId) && $hasCol($table, 'department_id')) {
                    $q->where('department_id', $departmentId);
                }

                $tomorrow = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                $prevDayNext = $date;
                $prev7Next = $tomorrow;
                $currentMonthNext = date('Y-m-d', strtotime('+1 day', strtotime($currentMonthEnd)));
                $prevMonthNext = date('Y-m-d', strtotime('+1 day', strtotime($prevMonthEnd)));

                $yearStart = $year . '-01-01';
                $yearEnd = $year . '-12-31';
                $yearNext = ($year + 1) . '-01-01';
                $prevYearStart = ($year - 1) . '-01-01';
                $prevYearEnd = ($year - 1) . '-12-31';
                $prevYearNext = $yearStart;

                $row = $q
                    ->selectRaw(
                        "SUM(CASE WHEN {$dateCol} >= ? AND {$dateCol} < ? THEN 1 ELSE 0 END) as todays_trans,
                         SUM(CASE WHEN {$dateCol} >= ? AND {$dateCol} < ? THEN 1 ELSE 0 END) as prev_day_trans,
                         SUM(CASE WHEN {$dateCol} >= ? AND {$dateCol} < ? THEN {$amountCol} ELSE 0 END) as todays_payment,
                         SUM(CASE WHEN {$dateCol} >= ? AND {$dateCol} < ? THEN {$amountCol} ELSE 0 END) as prev_day_payment,
                         SUM(CASE WHEN {$dateCol} >= ? AND {$dateCol} < ? THEN {$amountCol} ELSE 0 END) as prev7_day_payment,
                         SUM(CASE WHEN {$dateCol} >= ? AND {$dateCol} < ? THEN {$amountCol} ELSE 0 END) as current_month_payment,
                         SUM(CASE WHEN {$dateCol} >= ? AND {$dateCol} < ? THEN {$amountCol} ELSE 0 END) as previous_month_payment,
                         SUM(CASE WHEN {$dateCol} >= ? AND {$dateCol} < ? THEN {$amountCol} ELSE 0 END) as current_year_payment,
                         SUM(CASE WHEN {$dateCol} >= ? AND {$dateCol} < ? THEN {$amountCol} ELSE 0 END) as prev_year_payment",
                        [
                            $date,
                            $tomorrow,
                            $prevDate,
                            $prevDayNext,
                            $date,
                            $tomorrow,
                            $prevDate,
                            $prevDayNext,
                            $prev7Date,
                            $prev7Next,
                            $currentMonthStart,
                            $currentMonthNext,
                            $prevMonthStart,
                            $prevMonthNext,
                            $yearStart,
                            $yearNext,
                            $prevYearStart,
                            $prevYearNext,
                        ]
                    )
                    ->first();

                return [
                    'todays_trans' => (int) ($row->todays_trans ?? 0),
                    'prev_day_trans' => (int) ($row->prev_day_trans ?? 0),
                    'todays_payment' => (float) ($row->todays_payment ?? 0),
                    'prev_day_payment' => (float) ($row->prev_day_payment ?? 0),
                    'prev7_day_payment' => (float) ($row->prev7_day_payment ?? 0),
                    'current_month_payment' => (float) ($row->current_month_payment ?? 0),
                    'previous_month_payment' => (float) ($row->previous_month_payment ?? 0),
                    'current_year_payment' => (float) ($row->current_year_payment ?? 0),
                    'prev_year_payment' => (float) ($row->prev_year_payment ?? 0),
                ];
            };

            $inv = $rangeAgg('invoices');
            $hp = $rangeAgg('hostel_payments');
            $adm = $rangeAgg('admissions');

            $data = [];
            $data['todays_trans'] = $inv['todays_trans'] + $hp['todays_trans'] + $adm['todays_trans'];
            $data['prev_day_trans'] = $inv['prev_day_trans'] + $hp['prev_day_trans'] + $adm['prev_day_trans'];
            $data['todays_payment'] = $inv['todays_payment'] + $hp['todays_payment'] + $adm['todays_payment'];
            $data['prev_day_payment'] = $inv['prev_day_payment'] + $hp['prev_day_payment'] + $adm['prev_day_payment'];
            $data['prev7_day_payment'] = $inv['prev7_day_payment'] + $hp['prev7_day_payment'] + $adm['prev7_day_payment'];
            $data['current_month_payment'] = $inv['current_month_payment'] + $hp['current_month_payment'] + $adm['current_month_payment'];
            $data['previous_month_payment'] = $inv['previous_month_payment'] + $hp['previous_month_payment'] + $adm['previous_month_payment'];
            $data['current_year_payment'] = $inv['current_year_payment'] + $hp['current_year_payment'] + $adm['current_year_payment'];
            $data['prev_year_payment'] = $inv['prev_year_payment'] + $hp['prev_year_payment'] + $adm['prev_year_payment'];

        $deptCount = 0;
        if ($hasTable('departments')) {
            $dq = DB::table('departments');
            if ($hasCol('departments', 'status')) {
                $dq->where('status', 'active');
            }
            $deptCount = (int) $dq->count();
        }
        $data['total_dept'] = $deptCount;

        $stdCount = 0;
        if ($hasTable('students')) {
            $sq = DB::table('students');
            if (!empty($departmentId) && $hasCol('students', 'department_id')) {
                $sq->where('department_id', $departmentId);
            }
            if ($hasCol('students', 'status')) {
                $sq->where('status', 'active');
            }
            $stdCount = (int) $sq->count();
        }
        $data['total_student'] = $stdCount;

        $data['todays_wallet_recharge'] = 0.0;
        $data['todays_wallet_trans'] = 0;
        $data['remaining_balance'] = 0.0;
        $data['total_wallet_recharge'] = 0.0;

        if ($hasTable('wallet_transactions')) {
            $todayWq = DB::table('wallet_transactions');
            if ($hasCol('wallet_transactions', 'status')) {
                $todayWq->where('status', 'success');
            }
            if (!empty($departmentId) && $hasCol('wallet_transactions', 'department_id')) {
                $todayWq->where('department_id', $departmentId);
            }
            if ($hasCol('wallet_transactions', 'invoice_date')) {
                $todayWq->whereDate('invoice_date', $date);
            }

            if ($hasCol('wallet_transactions', 'paid_amount')) {
                $data['todays_wallet_recharge'] = (float) (clone $todayWq)->sum('paid_amount');
                $data['total_wallet_recharge'] = (float) DB::table('wallet_transactions')->sum('paid_amount');
            }
            $data['todays_wallet_trans'] = (int) $todayWq->count();
        }

        if ($hasTable('wallet_balances') && $hasCol('wallet_balances', 'amount')) {
            $data['remaining_balance'] = (float) DB::table('wallet_balances')->sum('amount');
        }

        $data['popup'] = null;
        if ($hasTable('popups')) {
            $pq = DB::table('popups');
            if ($hasCol('popups', 'status')) {
                $pq->where('status', 'active');
            }
            if ($hasCol('popups', 'type')) {
                $pq->where('type', 'admin');
            }
            $popup = $pq->first();
            if ($popup && property_exists($popup, 'image')) {
                $popup->image = $resolveFileUrl($popup->image);
            }
            $data['popup'] = $popup ?: null;
        }

            return response()->json($data);
        });
    }
}
