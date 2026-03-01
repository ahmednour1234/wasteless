<?php
// app/Http/Controllers/Dashboard/CategoryController.php
namespace App\Http\Controllers\Dashboard;

use App\Helpers\FileHelper as UploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /* index */
    public function index(Request $request)
    {
         $permissions = session('permissions');
    if (!isset($permissions['Category']) || !in_array('read', $permissions['Category']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
                    $query = Category::query();

         if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        // filter by active status: 1 = active, 0 = inactive
        if ($request->filled('active')) {
            if ($request->active === '1' || $request->active === '0') {
                $query->where('is_active', $request->active);
            }
        }

        $categories = $query->latest()->paginate(10)->withQueryString();
        return view('content.categories.index', compact('categories','request'));
    }

    /* create */
    public function create()
    {
           $permissions = session('permissions');
    if (!isset($permissions['Category']) || !in_array('create', $permissions['Category']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        return view('content.categories.create');
    }

    /* store */
    public function store(Request $request)
    {
          $permissions = session('permissions');
    if (!isset($permissions['Category']) || !in_array('create', $permissions['Category']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        $data = $request->validate([
            'name'  => 'required|string|max:255|unique:categories,name',
            'image' => 'nullable|image|max:2048',
        ]);

        $data['image'] = UploadHelper::uploadImage($request->file('image'));
        Category::create($data);

        toastr()->success('تم إنشاء القسم بنجاح');          // ✅ بدل with(...)
        return to_route('category.index');
    }

    /* edit */
    public function edit(Category $category)
    {
       $permissions = session('permissions');
    if (!isset($permissions['Category']) || !in_array('write', $permissions['Category']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        return view('content.categories.edit', compact('category'));
    }

    /* update */
    public function update(Request $request, Category $category)
    {
        $permissions = session('permissions');
    if (!isset($permissions['Category']) || !in_array('write', $permissions['Category']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        $data = $request->validate([
            'name'  => 'required|string|max:255|unique:categories,name,'.$category->id,
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->file('image')) {
            $data['image'] = UploadHelper::uploadImage($request->file('image'));
        }

        $category->update($data);

        toastr()->success('تم التحديث بنجاح');              // ✅
        return back();
    }

    /* destroy */
    public function destroy(Category $category)
    {
        $category->delete();

        toastr()->success('تم الحذف');                      // ✅
        return back();
    }

    /* AJAX: toggle active */
    public function toggle(Category $category)
    {
        $permissions = session('permissions');
    if (!isset($permissions['Category']) || !in_array('write', $permissions['Category']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        $category->update(['is_active' => ! $category->is_active]);
        return response()->json(['status' => $category->is_active]);
    }
}
