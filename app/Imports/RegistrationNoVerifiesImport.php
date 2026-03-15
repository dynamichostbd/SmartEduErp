<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class RegistrationNoVerifiesImport implements ToCollection
{
    protected array $data = [];

    public function __construct($data)
    {
        $this->data = is_array($data) ? $data : [];
    }

    public function collection(Collection $rows)
    {
        $registrations = [];

        foreach ($rows as $row) {
            $val = $row[0] ?? null;
            if ($val === null || $val === '') {
                continue;
            }

            $val = is_string($val) ? trim($val) : $val;
            if ($val === '' || $val === null) {
                continue;
            }

            if (!is_numeric($val)) {
                continue;
            }

            $registrations[] = (int) $val;
        }

        $payload = $this->data;
        $payload['registration_no_lists'] = json_encode(array_values($registrations));

        DB::table('registration_no_verifies')->insert($payload + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
