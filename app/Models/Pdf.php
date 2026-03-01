<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pdf extends Model
{
  use HasFactory;

  // Define the table name if it's different from the plural form of the model
  protected $table = 'pdfs';

  // Define the fillable properties for mass assignment
  protected $fillable = [
    'project_id',
    'name',
    'name_ar',
    'qrcode',
    'pdf',
    'size',
  ];

  // Define relationships (if applicable)
  public function project()
  {
    return $this->belongsTo(Project::class);
  }
}
