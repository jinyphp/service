<?php

namespace Jiny\Service\Http\Controllers\Admin\Categories;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service Categories 업데이트 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/service/categories/{id}') → UpdateController::__invoke()
 */
class UpdateController extends Controller
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

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:service_categories,code,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:service_categories,id',
            'pos' => 'nullable|integer|min:0',
            'enable' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        // Prevent setting parent to itself or creating circular reference
        if ($validated['parent_id'] == $id) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '카테고리는 자기 자신을 부모로 설정할 수 없습니다.');
        }

        // Generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['title']);

            // Ensure code is unique (excluding current record)
            $originalCode = $validated['code'];
            $counter = 1;
            while (DB::table($this->config['table'])->where('code', $validated['code'])->where('id', '!=', $id)->exists()) {
                $validated['code'] = $originalCode . '-' . $counter;
                $counter++;
            }
        }

        // Set defaults
        $validated['enable'] = $request->has('enable') ? true : false;
        $validated['pos'] = $validated['pos'] ?? 0;
        $validated['updated_at'] = now();

        try {
            DB::table($this->config['table'])
                ->where('id', $id)
                ->update($validated);

            return redirect()
                ->route($this->config['redirect_route'])
                ->with('success', '카테고리가 성공적으로 업데이트되었습니다.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '카테고리 업데이트 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}