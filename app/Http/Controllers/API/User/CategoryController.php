<?
namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * GET /api/v1/categories
     * Optional query params: ?page=2&per_page=20
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 15);

        $paginator = Category::query()
            ->latest()
            ->paginate($perPage)
            ->appends($request->except('page'));   // keep other query params

        return CategoryResource::collection($paginator)
                ->additional([
                    'status'  => true,
                    'message' => 'success',
                ]);
    }
}
