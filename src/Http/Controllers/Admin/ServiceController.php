<?php

namespace Jiny\Service\Http\Controllers\Admin;

use Jiny\Service\Models\Service;
use Jiny\Service\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends BaseAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = Service::class;
        $this->viewPath = 'jiny-service::admin.services';
        $this->routePrefix = 'services';
        $this->title = '서비스';
    }

    protected function getValidationRules($item = null): array
    {
        return [
            'service_category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'image' => 'nullable|string|max:500',
            'base_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer'
        ];
    }

    protected function getSearchFields(): array
    {
        return ['name', 'description', 'short_description'];
    }

    public function index(Request $request)
    {
        $query = $this->model::with('category');

        // 검색 기능
        if ($request->has('search') && $request->search) {
            $searchFields = $this->getSearchFields();
            $query->where(function($q) use ($request, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $request->search . '%');
                }
            });
        }

        // 카테고리 필터
        if ($request->has('category_id') && $request->category_id) {
            $query->where('service_category_id', $request->category_id);
        }

        $items = $query->orderBy('id', 'desc')->paginate(20);
        $categories = ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view("{$this->viewPath}.index", [
            'items' => $items,
            'categories' => $categories,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'searchValue' => $request->search,
            'selectedCategory' => $request->category_id
        ]);
    }

    public function create()
    {
        $categories = ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view("{$this->viewPath}.create", [
            'categories' => $categories,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    public function edit($id)
    {
        $item = $this->model::findOrFail($id);
        $categories = ServiceCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'categories' => $categories,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }
}