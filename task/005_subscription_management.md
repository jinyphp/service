# 005. Subscription Management System - TDD Implementation

## 개요
구독 생명주기 관리 시스템: 구독 생성, 수정, 취소, 갱신 및 청구 처리

## 의존관계
- **선행 태스크**: [004. 서비스 카탈로그](004_service_catalog.md)
- **후속 태스크**: [006. 파트너 트리 구조](006_partner_tree_structure.md)

## TDD 테스트 시나리오 (모두 HTTP 200 반환)

### Admin 구독 관리 테스트

#### 1. 구독 목록 조회
**테스트**: `AdminSubscriptionListTest`

```php
public function test_admin_subscription_list_returns_200()
{
    // Given: 관리자와 구독 데이터
    $admin = User::factory()->admin()->create();
    $service = Service::factory()->create();
    Subscription::factory()->count(10)->create(['service_id' => $service->id]);

    // When: 구독 목록 조회
    $response = $this->actingAs($admin)->get('/admin/service/subscriptions');

    // Then: HTTP 200과 구독 목록
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'data' => [
            'subscriptions' => [
                '*' => ['id', 'customer_name', 'service_name', 'status', 'start_date', 'amount']
            ],
            'summary' => ['total_subscriptions', 'active_subscriptions', 'monthly_revenue'],
            'pagination'
        ]
    ]);
}

public function test_admin_subscription_filtering_returns_200()
{
    // Given: 다양한 상태의 구독
    $admin = User::factory()->admin()->create();
    Subscription::factory()->create(['status' => 'active']);
    Subscription::factory()->create(['status' => 'cancelled']);
    Subscription::factory()->create(['status' => 'trial']);

    // When: 상태별 필터링
    $response = $this->actingAs($admin)->get('/admin/service/subscriptions?status=active');

    // Then: HTTP 200과 필터된 결과
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data.subscriptions');
}
```

#### 2. 구독 상세 조회
**테스트**: `AdminSubscriptionDetailTest`

```php
public function test_admin_subscription_detail_returns_200()
{
    // Given: 관리자와 구독
    $admin = User::factory()->admin()->create();
    $subscription = Subscription::factory()->create();
    SubscriptionBilling::factory()->count(3)->create(['subscription_id' => $subscription->id]);
    ServiceExecution::factory()->count(2)->create(['subscription_id' => $subscription->id]);

    // When: 구독 상세 조회
    $response = $this->actingAs($admin)->get("/admin/service/subscriptions/{$subscription->id}");

    // Then: HTTP 200과 상세 정보
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'subscription' => ['id', 'customer', 'service', 'status', 'billing_cycle'],
            'billing_history' => ['*' => ['date', 'amount', 'status']],
            'service_history' => ['*' => ['date', 'engineer', 'status', 'rating']]
        ]
    ]);
}
```

#### 3. 구독 상태 변경
**테스트**: `AdminSubscriptionStatusUpdateTest`

```php
public function test_admin_can_update_subscription_status_returns_200()
{
    // Given: 관리자와 활성 구독
    $admin = User::factory()->admin()->create();
    $subscription = Subscription::factory()->create(['status' => 'active']);

    // When: 구독 상태 변경
    $response = $this->actingAs($admin)->patch("/admin/service/subscriptions/{$subscription->id}/status", [
        'status' => 'suspended',
        'reason' => 'Payment failure',
        'effective_date' => now()->addDays(1)->format('Y-m-d')
    ]);

    // Then: HTTP 200과 상태 변경
    $response->assertStatus(200);
    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'status' => 'suspended'
    ]);
}
```

### Customer 구독 관리 테스트

#### 4. 고객 구독 목록
**테스트**: `CustomerSubscriptionListTest`

```php
public function test_customer_subscription_list_returns_200()
{
    // Given: 고객과 JWT 토큰
    [$customer, $token] = $this->createCustomerWithJWT();
    Subscription::factory()->count(3)->create(['customer_id' => $customer->id]);

    // When: 고객 구독 목록 조회
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                     ->get('/home/service/subscriptions');

    // Then: HTTP 200과 고객의 구독만 반환
    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data.subscriptions');
    $response->assertJsonStructure([
        'data' => [
            'subscriptions' => ['*' => ['id', 'service_name', 'status', 'next_service']],
            'upcoming_services' => ['*' => ['subscription_id', 'service_date', 'engineer']]
        ]
    ]);
}
```

#### 5. 구독 취소 요청
**테스트**: `CustomerSubscriptionCancelTest`

```php
public function test_customer_can_cancel_subscription_returns_200()
{
    // Given: 고객과 활성 구독
    [$customer, $token] = $this->createCustomerWithJWT();
    $subscription = Subscription::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'active'
    ]);

    // When: 구독 취소 요청
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                     ->post("/home/service/subscriptions/{$subscription->id}/cancel", [
                         'reason' => 'Service no longer needed',
                         'cancel_immediately' => false,
                         'feedback_rating' => 4,
                         'feedback_comment' => 'Good service overall'
                     ]);

    // Then: HTTP 200과 취소 처리
    $response->assertStatus(200);
    $response->assertJson(['status' => 'success']);
    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'status' => 'pending_cancellation'
    ]);
}

public function test_customer_cannot_cancel_others_subscription()
{
    // Given: 두 명의 고객
    [$customer1, $token1] = $this->createCustomerWithJWT();
    [$customer2, $token2] = $this->createCustomerWithJWT();
    $subscription = Subscription::factory()->create(['customer_id' => $customer2->id]);

    // When: 다른 고객의 구독 취소 시도
    $response = $this->withHeaders(['Authorization' => "Bearer {$token1}"])
                     ->post("/home/service/subscriptions/{$subscription->id}/cancel", [
                         'reason' => 'Test'
                     ]);

    // Then: 403 Forbidden
    $response->assertStatus(403);
}
```

### Partner 구독 관리 테스트

#### 6. 파트너 할당 구독 조회
**테스트**: `PartnerSubscriptionOverviewTest`

```php
public function test_partner_subscription_overview_returns_200()
{
    // Given: 파트너와 할당된 구독
    [$partner, $customer, $token] = $this->createPartnerWithJWT();
    $subscriptions = Subscription::factory()->count(5)->create();

    foreach ($subscriptions as $subscription) {
        ServiceExecution::factory()->create([
            'subscription_id' => $subscription->id,
            'partner_id' => $partner->id,
            'status' => 'scheduled'
        ]);
    }

    // When: 파트너 구독 개요 조회
    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                     ->get('/partner/subscriptions');

    // Then: HTTP 200과 할당된 구독 정보
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'overview' => ['assigned_subscriptions', 'completed_services', 'customer_satisfaction'],
            'subscriptions' => ['*' => ['id', 'customer_name', 'service_date', 'status']]
        ]
    ]);
}
```

## 컨트롤러 구현

### 1. Admin Subscription Controller

```php
<?php

namespace Jiny\Service\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Jiny\Service\Models\Subscription;
use Jiny\Service\Models\SubscriptionBilling;
use Jiny\Service\Models\ServiceExecution;
use Jiny\Service\Services\CustomerShardService;

class SubscriptionController extends Controller
{
    private CustomerShardService $shardService;

    public function __construct(CustomerShardService $shardService)
    {
        $this->shardService = $shardService;
    }

    public function index(Request $request)
    {
        $query = Subscription::with(['service']);

        // 검색 필터
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('service', function ($serviceQuery) use ($search) {
                    $serviceQuery->where('name', 'like', "%{$search}%");
                })->orWhere('customer_id', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // 서비스 필터
        if ($serviceId = $request->get('service_id')) {
            $query->where('service_id', $serviceId);
        }

        // 날짜 범위 필터
        if ($dateFrom = $request->get('date_from')) {
            $query->where('start_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->where('start_date', '<=', $dateTo);
        }

        // 정렬
        $query->orderBy($request->get('sort', 'created_at'),
                       $request->get('direction', 'desc'));

        $subscriptions = $query->paginate($request->get('per_page', 20));

        // 구독 데이터 변환 (고객 정보 포함)
        $transformedSubscriptions = $subscriptions->getCollection()->map(function ($subscription) {
            $customer = $this->getCustomerInfo($subscription->customer_id);

            return [
                'id' => $subscription->id,
                'customer_name' => $customer->name ?? 'Unknown',
                'customer_email' => $customer->email ?? 'Unknown',
                'service_name' => $subscription->service->name,
                'status' => $subscription->status,
                'start_date' => $subscription->start_date,
                'end_date' => $subscription->end_date,
                'amount' => $subscription->amount,
                'billing_cycle' => $subscription->billing_cycle,
                'next_billing' => $subscription->next_billing_date,
                'created_at' => $subscription->created_at
            ];
        });

        // 요약 통계
        $summary = [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'trial_subscriptions' => Subscription::where('status', 'trial')->count(),
            'cancelled_subscriptions' => Subscription::where('status', 'cancelled')->count(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'churn_rate' => $this->calculateChurnRate()
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'subscriptions' => $transformedSubscriptions,
                'summary' => $summary,
                'pagination' => [
                    'current_page' => $subscriptions->currentPage(),
                    'total_pages' => $subscriptions->lastPage(),
                    'total_count' => $subscriptions->total()
                ]
            ]
        ], 200);
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['service']);

        // 고객 정보
        $customer = $this->getCustomerInfo($subscription->customer_id);

        // 청구 이력
        $billingHistory = SubscriptionBilling::where('subscription_id', $subscription->id)
                                            ->orderBy('billing_date', 'desc')
                                            ->get();

        // 서비스 실행 이력
        $serviceHistory = ServiceExecution::where('subscription_id', $subscription->id)
                                         ->with(['partner'])
                                         ->orderBy('scheduled_date', 'desc')
                                         ->get();

        // 구독 메트릭스
        $metrics = [
            'total_paid' => $billingHistory->where('status', 'paid')->sum('total_amount'),
            'average_rating' => $serviceHistory->whereNotNull('customer_rating')->avg('customer_rating'),
            'services_completed' => $serviceHistory->where('status', 'completed')->count(),
            'next_service_date' => $serviceHistory->where('status', 'scheduled')->first()?->scheduled_date
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'subscription' => [
                    'id' => $subscription->id,
                    'customer' => [
                        'id' => $customer->id ?? null,
                        'name' => $customer->name ?? 'Unknown',
                        'email' => $customer->email ?? 'Unknown',
                        'phone' => $customer->phone ?? null
                    ],
                    'service' => $subscription->service,
                    'status' => $subscription->status,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'trial_end_date' => $subscription->trial_end_date,
                    'billing_cycle' => $subscription->billing_cycle,
                    'amount' => $subscription->amount,
                    'discount_amount' => $subscription->discount_amount,
                    'next_billing_date' => $subscription->next_billing_date,
                    'auto_renewal' => $subscription->auto_renewal,
                    'cancellation_date' => $subscription->cancellation_date,
                    'cancellation_reason' => $subscription->cancellation_reason
                ],
                'billing_history' => $billingHistory,
                'service_history' => $serviceHistory,
                'metrics' => $metrics
            ]
        ], 200);
    }

    public function updateStatus(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,cancelled,suspended,expired',
            'reason' => 'required_if:status,cancelled,suspended|string|max:500',
            'effective_date' => 'nullable|date|after_or_equal:today'
        ]);

        $oldStatus = $subscription->status;
        $effectiveDate = $validated['effective_date'] ?? now();

        // 상태 변경 로직
        switch ($validated['status']) {
            case 'cancelled':
                $this->handleCancellation($subscription, $validated['reason'], $effectiveDate);
                break;
            case 'suspended':
                $this->handleSuspension($subscription, $validated['reason'], $effectiveDate);
                break;
            case 'active':
                $this->handleReactivation($subscription, $effectiveDate);
                break;
            default:
                $subscription->update(['status' => $validated['status']]);
        }

        // 상태 변경 로그
        $this->logStatusChange($subscription, $oldStatus, $validated['status'], $validated['reason'] ?? null);

        // 고객 알림 발송
        $this->notifyCustomer($subscription, $validated['status'], $validated['reason'] ?? null);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription status updated successfully',
            'data' => [
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->fresh()->status,
                    'effective_date' => $effectiveDate,
                    'reason' => $validated['reason'] ?? null
                ]
            ]
        ], 200);
    }

    public function analytics(Request $request)
    {
        $period = $request->get('period', 'month'); // week, month, quarter, year
        $startDate = $this->getStartDate($period);

        // 구독 성장 분석
        $growthData = $this->calculateGrowthMetrics($startDate);

        // 수익 분석
        $revenueData = $this->calculateRevenueMetrics($startDate);

        // 이탈 분석
        $churnData = $this->calculateChurnMetrics($startDate);

        // 서비스별 성과
        $servicePerformance = $this->calculateServicePerformance($startDate);

        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => $period,
                'date_range' => [
                    'start' => $startDate,
                    'end' => now()
                ],
                'growth' => $growthData,
                'revenue' => $revenueData,
                'churn' => $churnData,
                'service_performance' => $servicePerformance
            ]
        ], 200);
    }

    public function processRefund(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'billing_id' => 'required|exists:subscription_billings,id',
            'refund_amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
            'refund_method' => 'required|in:original_payment,credit,manual'
        ]);

        $billing = SubscriptionBilling::findOrFail($validated['billing_id']);

        // 환불 가능 여부 확인
        if ($billing->status !== 'paid') {
            return response()->json(['error' => 'Cannot refund unpaid billing'], 422);
        }

        if ($validated['refund_amount'] > $billing->total_amount) {
            return response()->json(['error' => 'Refund amount exceeds paid amount'], 422);
        }

        // 환불 처리
        $this->processRefundTransaction($billing, $validated);

        // 환불 로그
        $this->logRefund($subscription, $billing, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Refund processed successfully',
            'data' => [
                'refund_amount' => $validated['refund_amount'],
                'refund_method' => $validated['refund_method'],
                'processed_at' => now()
            ]
        ], 200);
    }

    private function getCustomerInfo(int $customerId): ?object
    {
        // 샤드에서 고객 정보 조회
        return $this->shardService->findCustomerById($customerId);
    }

    private function calculateMonthlyRevenue(): float
    {
        return SubscriptionBilling::where('status', 'paid')
                                 ->whereMonth('billing_date', now()->month)
                                 ->whereYear('billing_date', now()->year)
                                 ->sum('total_amount');
    }

    private function calculateChurnRate(): float
    {
        $totalSubscriptions = Subscription::count();
        $cancelledThisMonth = Subscription::where('status', 'cancelled')
                                        ->whereMonth('cancellation_date', now()->month)
                                        ->count();

        return $totalSubscriptions > 0 ? ($cancelledThisMonth / $totalSubscriptions) * 100 : 0;
    }

    private function handleCancellation(Subscription $subscription, string $reason, $effectiveDate): void
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancellation_date' => $effectiveDate,
            'cancellation_reason' => $reason,
            'auto_renewal' => false
        ]);

        // 미래 청구 취소
        SubscriptionBilling::where('subscription_id', $subscription->id)
                          ->where('billing_date', '>', $effectiveDate)
                          ->where('status', 'pending')
                          ->update(['status' => 'cancelled']);
    }

    private function handleSuspension(Subscription $subscription, string $reason, $effectiveDate): void
    {
        $subscription->update([
            'status' => 'suspended',
            'notes' => $subscription->notes . "\nSuspended on {$effectiveDate}: {$reason}"
        ]);

        // 예정된 서비스 실행 일시 정지
        ServiceExecution::where('subscription_id', $subscription->id)
                       ->where('status', 'scheduled')
                       ->where('scheduled_date', '>', $effectiveDate)
                       ->update(['status' => 'cancelled']);
    }

    private function handleReactivation(Subscription $subscription, $effectiveDate): void
    {
        $subscription->update([
            'status' => 'active',
            'notes' => $subscription->notes . "\nReactivated on {$effectiveDate}"
        ]);

        // 다음 청구일 재계산
        $this->recalculateNextBillingDate($subscription);
    }

    private function logStatusChange(Subscription $subscription, string $oldStatus, string $newStatus, ?string $reason): void
    {
        \DB::table('subscription_status_logs')->insert([
            'subscription_id' => $subscription->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'changed_by' => auth()->id(),
            'changed_at' => now()
        ]);
    }

    private function notifyCustomer(Subscription $subscription, string $status, ?string $reason): void
    {
        // 큐에 알림 작업 추가
        // dispatch(new SendSubscriptionStatusNotification($subscription, $status, $reason));
    }

    // 추가 헬퍼 메서드들...
    private function getStartDate(string $period): \DateTime
    {
        return match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
    }
}
```

### 2. Customer Subscription Controller

```php
<?php

namespace Jiny\Service\Http\Controllers\Customer;

use Illuminate\Http\Request;
use Jiny\Service\Models\Subscription;
use Jiny\Service\Models\ServiceExecution;
use Jiny\Service\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    private SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function index(Request $request)
    {
        $customer = $request->input('authenticated_customer');

        $query = Subscription::where('customer_id', $customer->id)
                            ->with(['service']);

        // 상태 필터
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->get();

        // 구독 데이터 변환
        $transformedSubscriptions = $subscriptions->map(function ($subscription) {
            return [
                'id' => $subscription->id,
                'service_name' => $subscription->service->name,
                'service_image' => $subscription->service->image_url,
                'status' => $subscription->status,
                'start_date' => $subscription->start_date,
                'end_date' => $subscription->end_date,
                'trial_end_date' => $subscription->trial_end_date,
                'amount' => $subscription->amount,
                'billing_cycle' => $subscription->billing_cycle,
                'next_billing' => $subscription->next_billing_date,
                'auto_renewal' => $subscription->auto_renewal,
                'can_cancel' => $this->canCancel($subscription),
                'can_modify' => $this->canModify($subscription)
            ];
        });

        // 다가오는 서비스
        $upcomingServices = ServiceExecution::whereIn('subscription_id', $subscriptions->pluck('id'))
                                           ->where('status', 'scheduled')
                                           ->where('scheduled_date', '>', now())
                                           ->orderBy('scheduled_date')
                                           ->limit(5)
                                           ->get()
                                           ->map(function ($execution) {
                                               return [
                                                   'subscription_id' => $execution->subscription_id,
                                                   'service_date' => $execution->scheduled_date,
                                                   'service_name' => $execution->subscription->service->name,
                                                   'engineer' => $execution->partner->name ?? 'To be assigned',
                                                   'status' => $execution->status
                                               ];
                                           });

        return response()->json([
            'status' => 'success',
            'data' => [
                'subscriptions' => $transformedSubscriptions,
                'upcoming_services' => $upcomingServices,
                'summary' => [
                    'total_subscriptions' => $subscriptions->count(),
                    'active_subscriptions' => $subscriptions->where('status', 'active')->count(),
                    'trial_subscriptions' => $subscriptions->where('status', 'trial')->count()
                ]
            ]
        ], 200);
    }

    public function show(Request $request, Subscription $subscription)
    {
        $customer = $request->input('authenticated_customer');

        // 소유권 확인
        if ($subscription->customer_id !== $customer->id) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        $subscription->load(['service']);

        // 서비스 실행 이력
        $serviceHistory = ServiceExecution::where('subscription_id', $subscription->id)
                                         ->orderBy('scheduled_date', 'desc')
                                         ->get()
                                         ->map(function ($execution) {
                                             return [
                                                 'id' => $execution->id,
                                                 'scheduled_date' => $execution->scheduled_date,
                                                 'completed_date' => $execution->completed_date,
                                                 'status' => $execution->status,
                                                 'engineer' => $execution->partner->name ?? 'Not assigned',
                                                 'duration' => $execution->duration_minutes,
                                                 'rating' => $execution->customer_rating,
                                                 'feedback' => $execution->customer_feedback,
                                                 'before_images' => json_decode($execution->before_images, true),
                                                 'after_images' => json_decode($execution->after_images, true)
                                             ];
                                         });

        // 청구 이력
        $billingHistory = $subscription->billings()
                                      ->orderBy('billing_date', 'desc')
                                      ->get()
                                      ->map(function ($billing) {
                                          return [
                                              'id' => $billing->id,
                                              'billing_date' => $billing->billing_date,
                                              'due_date' => $billing->due_date,
                                              'amount' => $billing->amount,
                                              'total_amount' => $billing->total_amount,
                                              'status' => $billing->status,
                                              'paid_at' => $billing->paid_at,
                                              'invoice_number' => $billing->invoice_number
                                          ];
                                      });

        return response()->json([
            'status' => 'success',
            'data' => [
                'subscription' => [
                    'id' => $subscription->id,
                    'service' => $subscription->service,
                    'status' => $subscription->status,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'trial_end_date' => $subscription->trial_end_date,
                    'billing_cycle' => $subscription->billing_cycle,
                    'amount' => $subscription->amount,
                    'discount_amount' => $subscription->discount_amount,
                    'next_billing_date' => $subscription->next_billing_date,
                    'auto_renewal' => $subscription->auto_renewal
                ],
                'service_history' => $serviceHistory,
                'billing_history' => $billingHistory,
                'options' => [
                    'can_cancel' => $this->canCancel($subscription),
                    'can_modify' => $this->canModify($subscription),
                    'can_pause' => $this->canPause($subscription),
                    'can_resume' => $this->canResume($subscription)
                ]
            ]
        ], 200);
    }

    public function subscribe(Request $request, Service $service)
    {
        $customer = $request->input('authenticated_customer');

        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'auto_renewal' => 'boolean',
            'payment_method_id' => 'required|string',
            'start_date' => 'nullable|date|after_or_equal:today'
        ]);

        // 중복 구독 확인
        $existingSubscription = Subscription::where('customer_id', $customer->id)
                                          ->where('service_id', $service->id)
                                          ->whereIn('status', ['active', 'trial'])
                                          ->first();

        if ($existingSubscription) {
            return response()->json(['error' => 'You already have an active subscription for this service'], 422);
        }

        // 구독 생성
        $subscription = $this->subscriptionService->createSubscription($customer, $service, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription created successfully',
            'data' => [
                'subscription' => [
                    'id' => $subscription->id,
                    'service_name' => $subscription->service->name,
                    'status' => $subscription->status,
                    'start_date' => $subscription->start_date,
                    'billing_cycle' => $subscription->billing_cycle,
                    'amount' => $subscription->amount
                ]
            ]
        ], 200);
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        $customer = $request->input('authenticated_customer');

        // 소유권 확인
        if ($subscription->customer_id !== $customer->id) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        // 취소 가능 여부 확인
        if (!$this->canCancel($subscription)) {
            return response()->json(['error' => 'Subscription cannot be cancelled'], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'cancel_immediately' => 'boolean',
            'feedback_rating' => 'nullable|integer|min:1|max:5',
            'feedback_comment' => 'nullable|string|max:1000'
        ]);

        // 취소 처리
        $cancellationResult = $this->subscriptionService->cancelSubscription($subscription, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription cancellation request submitted',
            'data' => [
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->fresh()->status,
                    'cancellation_date' => $cancellationResult['cancellation_date'],
                    'refund_amount' => $cancellationResult['refund_amount']
                ]
            ]
        ], 200);
    }

    public function modify(Request $request, Subscription $subscription)
    {
        $customer = $request->input('authenticated_customer');

        // 소유권 확인
        if ($subscription->customer_id !== $customer->id) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        $validated = $request->validate([
            'billing_cycle' => 'nullable|in:monthly,quarterly,yearly',
            'auto_renewal' => 'nullable|boolean',
            'effective_date' => 'nullable|date|after_or_equal:today'
        ]);

        // 수정 처리
        $modificationResult = $this->subscriptionService->modifySubscription($subscription, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription modification request submitted',
            'data' => [
                'subscription' => $subscription->fresh(),
                'changes' => $modificationResult['changes'],
                'effective_date' => $modificationResult['effective_date']
            ]
        ], 200);
    }

    public function pause(Request $request, Subscription $subscription)
    {
        $customer = $request->input('authenticated_customer');

        if ($subscription->customer_id !== $customer->id) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        $validated = $request->validate([
            'pause_duration' => 'required|integer|min:1|max:90', // 최대 90일
            'reason' => 'required|string|max:500'
        ]);

        $pauseResult = $this->subscriptionService->pauseSubscription($subscription, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription paused successfully',
            'data' => [
                'subscription' => $subscription->fresh(),
                'pause_until' => $pauseResult['pause_until']
            ]
        ], 200);
    }

    public function resume(Request $request, Subscription $subscription)
    {
        $customer = $request->input('authenticated_customer');

        if ($subscription->customer_id !== $customer->id) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        $resumeResult = $this->subscriptionService->resumeSubscription($subscription);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription resumed successfully',
            'data' => [
                'subscription' => $subscription->fresh(),
                'resumed_at' => $resumeResult['resumed_at']
            ]
        ], 200);
    }

    public function history(Request $request, Subscription $subscription)
    {
        $customer = $request->input('authenticated_customer');

        if ($subscription->customer_id !== $customer->id) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        // 모든 이력 정보 통합
        $timeline = $this->subscriptionService->getSubscriptionTimeline($subscription);

        return response()->json([
            'status' => 'success',
            'data' => [
                'timeline' => $timeline,
                'statistics' => [
                    'total_services' => $timeline->where('type', 'service_execution')->count(),
                    'total_payments' => $timeline->where('type', 'billing')->where('status', 'paid')->count(),
                    'total_paid' => $timeline->where('type', 'billing')->where('status', 'paid')->sum('amount'),
                    'average_rating' => $timeline->where('type', 'service_execution')->whereNotNull('rating')->avg('rating')
                ]
            ]
        ], 200);
    }

    // 헬퍼 메서드들
    private function canCancel(Subscription $subscription): bool
    {
        return in_array($subscription->status, ['active', 'trial']) &&
               ($subscription->cancellation_date === null);
    }

    private function canModify(Subscription $subscription): bool
    {
        return in_array($subscription->status, ['active', 'trial']);
    }

    private function canPause(Subscription $subscription): bool
    {
        return $subscription->status === 'active';
    }

    private function canResume(Subscription $subscription): bool
    {
        return $subscription->status === 'paused';
    }
}
```

계속해서 더 많은 태스크를 생성하겠습니다. 현재까지 005_subscription_management.md를 생성했습니다.

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[{"content": "Analyze task dependencies and create sequential numbering", "status": "completed", "activeForm": "Analyzing task dependencies and creating sequential numbering"}, {"content": "Create numbered task files in order", "status": "in_progress", "activeForm": "Creating numbered task files in order"}, {"content": "Update task overview with dependencies", "status": "pending", "activeForm": "Updating task overview with dependencies"}]