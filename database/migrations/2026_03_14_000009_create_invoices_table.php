<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices')) {
            return;
        }

        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->unsignedBigInteger('online_admission_id')->nullable();
            $table->integer('student_migration_id')->nullable();
            $table->unsignedBigInteger('academic_session_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->unsignedBigInteger('academic_qualification_id')->nullable()->index();
            $table->unsignedBigInteger('academic_class_id')->nullable()->index();
            $table->unsignedBigInteger('account_head_id')->nullable()->index();
            $table->unsignedBigInteger('payment_gateway_id')->nullable();
            $table->integer('exam_id')->nullable();
            $table->integer('examination_year')->nullable();
            $table->string('admission_id', 40)->nullable();
            $table->integer('college_roll')->nullable();
            $table->string('reg_no', 50)->nullable();
            $table->date('invoice_date');
            $table->string('invoice_number', 50)->unique();
            $table->string('form_number')->nullable();
            $table->decimal('form_fee', 10)->nullable();
            $table->decimal('college_fee', 10)->nullable();
            $table->decimal('fine_amount', 10)->nullable();
            $table->decimal('fees_amount', 10)->nullable();
            $table->decimal('amount', 10);
            $table->decimal('service_charge', 10)->nullable();
            $table->decimal('gateway_charge', 10)->nullable();
            $table->decimal('paid_amount', 10)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 10)->default('SSL');
            $table->string('card_type', 100)->nullable();
            $table->string('bank_trans_id')->nullable();
            $table->decimal('refund_amount', 10)->nullable();
            $table->date('refund_date')->nullable();
            $table->text('refund_note')->nullable();
            $table->string('status', 15)->default('pending');
            $table->enum('created_from', ['web', 'app'])->default('web');
            $table->timestamps();
            $table->string('created_by', 100)->nullable();
            $table->string('created_ip', 50)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->string('updated_ip', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
