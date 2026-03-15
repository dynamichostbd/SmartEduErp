<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TabulationSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    protected $data;
    protected $subjects;
    protected $allSubjects;
    protected $childSubjectEnabled;

    public function __construct(array $data, array $subjects, $childSubjectEnabled = 0)
    {
        $this->data = $data;
        $this->allSubjects = $subjects;
        $this->childSubjectEnabled = $childSubjectEnabled;
        $this->subjects = $subjects;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        $row1 = ['SL.', 'College Roll', 'Student Name'];
        $row2 = ['', '', ''];

        foreach ($this->subjects as $subject) {
            $subjectName = $subject['subject']['name_en'] ?? 'Subject';
            $row1[] = $subjectName;
            array_push($row1, '', '', '', '', '');

            $row2[] = 'CQ';
            $row2[] = 'MCQ';
            $row2[] = 'PRAC';
            $row2[] = 'OBTAINED';
            $row2[] = 'GPA';
            $row2[] = 'GRADE';
        }

        $row1[] = 'Total Mark';
        $row1[] = 'GPA';
        $row1[] = 'Grade';

        $row2[] = '';
        $row2[] = '';
        $row2[] = '';

        return [
            $row1,
            $row2,
        ];
    }

    public function title(): string
    {
        return 'Tabulation Sheet';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();
                $highestColumn = $delegate->getHighestColumn();
                $highestRow = $delegate->getHighestRow();

                $delegate->mergeCells('A1:A2');
                $delegate->mergeCells('B1:B2');
                $delegate->mergeCells('C1:C2');

                $currentCol = 4;
                foreach ($this->subjects as $subject) {
                    $delegate->mergeCellsByColumnAndRow($currentCol, 1, $currentCol + 5, 1);
                    $currentCol += 6;
                }

                for ($i = 0; $i < 3; $i++) {
                    $delegate->mergeCellsByColumnAndRow($currentCol, 1, $currentCol, 2);
                    $currentCol++;
                }

                $headerStyle = [
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ];
                $delegate->getStyle('A1:' . $highestColumn . '2')->applyFromArray($headerStyle);

                $delegate->getStyle('C1:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $delegate->getStyle('A3:' . $highestColumn . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $delegate->getStyle('C3:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
