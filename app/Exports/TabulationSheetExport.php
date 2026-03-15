<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TabulationSheetExport implements WithMultipleSheets
{
    protected $data;
    protected $subjects;
    protected $childSubjectEnabled;

    public function __construct(array $data, array $subjects, $childSubjectEnabled = 0)
    {
        $this->data = $data;
        $this->subjects = $subjects;
        $this->childSubjectEnabled = $childSubjectEnabled;
    }

    public function sheets(): array
    {
        return [
            new TabulationSheet($this->data, $this->subjects, $this->childSubjectEnabled),
        ];
    }
}
