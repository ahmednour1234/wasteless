<?php

namespace App\Http\Controllers\API\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BranchController extends Controller
{
    /**
     * GET /api/branches
     * List all active branches, with optional filters.
     */
    public function index(Request $request)
    {
        $query = Branch::where('active', true);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('address')) {
            $query->where('address', 'like', '%' . $request->address . '%');
        }
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        $branches = $query
            ->orderBy('id', 'desc')
            ->paginate(15);

        return BranchResource::collection($branches);
    }

    /**
     * GET /api/company/branches
     * List all active branches for the authenticated company.
     */
    public function companyBranches(Request $request)
    {
        $company = $request->user(); // authenticated via company guard

        $branches = $company->branches()
            ->get();

        return BranchResource::collection($branches);
    }

    /**
     * POST /api/branches
     * Create a new branch for the authenticated company.
     */
    public function store(Request $request)
    {
        $company = $request->user();

        $data = $request->validate([
            'lat'     => 'required|numeric',
            'lng'     => 'required|numeric',
            'name'    => 'required|string|max:255',
            'address' => 'required|string',
            'phone'   => 'required|string|max:50',
            'active'  => 'sometimes|boolean',
        ]);

        // associate branch with the logged-in company
        $branch = $company->branches()->create($data);

        return (new BranchResource($branch))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * GET /api/branches/{branch}
     * Show a single branch.
     */
    public function show(Branch $branch)
    {
        return new BranchResource($branch);
    }

    /**
     * PUT|PATCH /api/branches/{branch}
     * Update an existing branch (only if it belongs to auth company).
     */

public function update(Request $request, Branch $branch)
{
    $company = $request->user();

    // تأكد أن الفرع يخص الشركة
    if ($branch->company_id !== $company->id) {
        return response()->json([
            'message' => 'Unauthorized.'
        ], Response::HTTP_FORBIDDEN);
    }

    // التحقق من المدخلات
    $data = $request->validate([
        'lat'     => 'sometimes|required|numeric',
        'lng'     => 'sometimes|required|numeric',
        'name'    => 'sometimes|required|string|max:255',
        'address' => 'sometimes|required|string',
        'phone'   => 'sometimes|required|string|max:50',
        'active'  => 'sometimes|boolean',
        'main'    => 'sometimes|boolean',
    ]);

    // ⚠️ منطق ضمان وجود فرع رئيسي دائمًا
    if (isset($data['main']) && $data['main'] === false) {
        $mainBranchesCount = Branch::where('company_id', $company->id)
            ->where('main', true)
            ->count();

        // إذا هذا الفرع هو الوحيد الرئيسي
        if ($mainBranchesCount === 1 && $branch->main) {
            return response()->json([
                'message' => 'يجب أن تحتوي الشركة على فرع رئيسي واحد على الأقل.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // إذا تم طلب أن يكون هذا الفرع هو الرئيسي
    if (isset($data['main']) && $data['main'] === true) {
        Branch::where('company_id', $company->id)
              ->where('id', '!=', $branch->id)
              ->update(['main' => false]);
    }

    // تحديث بيانات الفرع
    $branch->update($data);

    return new BranchResource($branch);
}

}
