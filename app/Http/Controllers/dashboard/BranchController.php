<?php

// app/Http/Controllers/Dashboard/BranchController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of branches, optionally filtered by company.
     */
    public function index(Request $request)
    {
         $permissions = session('permissions');
    if (!isset($permissions['Branch']) || !in_array('read', $permissions['Branch']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
         $query = Branch::query();

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by branch name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by branch phone
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        $branches = $query->orderBy('id', 'desc')
                          ->with('company:id,name')
                          ->paginate(15)
                          ->withQueryString();

        // Fetch all companies for the filter dropdown
        $companies = Company::select('id','name')->orderBy('name')->get();


        return view('content.branch.index', compact('branches','companies'));
    }
}
