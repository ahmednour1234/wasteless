<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * GET /api/user/companies
     * Returns a paginated list of all companies (15 per page).
     */
    public function index(Request $request)
    {
        // NOTE: perPage is hard-coded to 15 for a static pagination size.
        $companies = Company::orderBy('id', 'desc')
                            ->paginate(15);

    return new ApiCollection($companies, CompanyResource::class);
    }
}
