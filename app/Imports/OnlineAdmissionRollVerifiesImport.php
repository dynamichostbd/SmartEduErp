<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class OnlineAdmissionRollVerifiesImport implements ToCollection
{
    protected array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection(Collection $rows)
    {
        $rollLists = [];
        $nameLists = [];

        foreach ($rows as $row) {
            $roll = $row[0] ?? null;
            $name = $row[1] ?? null;

            if ($roll === null || $roll === '') {
                continue;
            }

            $roll = is_string($roll) ? trim($roll) : $roll;
            if ($roll === '' || $roll === null) {
                continue;
            }

            $rollLists[] = $roll;
            $nameLists[] = is_string($name) ? trim($name) : $name;
        }

        if (empty($this->data['academic_session_id']) || empty($this->data['academic_qualification_id']) || empty($this->data['department_id']) || empty($this->data['academic_class_id'])) {
            return;
        }

        DB::table('online_admission_roll_verifies')->insert([
            'academic_session_id' => $this->data['academic_session_id'],
            'academic_qualification_id' => $this->data['academic_qualification_id'],
            'department_id' => $this->data['department_id'],
            'academic_class_id' => $this->data['academic_class_id'],
            'roll_lists' => json_encode(array_values($rollLists)),
            'name_lists' => json_encode(array_values($nameLists)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
