<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentMigrationRollVerifiesImport implements ToCollection
{
    protected array $data = [];

    public function __construct($data)
    {
        $this->data = is_array($data) ? $data : [];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $roll = $row[0] ?? null;
            if ($roll === null || $roll === '') {
                continue;
            }

            $roll = is_string($roll) ? trim($roll) : $roll;
            if ($roll === '' || $roll === null) {
                continue;
            }

            $payload = [
                'academic_session_id' => $this->data['academic_session_id'] ?? null,
                'migrate_department_id' => $this->data['migrate_department_id'] ?? null,
                'admission_roll' => $roll,
            ];

            if (empty($payload['academic_session_id']) || empty($payload['migrate_department_id'])) {
                continue;
            }

            $this->upsertRoll($payload);
            $this->createStudentMigration($payload);
        }
    }

    protected function upsertRoll(array $payload): void
    {
        $where = [
            'academic_session_id' => $payload['academic_session_id'],
            'migrate_department_id' => $payload['migrate_department_id'],
            'admission_roll' => $payload['admission_roll'],
        ];

        $existing = DB::table('student_migration_roll_verifies')->where($where)->first();
        if ($existing) {
            DB::table('student_migration_roll_verifies')
                ->where('id', $existing->id)
                ->update(['updated_at' => now()]);
            return;
        }

        DB::table('student_migration_roll_verifies')->insert($where + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function createStudentMigration(array $payload): void
    {
        if (!Schema::hasTable('student_migrations')) {
            return;
        }

        $student = DB::table('students')
            ->select('id', 'academic_qualification_id', 'department_id', 'academic_class_id', 'academic_session_id', 'college_roll', 'admission_id')
            ->where([
                'admission_id' => $payload['admission_roll'],
                'academic_session_id' => $payload['academic_session_id'],
            ])
            ->where('department_id', '!=', $payload['migrate_department_id'])
            ->first();

        if (!$student) {
            return;
        }

        $data = [
            'student_id' => $student->id,
            'academic_qualification_id' => $student->academic_qualification_id,
            'department_id' => $student->department_id,
            'academic_class_id' => $student->academic_class_id,
            'migrate_department_id' => $payload['migrate_department_id'],
            'academic_session_id' => $student->academic_session_id,
            'college_roll' => $student->college_roll,
        ];

        $exists = DB::table('student_migrations')->where($data)->exists();
        if ($exists) {
            return;
        }

        DB::table('student_migrations')->insert($data + [
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
