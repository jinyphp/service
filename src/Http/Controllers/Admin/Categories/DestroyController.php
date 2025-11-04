<?php

namespace Jiny\Service\Http\Controllers\Admin\Categories;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Service Categories 삭제 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/service/categories/{id}') → DestroyController::__invoke()
 */
class DestroyController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        $this->config = [
            'table' => 'service_categories',
            'redirect_route' => 'admin.service.categories.index',
        ];
    }

    public function __invoke(Request $request, $id)
    {
        $category = DB::table($this->config['table'])
            ->where('id', $id)
            ->first();

        if (!$category) {
            return redirect()
                ->route($this->config['redirect_route'])
                ->with('error', '카테고리를 찾을 수 없습니다.');
        }

        try {
            // Check if category has services
            $serviceCount = DB::table('site_services')
                ->where('category_id', $id)
                ->whereNull('deleted_at')
                ->count();

            if ($serviceCount > 0) {
                return redirect()
                    ->route($this->config['redirect_route'])
                    ->with('error', '이 카테고리에 속한 서비스가 ' . $serviceCount . '개 있어서 삭제할 수 없습니다. 먼저 서비스들을 다른 카테고리로 이동하거나 삭제해주세요.');
            }

            // Check if category has subcategories
            $subCategoryCount = DB::table($this->config['table'])
                ->where('parent_id', $id)
                ->count();

            if ($subCategoryCount > 0) {
                return redirect()
                    ->route($this->config['redirect_route'])
                    ->with('error', '이 카테고리에 하위 카테고리가 ' . $subCategoryCount . '개 있어서 삭제할 수 없습니다. 먼저 하위 카테고리들을 삭제해주세요.');
            }

            // Delete the category
            DB::table($this->config['table'])
                ->where('id', $id)
                ->delete();

            return redirect()
                ->route($this->config['redirect_route'])
                ->with('success', '카테고리가 성공적으로 삭제되었습니다.');

        } catch (\Exception $e) {
            return redirect()
                ->route($this->config['redirect_route'])
                ->with('error', '카테고리 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}