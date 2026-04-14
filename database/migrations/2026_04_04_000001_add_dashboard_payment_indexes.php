<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return !empty($rows);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function addIndexIfMissing(string $table, string $indexName, string $sql): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        DB::statement($sql);
    }

    public function up(): void
    {
        // invoices
        if (Schema::hasTable('invoices')) {
            if (Schema::hasColumn('invoices', 'status') && Schema::hasColumn('invoices', 'payment_date')) {
                $this->addIndexIfMissing(
                    'invoices',
                    'idx_invoices_status_payment_date',
                    'CREATE INDEX idx_invoices_status_payment_date ON invoices (status, payment_date)'
                );
            }

            if (Schema::hasColumn('invoices', 'department_id') && Schema::hasColumn('invoices', 'status') && Schema::hasColumn('invoices', 'payment_date')) {
                $this->addIndexIfMissing(
                    'invoices',
                    'idx_invoices_dept_status_payment_date',
                    'CREATE INDEX idx_invoices_dept_status_payment_date ON invoices (department_id, status, payment_date)'
                );
            }
        }

        // hostel_payments
        if (Schema::hasTable('hostel_payments')) {
            if (Schema::hasColumn('hostel_payments', 'status') && Schema::hasColumn('hostel_payments', 'payment_date')) {
                $this->addIndexIfMissing(
                    'hostel_payments',
                    'idx_hostel_payments_status_payment_date',
                    'CREATE INDEX idx_hostel_payments_status_payment_date ON hostel_payments (status, payment_date)'
                );
            }

            if (Schema::hasColumn('hostel_payments', 'department_id') && Schema::hasColumn('hostel_payments', 'status') && Schema::hasColumn('hostel_payments', 'payment_date')) {
                $this->addIndexIfMissing(
                    'hostel_payments',
                    'idx_hostel_payments_dept_status_payment_date',
                    'CREATE INDEX idx_hostel_payments_dept_status_payment_date ON hostel_payments (department_id, status, payment_date)'
                );
            }
        }

        // admissions (if exists)
        if (Schema::hasTable('admissions')) {
            if (Schema::hasColumn('admissions', 'status') && Schema::hasColumn('admissions', 'payment_date')) {
                $this->addIndexIfMissing(
                    'admissions',
                    'idx_admissions_status_payment_date',
                    'CREATE INDEX idx_admissions_status_payment_date ON admissions (status, payment_date)'
                );
            }

            if (Schema::hasColumn('admissions', 'department_id') && Schema::hasColumn('admissions', 'status') && Schema::hasColumn('admissions', 'payment_date')) {
                $this->addIndexIfMissing(
                    'admissions',
                    'idx_admissions_dept_status_payment_date',
                    'CREATE INDEX idx_admissions_dept_status_payment_date ON admissions (department_id, status, payment_date)'
                );
            }
        }

        // wallet_transactions (if exists)
        if (Schema::hasTable('wallet_transactions')) {
            if (Schema::hasColumn('wallet_transactions', 'status') && Schema::hasColumn('wallet_transactions', 'invoice_date')) {
                $this->addIndexIfMissing(
                    'wallet_transactions',
                    'idx_wallet_transactions_status_invoice_date',
                    'CREATE INDEX idx_wallet_transactions_status_invoice_date ON wallet_transactions (status, invoice_date)'
                );
            }

            if (Schema::hasColumn('wallet_transactions', 'department_id') && Schema::hasColumn('wallet_transactions', 'status') && Schema::hasColumn('wallet_transactions', 'invoice_date')) {
                $this->addIndexIfMissing(
                    'wallet_transactions',
                    'idx_wallet_transactions_dept_status_invoice_date',
                    'CREATE INDEX idx_wallet_transactions_dept_status_invoice_date ON wallet_transactions (department_id, status, invoice_date)'
                );
            }
        }
    }

    public function down(): void
    {
        // Intentionally not dropping indexes automatically.
    }
};
