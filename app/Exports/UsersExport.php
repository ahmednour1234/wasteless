<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
  protected $users;

  // Constructor to accept filtered users
  public function __construct($users)
  {
    $this->users = $users;
  }

  // Return the collection of users for export
  public function collection()
  {
    return $this->users;
  }

  // Define the headers for the Excel export
  public function headings(): array
  {
    return [
      'ID',           // Column 1: User ID
      'Name',         // Column 2: Name
      'Email',        // Column 3: Email
      'Phone',        // Column 4: Phone
      'Role',         // Column 5: Role Name
      'Created At',   // Column 6: Created At
      'Updated At'    // Column 7: Updated At
    ];
  }

  // Map the user data and show the role name
  public function map($user): array
  {
    return [
      $user->id,                             // User ID
      $user->name,                           // User Name
      $user->email,                          // User Email
      $user->phone,                          // User Phone
      $user->role->name ?? 'N/A',            // Role Name (using null coalescing in case there's no role)
      $user->created_at->toFormattedDateString(),  // Formatted Created At
      $user->updated_at->toFormattedDateString()   // Formatted Updated At
    ];
  }
}
