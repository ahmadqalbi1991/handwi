<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CampaignReportExport implements FromArray, WithHeadings
{
    protected $data;

    /**
     * Constructor to pass the custom data.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Return the array of data to be exported.
     *
     * @return array
     */
    public function array(): array
    {
        return $this->data;
    }

    /**
     * Custom headings for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Campaign Name',
            'Campaign Date',
            'Campaign Time',
            'Product Name',
            'Winner',
            'Status'
        ];  // You can customize these headings
    }
}
