<?php

namespace Jiny\Service\Http\Controllers\Admin\ServiceUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\Service;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'user_uuid' => 'required|string|max:255',
            'user_shard' => 'required|string|max:255',
            'user_id' => 'required|integer',
            'user_email' => 'required|email|max:255',
            'user_name' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
            'status' => 'required|in:pending,active,suspended,cancelled,expired',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,lifetime',
            'started_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:started_at',
            'next_billing_at' => 'nullable|date',
            'plan_name' => 'nullable|string|max:255',
            'plan_price' => 'nullable|numeric|min:0',
            'plan_features' => 'nullable|array',
            'monthly_price' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'auto_renewal' => 'boolean',
            'auto_upgrade' => 'boolean',
            'admin_notes' => 'nullable|string',
        ]);

        try {
            // 서비스 정보 조회
            $service = Service::findOrFail($validated['service_id']);
            $validated['service_title'] = $service->title;

            // UUID가 제공되지 않은 경우 생성
            if (empty($validated['user_uuid'])) {
                $validated['user_uuid'] = Str::uuid()->toString();
            }

            // 기본값 설정
            if (!isset($validated['started_at']) && $validated['status'] === 'active') {
                $validated['started_at'] = now();
            }

            if (!isset($validated['expires_at']) && isset($validated['started_at'])) {
                $startDate = $validated['started_at'];
                switch ($validated['billing_cycle']) {
                    case 'monthly':
                        $validated['expires_at'] = now()->parse($startDate)->addMonth();
                        break;
                    case 'quarterly':
                        $validated['expires_at'] = now()->parse($startDate)->addMonths(3);
                        break;
                    case 'yearly':
                        $validated['expires_at'] = now()->parse($startDate)->addYear();
                        break;
                    case 'lifetime':
                        $validated['expires_at'] = now()->addYears(100); // 평생은 100년으로 설정
                        break;
                }
            }

            if (!isset($validated['next_billing_at']) && $validated['billing_cycle'] !== 'lifetime') {
                $validated['next_billing_at'] = $validated['expires_at'];
            }

            // 사용자 생성
            $serviceUser = ServiceUser::create($validated);

            // 사용자 캐시 정보 업데이트
            $serviceUser->updateUserCache();

            return redirect()
                ->route('admin.service.users.show', $serviceUser->id)
                ->with('success', '서비스 구독자가 성공적으로 생성되었습니다.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '구독자 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}