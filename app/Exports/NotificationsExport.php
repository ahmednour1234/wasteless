<?php

namespace App\Exports;

use App\Models\Notification;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NotificationsExport implements FromCollection, WithHeadings
{
    /**
     * Fetch the data to be exported.
     */
    public function collection()
    {
        return Notification::select('id', 'subject', 'content', 'send_to', 'created_at')->get();
    }

    /**
     * Define headings for the exported sheet.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Subject',
            'Content',
            'Send To',
            'Created At',
            'seller_id',
            'sale_id',
            'order_id',
            'survey_id'
        ];
    }
}
