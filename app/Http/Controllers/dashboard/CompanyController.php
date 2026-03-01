<?php
// app/Http/Controllers/Dashboard/CompanyController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies, with optional name/phone filters.
     */
    public function index(Request $request)
    {
       $permissions = session('permissions');
    if (!isset($permissions['Company']) || !in_array('read', $permissions['Company']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        $query = Company::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%'.$request->phone.'%');
        }

        $companies = $query
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('content.companies.index', compact('companies'));
    }

    /**
     * Show the detail for a single company.
     */
  public function show(Company $company)
{
    $permissions = session('permissions');
    if (
        !isset($permissions['Company']) ||
        !in_array('read', $permissions['Company']['actions'])
    ) {
        abort(403, 'Unauthorized action.');
    }

    // جلب جميع الفروع الخاصة بالشركة
    $branches = $company->branches()->get();

    return view('content.companies.show', compact('company', 'branches'));
}
/**
 * Toggle the active status of a company.
 */
public function toggleStatus(Company $company)
{
    $permissions = session('permissions');
    if (
        !isset($permissions['Company']) ||
        !in_array('write', $permissions['Company']['actions'])
    ) {
        abort(403, 'Unauthorized action.');
    }

    $company->update(['active' => !$company->active]);

    return redirect()->back()->with('success', 'Company status updated successfully.');
}
   public function updatePassword(Request $request, Company $company)
    {
        $permissions = session('permissions');
        if (!isset($permissions['Company']) || !in_array('write', $permissions['Company']['actions'])) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $company->update([
            'password' => $request->password, // سيتم تشفيره تلقائيًا بسبب cast: 'password' => 'hashed'
        ]);

        return redirect()->back()->with('success', 'Company password updated successfully.');
    }
}
