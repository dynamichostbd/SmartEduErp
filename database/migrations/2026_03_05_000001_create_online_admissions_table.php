<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('academic_session_id')->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->unsignedBigInteger('academic_qualification_id')->index();
            $table->unsignedBigInteger('academic_class_id')->index();
            $table->string('admission_roll', 100);
            $table->integer('roll_auto_generate')->default(0);
            $table->float('ssc_gpa', 10, 0)->nullable();
            $table->string('registration_no', 50)->nullable();
            $table->string('student_type', 15)->default('Regular');
            $table->string('name', 100);
            $table->string('gender', 20);
            $table->date('dob')->nullable();
            $table->string('religion', 30);
            $table->string('nid')->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('fathers_name', 100)->nullable();
            $table->string('mothers_name', 100)->nullable();
            $table->string('mobile', 30)->index('mobile');
            $table->string('email', 50)->nullable();
            $table->string('address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('guardian_type', 20);
            $table->string('guardian_name', 100);
            $table->string('guardian_mobile', 30);
            $table->string('guardian_relations', 50);
            $table->integer('passing_year')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('extra_curricular_activity', 255)->nullable();
            $table->string('quota', 5)->nullable();
            $table->string('marital_status', 15)->nullable();
            $table->integer('subject_id')->nullable();
            $table->integer('subject_cluster_id')->nullable();
            $table->text('cluster_subjects')->nullable();
            $table->text('subject_choose')->nullable();
            $table->text('profile')->nullable();
            $table->text('documents')->nullable();
            $table->double('gateway_percent', 8, 2)->nullable();
            $table->double('college_percent', 8, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admissions');
    }
};
