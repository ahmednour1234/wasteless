<?php

namespace App\Exports;

use App\Models\Bank;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BanksExport implements FromCollection, WithHeadings
{
    /**
     * Fetch the data to be exported.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Bank::select('name', 'name_ar')->get();
    }

    /**
     * Define the headings for the exported Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return ['Name', 'Name (Arabic)'];
    }
}
