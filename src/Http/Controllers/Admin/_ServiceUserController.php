<?php

namespace Jiny\Service\Http\Controllers\Admin;

use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\SiteService;
use Jiny\Service\Models\ServicePlan;
use Illuminate\Http\Request;

class ServiceUserController extends BaseAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = ServiceUser::class;
        $this->viewPath = 'jiny-service::admin.service-users';
        $this->routePrefix = 'service-users';
        $this->title = '서비스 사용자';
    }

    protected function getValidationRules($item = null): array
    {
        return [
            'user_uuid' => 'required|string|max:36',
            'user_shard' => 'nullable|string|max:100',
            'user_id' => 'nullable|integer',
            'user_email' => 'required|email|max:255',
            'user_name' => 'required|string|max:255',
            'service_id' => 'required|exists:site_services,id',
            'service_title' => 'required|string|max:255',
            'status' => 'required|in:pending,active,expired,cancelled,suspended',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'started_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'next_billing_at' => 'nullable|date',
            'plan_name' => 'required|string|max:255',
            'plan_price' => 'required|numeric|min:0',
            'plan_features' => 'nullable|json',
            'monthly_price' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'payment_status' => 'nullable|string|max:100',
            'auto_renewal' => 'boolean',
            'auto_upgrade' => 'boolean',
            'cancelled_at' => 'nullable|date',
            'cancel_reason' => 'nullable|string',
            'refund_amount' => 'nullable|numeric|min:0',
            'refunded_at' => 'nullable|date',
            'admin_notes' => 'nullable|string'
        ];
    }

    protected function getSearchFields(): array
    {
        return ['user_email', 'user_name', 'service_title', 'plan_name'];
    }

    public function index(Request $request)
    {
        $query = $this->model::with('service');

        // 검색 기능
        if ($request->has('search') && $request->search) {
            $searchFields = $this->getSearchFields();
            $query->where(function($q) use ($request, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $request->search . '%');
                }
            });
        }

        // 상태 필터
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // 서비스 필터
        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        $items = $query->orderBy('id', 'desc')->paginate(20);
        $services = SiteService::where('is_active', true)->orderBy('title')->get();

        return view("{$this->viewPath}.index", [
            'items' => $items,
            'services' => $services,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'searchValue' => $request->search,
            'selectedStatus' => $request->status,
            'selectedService' => $request->service_id
        ]);
    }

    public function create()
    {
        $services = SiteService::where('is_active', true)->orderBy('title')->get();

        return view("{$this->viewPath}.create", [
            'services' => $services,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    public function edit($id)
    {
        $item = $this->model::with('service')->findOrFail($id);
        $services = SiteService::where('is_active', true)->orderBy('title')->get();

        return view("{$this->viewPath}.edit", [
            'item' => $item,
            'services' => $services,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules());

        // JSON 필드 처리
        if ($request->has('plan_features') && is_string($request->plan_features)) {
            $validated['plan_features'] = json_decode($request->plan_features, true);
        }

        $item = $this->model::create($validated);

        return redirect()->route("admin.{$this->routePrefix}.show", $item->id)
            ->with('success', $this->title . ' 항목이 성공적으로 생성되었습니다.');
    }

    public function update(Request $request, $id)
    {
        $item = $this->model::findOrFail($id);
        $validated = $request->validate($this->getValidationRules($item));

        // JSON 필드 처리
        if ($request->has('plan_features') && is_string($request->plan_features)) {
            $validated['plan_features'] = json_decode($request->plan_features, true);
        }

        $item->update($validated);

        return redirect()->route("admin.{$this->routePrefix}.show", $item->id)
            ->with('success', $this->title . ' 항목이 성공적으로 수정되었습니다.');
    }
}