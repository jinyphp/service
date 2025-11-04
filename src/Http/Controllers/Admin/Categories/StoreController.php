<?php

namespace Jiny\Service\Http\Controllers\Admin\Categories;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service Categories 저장 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/service/categories') → StoreController::__invoke()
 */
class StoreController extends Controller
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

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:service_categories,code',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:service_categories,id',
            'pos' => 'nullable|integer|min:0',
            'enable' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        // Generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['title']);

            // Ensure code is unique
            $originalCode = $validated['code'];
            $counter = 1;
            while (DB::table($this->config['table'])->where('code', $validated['code'])->exists()) {
                $validated['code'] = $originalCode . '-' . $counter;
                $counter++;
            }
        }

        // Set defaults
        $validated['enable'] = $request->has('enable') ? true : false;
        $validated['pos'] = $validated['pos'] ?? 0;
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        try {
            $id = DB::table($this->config['table'])->insertGetId($validated);

            return redirect()
                ->route($this->config['redirect_route'])
                ->with('success', '카테고리가 성공적으로 생성되었습니다.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '카테고리 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}