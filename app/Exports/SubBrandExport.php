<?php

namespace App\Exports;

use App\Models\SubBrand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubBrandExport implements FromCollection, WithHeadings
{
  protected $brand_id;

  // Constructor accepts brand_id to filter sub-brands
  public function __construct($brand_id)
  {
    $this->brand_id = $brand_id;
  }

  // Retrieve the filtered collection of sub-brands based on brand_id
  public function collection()
  {
    // Get the sub-brands with the related brand name
    return SubBrand::where('brand_id', $this->brand_id)
      ->with('brand') // Eager load the related brand
      ->get(['name', 'name_ar', 'brand_id'])
      ->map(function ($subBrand) {
        // Replace the brand_id with the actual brand name
        return [
          'name' => $subBrand->name,
          'name_ar' => $subBrand->name_ar,
          'brand_name' => $subBrand->brand->name, // Get the brand's name
        ];
      });
  }

  // Headings for the Excel file
  public function headings(): array
  {
    return [
      'Sub Brand Name (English)',
      'Sub Brand Name (Arabic)',
      'Brand Name', // Changed this from 'Brand ID' to 'Brand Name'
    ];
  }
}
