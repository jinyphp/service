<?php

namespace Jiny\Service\Http\Controllers\Admin;

use Jiny\Service\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends BaseAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = ServiceCategory::class;
        $this->viewPath = 'jiny-service::admin.service-categories';
        $this->routePrefix = 'service-categories';
        $this->title = '서비스 카테고리';
    }

    protected function getValidationRules($item = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer'
        ];
    }

    protected function getSearchFields(): array
    {
        return ['name', 'description'];
    }
}