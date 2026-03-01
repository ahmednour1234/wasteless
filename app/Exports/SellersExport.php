<?php
namespace App\Exports;

use App\Models\Seller;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SellersExport implements FromCollection, WithHeadings, WithTitle
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    // Return the collection of sellers based on the type
    public function collection()
    {
        return Seller::where('type', $this->type)
            ->get(['name', 'email', 'phone', 'branch_id', 'manager_id', 'brands', 'distributions', 'fcm_tokens', 'type']);
    }

    // Set the headings for the columns
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Branch ID',
            'Manager ID',
            'Brands',
            'Distributions',
            'FCM Tokens',
            'Type'
        ];
    }

    // Set the title of the sheet
    public function title(): string
    {
        return 'Sellers';
    }
}
