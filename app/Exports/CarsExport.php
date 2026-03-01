<?php

namespace App\Exports;

use App\Models\Car;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CarsExport implements FromCollection, WithHeadings
{
    /**
     * Fetch the data to be exported.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Car::with('brand')
            ->get()
            ->map(function ($car) {
                return [
                    'name' => $car->name,
                    'name_ar' => $car->name_ar,
                    'brand_name' => $car->brand->name ?? 'No Brand', // Handle missing brand
                ];
            });
    }

    /**
     * Define the headings for the exported Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return ['Name', 'Name (Arabic)', 'Brand Name'];
    }
}
