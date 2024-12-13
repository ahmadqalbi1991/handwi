<?php
namespace App\Exports;

use App\Models\SubscriberEmail;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscriberEmailsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return SubscriberEmail::select('id','email', 'created_at')->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'Email',
            'Created At',
        ];
    }

    public function map($subscriberEmail): array
    {
        return [
            'id'  => $subscriberEmail->id,
            'email' => $subscriberEmail->email,
            'created_at' => Carbon::parse($subscriberEmail->created_at)->format('M d, Y h:i A'),
        ];
    }
}

