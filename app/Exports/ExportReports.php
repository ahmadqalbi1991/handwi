<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReports implements WithHeadings, FromArray, WithStyles,ShouldAutoSize
{
    protected $rows;
    protected $heading;

    public function headings(): array
    {
        return $this->heading;
    }
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
    public function __construct(array $rows,$headings)
    {
        $this->rows = $rows;
        $this->heading = $headings;
    }

    function array(): array
    {
        return $this->rows;
    }

}
