<?php
namespace App\Exports;

use App\Models\Survey;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SurveyExport implements FromCollection, WithHeadings
{
    protected $surveys;

    public function __construct($surveys)
    {
        $this->surveys = $surveys;
    }

    public function collection()
    {
        return $this->surveys->map(function($survey) {
            return [
                $survey->seller->name,
                $survey->created_at,
                $survey->end_time,
                $survey->car_count,
                $survey->type == 1 ? 'yes' : 'No',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Seller Name', 
            'Created At', 
            'Sent Time', 
            'Car Count', 
            'Before Time', 
        ];
    }
}
