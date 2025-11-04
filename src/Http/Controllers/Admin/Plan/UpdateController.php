<?php

namespace Jiny\Service\Http\Controllers\Admin\Plan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServicePlan;
use Illuminate\Support\Str;

class UpdateController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $plan = ServicePlan::findOrFail($id);

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'plan_name' => 'required|string|max:255',
            'plan_code' => 'required|string|max:255|unique:service_plans,plan_code,' . $plan->id,
            'description' => 'nullable|string',
            'plan_type' => 'required|in:basic,standard,premium,enterprise,custom',
            'billing_type' => 'required|in:subscription,one_time,usage_based,hybrid',

            // 가격 검증
            'monthly_price' => 'nullable|numeric|min:0',
            'quarterly_price' => 'nullable|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'lifetime_price' => 'nullable|numeric|min:0',
            'setup_fee' => 'nullable|numeric|min:0',

            // 제한사항 검증
            'max_users' => 'nullable|integer|min:0',
            'max_projects' => 'nullable|integer|min:0',
            'storage_limit_gb' => 'nullable|numeric|min:0',
            'api_calls_per_month' => 'nullable|integer|min:0',

            // 무료 체험 검증
            'trial_period_days' => 'nullable|integer|min:0|max:365',

            // 정렬 순서 검증
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'service_id', 'plan_name', 'plan_code', 'description',
            'plan_type', 'billing_type', 'setup_fee', 'trial_period_days',
            'max_users', 'max_projects', 'storage_limit_gb', 'api_calls_per_month',
            'sort_order', 'color_code', 'icon'
        ]);

        // 가격 정보 처리
        $data['monthly_price'] = $request->monthly_price ?: 0;
        $data['quarterly_price'] = $request->quarterly_price ?: 0;
        $data['yearly_price'] = $request->yearly_price ?: 0;
        $data['lifetime_price'] = $request->lifetime_price ?: 0;

        // 이용 가능한 결제 주기 설정
        $data['monthly_available'] = $request->has('monthly_available') && $data['monthly_price'] > 0;
        $data['quarterly_available'] = $request->has('quarterly_available') && $data['quarterly_price'] > 0;
        $data['yearly_available'] = $request->has('yearly_available') && $data['yearly_price'] > 0;
        $data['lifetime_available'] = $request->has('lifetime_available') && $data['lifetime_price'] > 0;

        // Boolean 필드 처리
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['is_popular'] = $request->has('is_popular');
        $data['allow_trial'] = $request->has('allow_trial');
        $data['auto_renewal'] = $request->has('auto_renewal');
        $data['immediate_upgrade'] = $request->has('immediate_upgrade');
        $data['immediate_downgrade'] = $request->has('immediate_downgrade');

        // 피처 처리
        $features = [];
        if ($request->filled('features')) {
            $features = array_filter($request->features);
        }
        $data['features'] = $features;

        // 제한사항 처리
        $limitations = [];
        if ($request->filled('limitations')) {
            foreach ($request->limitations as $key => $value) {
                if (!empty($value)) {
                    $limitations[$key] = $value;
                }
            }
        }
        $data['limitations'] = $limitations;

        // 할당량 처리
        $quotas = [];
        if ($request->filled('quotas')) {
            foreach ($request->quotas as $key => $value) {
                if (!empty($value)) {
                    $quotas[$key] = $value;
                }
            }
        }
        $data['quotas'] = $quotas;

        // 할인 규칙 처리
        $discountRules = [];
        if ($request->filled('discount_rules')) {
            $discountRules = array_filter($request->discount_rules);
        }
        $data['discount_rules'] = $discountRules;

        // 업그레이드/다운그레이드 경로 처리
        $data['upgrade_paths'] = $request->filled('upgrade_paths') ? array_filter($request->upgrade_paths) : [];
        $data['downgrade_paths'] = $request->filled('downgrade_paths') ? array_filter($request->downgrade_paths) : [];

        // 지역 제한 처리
        $data['available_regions'] = $request->filled('available_regions') ? array_filter($request->available_regions) : null;
        $data['restricted_regions'] = $request->filled('restricted_regions') ? array_filter($request->restricted_regions) : null;

        // 기본값 설정
        $data['sort_order'] = $data['sort_order'] ?: 0;
        $data['trial_period_days'] = $data['trial_period_days'] ?: 0;

        // 구독자가 있는 플랜의 경우 중요한 변경사항 확인
        $subscribersCount = $plan->serviceUsers()->count();
        if ($subscribersCount > 0) {
            // 가격 변경이 있는 경우 로그 기록
            $priceChanged = false;
            if ($plan->monthly_price != $data['monthly_price'] ||
                $plan->quarterly_price != $data['quarterly_price'] ||
                $plan->yearly_price != $data['yearly_price'] ||
                $plan->lifetime_price != $data['lifetime_price']) {
                $priceChanged = true;
            }

            // 플랜이 비활성화되는 경우 경고 메시지
            if ($plan->is_active && !$data['is_active']) {
                session()->flash('warning', "이 플랜을 비활성화하면 {$subscribersCount}명의 구독자에게 영향을 줄 수 있습니다.");
            }

            if ($priceChanged) {
                session()->flash('info', "가격 변경은 새로운 구독자부터 적용됩니다. 기존 구독자는 영향받지 않습니다.");
            }
        }

        $plan->update($data);

        return redirect()
            ->route('admin.service.plan.index')
            ->with('success', '서비스 플랜이 성공적으로 수정되었습니다.');
    }
}