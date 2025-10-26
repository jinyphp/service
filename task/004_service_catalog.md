# 004. Service Catalog Management - TDD Implementation

## 개요
서비스 카탈로그 CRUD 관리 시스템 구현: 서비스 생성, 수정, 삭제, 조회 및 공개 카탈로그

## 의존관계
- **선행 태스크**: [003. 인증 시스템](003_authentication_system.md)
- **후속 태스크**: [005. 구독 관리 시스템](005_subscription_management.md)

## TDD 테스트 시나리오 (모두 HTTP 200 반환)

### Admin 서비스 관리 테스트

#### 1. 서비스 카탈로그 목록 조회
**테스트**: `AdminServiceCatalogListTest`

```php
public function test_admin_service_catalog_list_returns_200()
{
    // Given: 관리자와 서비스 데이터
    $admin = User::factory()->admin()->create();
    Service::factory()->count(5)->create();

    // When: 서비스 목록 조회
    $response = $this->actingAs($admin)->get('/admin/service/catalog');

    // Then: HTTP 200과 서비스 목록
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'data' => [
            'services' => [
                '*' => ['id', 'name', 'category', 'status', 'base_price', 'created_at']
            ],
            'pagination' => ['current_page', 'total_pages', 'total_count']
        ]
    ]);
}

public function test_admin_service_search_returns_200()
{
    // Given: 관리자와 검색 가능한 서비스
    $admin = User::factory()->admin()->create();
    Service::factory()->create(['name' => 'Premium Air Conditioning Service']);
    Service::factory()->create(['name' => 'Basic Cleaning Service']);

    // When: 서비스 검색
    $response = $this->actingAs($admin)->get('/admin/service/catalog?search=Air');

    // Then: HTTP 200과 검색 결과
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data.services');
    $response->assertJsonPath('data.services.0.name', 'Premium Air Conditioning Service');
}
```

#### 2. 서비스 생성
**테스트**: `AdminServiceCreateTest`

```php
public function test_admin_can_create_service_returns_200()
{
    // Given: 관리자
    $admin = User::factory()->admin()->create();

    // When: 새 서비스 생성
    $serviceData = [
        'name' => 'Premium Air Conditioning Service',
        'description' => 'Complete AC maintenance and cleaning service',
        'category' => 'maintenance',
        'pricing_model' => 'fixed',
        'base_price' => 150000,
        'features' => ['Deep cleaning', 'Filter replacement', 'Performance check'],
        'trial_enabled' => true,
        'trial_config' => [
            'type' => 'time_based',
            'duration' => 7,
            'discount' => 100
        ],
        'status' => 'active'
    ];

    $response = $this->actingAs($admin)->post('/admin/service/catalog', $serviceData);

    // Then: HTTP 200과 생성된 서비스
    $response->assertStatus(200);
    $response->assertJson(['status' => 'success']);
    $this->assertDatabaseHas('services', [
        'name' => 'Premium Air Conditioning Service',
        'category' => 'maintenance'
    ]);
}

public function test_service_creation_validation_returns_422()
{
    // Given: 관리자
    $admin = User::factory()->admin()->create();

    // When: 잘못된 데이터로 서비스 생성
    $response = $this->actingAs($admin)->post('/admin/service/catalog', [
        'name' => '', // 필수 필드 누락
        'category' => 'invalid_category'
    ]);

    // Then: 422 Validation Error
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'category']);
}
```

#### 3. 서비스 상세 조회
**테스트**: `AdminServiceDetailTest`

```php
public function test_admin_service_detail_returns_200()
{
    // Given: 관리자와 서비스
    $admin = User::factory()->admin()->create();
    $service = Service::factory()->create();
    Subscription::factory()->count(3)->create(['service_id' => $service->id]);

    // When: 서비스 상세 조회
    $response = $this->actingAs($admin)->get("/admin/service/catalog/{$service->id}");

    // Then: HTTP 200과 상세 정보
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'data' => [
            'service' => [
                'id', 'name', 'description', 'category', 'pricing_model',
                'base_price', 'features', 'trial_config', 'status'
            ],
            'statistics' => [
                'subscriptions_count', 'revenue_total', 'average_rating'
            ]
        ]
    ]);
}

public function test_nonexistent_service_returns_404()
{
    // Given: 관리자
    $admin = User::factory()->admin()->create();

    // When: 존재하지 않는 서비스 조회
    $response = $this->actingAs($admin)->get('/admin/service/catalog/999');

    // Then: 404 Not Found
    $response->assertStatus(404);
}
```

### Customer 공개 카탈로그 테스트

#### 4. 공개 서비스 카탈로그
**테스트**: `CustomerServiceCatalogTest`

```php
public function test_public_service_catalog_returns_200()
{
    // Given: 공개된 서비스들
    Service::factory()->count(3)->create(['status' => 'active']);
    Service::factory()->create(['status' => 'draft']); // 비공개

    // When: 공개 카탈로그 조회 (인증 불필요)
    $response = $this->get('/home/service/catalog');

    // Then: HTTP 200과 활성 서비스만 반환
    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data.services');
    $response->assertJsonStructure([
        'data' => [
            'services' => [
                '*' => ['id', 'name', 'category', 'base_price', 'features', 'trial_available']
            ],
            'filters' => ['categories', 'price_range']
        ]
    ]);
}

public function test_customer_service_filtering_returns_200()
{
    // Given: 다양한 카테고리의 서비스
    Service::factory()->create(['category' => 'maintenance', 'base_price' => 100000, 'status' => 'active']);
    Service::factory()->create(['category' => 'cleaning', 'base_price' => 200000, 'status' => 'active']);

    // When: 카테고리 필터 적용
    $response = $this->get('/home/service/catalog?category=maintenance&price_max=150000');

    // Then: HTTP 200과 필터된 결과
    $response->assertStatus(200);
    $response->assertJsonCount(1, 'data.services');
}
```

#### 5. 고객용 서비스 상세
**테스트**: `CustomerServiceDetailTest`

```php
public function test_customer_service_detail_returns_200()
{
    // Given: 활성 서비스
    $service = Service::factory()->create([
        'status' => 'active',
        'trial_config' => json_encode(['type' => 'time_based', 'duration' => 7])
    ]);

    // When: 서비스 상세 조회
    $response = $this->get("/home/service/catalog/{$service->id}");

    // Then: HTTP 200과 고객용 정보
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'service' => [
                'id', 'name', 'description', 'category', 'base_price',
                'features', 'trial_available', 'trial_config'
            ],
            'reviews' => ['average_rating', 'total_reviews'],
            'pricing_options' => ['monthly', 'quarterly', 'yearly']
        ]
    ]);
}

public function test_draft_service_not_accessible_to_customers()
{
    // Given: 비공개 서비스
    $service = Service::factory()->create(['status' => 'draft']);

    // When: 고객이 비공개 서비스 접근
    $response = $this->get("/home/service/catalog/{$service->id}");

    // Then: 404 Not Found
    $response->assertStatus(404);
}
```

## 컨트롤러 구현

### 1. Admin Service Catalog Controller

```php
<?php

namespace Jiny\Service\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Jiny\Service\Models\Service;
use Jiny\Service\Models\ServiceCategory;
use Illuminate\Support\Str;

class ServiceCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with(['category']);

        // 검색 필터
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 카테고리 필터
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        // 상태 필터
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // 정렬
        $query->orderBy($request->get('sort', 'created_at'),
                       $request->get('direction', 'desc'));

        // 페이지네이션
        $services = $query->paginate($request->get('per_page', 20));

        // 통계 계산
        $statistics = $this->calculateStatistics();

        return response()->json([
            'status' => 'success',
            'data' => [
                'services' => $services->items(),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'total_pages' => $services->lastPage(),
                    'total_count' => $services->total(),
                    'per_page' => $services->perPage()
                ],
                'statistics' => $statistics
            ]
        ], 200);
    }

    public function create()
    {
        $categories = ServiceCategory::where('is_active', true)
                                   ->orderBy('sort_order')
                                   ->get();

        $pricingModels = ['fixed', 'hourly', 'subscription'];
        $trialTypes = ['time_based', 'usage_based', 'feature_based', 'hybrid'];

        return response()->json([
            'status' => 'success',
            'data' => [
                'categories' => $categories,
                'pricing_models' => $pricingModels,
                'trial_types' => $trialTypes
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'required|string',
            'category' => 'required|string|exists:service_categories,name',
            'pricing_model' => 'required|in:fixed,hourly,subscription',
            'base_price' => 'required|numeric|min:0',
            'features' => 'array',
            'features.*' => 'string',
            'trial_enabled' => 'boolean',
            'trial_config' => 'array|required_if:trial_enabled,true',
            'status' => 'required|in:active,inactive,draft',
            'image_url' => 'nullable|url',
            'sort_order' => 'integer|min:0'
        ]);

        // 슬러그 생성
        $validated['slug'] = Str::slug($validated['name']);

        // 중복 슬러그 처리
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Service::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter++;
        }

        // JSON 필드 처리
        $validated['features'] = json_encode($validated['features'] ?? []);
        $validated['trial_config'] = $validated['trial_enabled']
            ? json_encode($validated['trial_config'])
            : null;

        $service = Service::create($validated);

        // 활동 로그
        $this->logAdminActivity('service_created', [
            'service_id' => $service->id,
            'service_name' => $service->name
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Service created successfully',
            'data' => [
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'slug' => $service->slug,
                    'status' => $service->status,
                    'created_at' => $service->created_at
                ]
            ]
        ], 200);
    }

    public function show(Service $service)
    {
        // 구독 통계
        $subscriptionStats = [
            'total_subscriptions' => $service->subscriptions()->count(),
            'active_subscriptions' => $service->subscriptions()->where('status', 'active')->count(),
            'trial_subscriptions' => $service->subscriptions()->where('status', 'trial')->count(),
        ];

        // 수익 통계
        $revenueStats = [
            'total_revenue' => $service->subscriptions()
                ->join('subscription_billings', 'subscriptions.id', '=', 'subscription_billings.subscription_id')
                ->where('subscription_billings.status', 'paid')
                ->sum('subscription_billings.total_amount'),
            'monthly_recurring_revenue' => $service->subscriptions()
                ->where('status', 'active')
                ->where('billing_cycle', 'monthly')
                ->sum('amount')
        ];

        // 평점 통계
        $ratingStats = [
            'average_rating' => $service->serviceExecutions()
                ->whereNotNull('customer_rating')
                ->avg('customer_rating'),
            'total_reviews' => $service->serviceExecutions()
                ->whereNotNull('customer_feedback')
                ->count()
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'service' => $service->load('category'),
                'statistics' => [
                    'subscriptions' => $subscriptionStats,
                    'revenue' => $revenueStats,
                    'ratings' => $ratingStats
                ]
            ]
        ], 200);
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::where('is_active', true)
                                   ->orderBy('sort_order')
                                   ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'service' => $service,
                'categories' => $categories,
                'pricing_models' => ['fixed', 'hourly', 'subscription'],
                'trial_types' => ['time_based', 'usage_based', 'feature_based', 'hybrid']
            ]
        ], 200);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'description' => 'required|string',
            'category' => 'required|string|exists:service_categories,name',
            'pricing_model' => 'required|in:fixed,hourly,subscription',
            'base_price' => 'required|numeric|min:0',
            'features' => 'array',
            'features.*' => 'string',
            'trial_enabled' => 'boolean',
            'trial_config' => 'array|required_if:trial_enabled,true',
            'status' => 'required|in:active,inactive,draft',
            'image_url' => 'nullable|url',
            'sort_order' => 'integer|min:0'
        ]);

        // 이름이 변경되면 슬러그 재생성
        if ($service->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);

            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Service::where('slug', $validated['slug'])->where('id', '!=', $service->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }
        }

        // JSON 필드 처리
        $validated['features'] = json_encode($validated['features'] ?? []);
        $validated['trial_config'] = $validated['trial_enabled']
            ? json_encode($validated['trial_config'])
            : null;

        $service->update($validated);

        // 활동 로그
        $this->logAdminActivity('service_updated', [
            'service_id' => $service->id,
            'service_name' => $service->name
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Service updated successfully',
            'data' => ['service' => $service->fresh()]
        ], 200);
    }

    public function destroy(Service $service)
    {
        // 활성 구독 확인
        $activeSubscriptions = $service->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->count();

        if ($activeSubscriptions > 0) {
            return response()->json([
                'error' => 'Cannot delete service with active subscriptions',
                'active_subscriptions' => $activeSubscriptions
            ], 422);
        }

        // 소프트 삭제 또는 하드 삭제
        $hasHistoricalData = $service->subscriptions()->count() > 0;

        if ($hasHistoricalData) {
            // 소프트 삭제 (상태를 inactive로 변경)
            $service->update(['status' => 'inactive']);
            $message = 'Service deactivated due to historical data';
        } else {
            // 하드 삭제
            $service->delete();
            $message = 'Service deleted successfully';
        }

        // 활동 로그
        $this->logAdminActivity('service_deleted', [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'deletion_type' => $hasHistoricalData ? 'soft' : 'hard'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $message
        ], 200);
    }

    public function updateStatus(Request $request, Service $service)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,draft',
            'reason' => 'nullable|string|max:500'
        ]);

        $oldStatus = $service->status;
        $service->update(['status' => $validated['status']]);

        // 활동 로그
        $this->logAdminActivity('service_status_changed', [
            'service_id' => $service->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'reason' => $validated['reason'] ?? null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Service status updated successfully',
            'data' => [
                'service' => $service->fresh()
            ]
        ], 200);
    }

    public function duplicate(Service $service)
    {
        $newService = $service->replicate();
        $newService->name = $service->name . ' (Copy)';
        $newService->slug = Str::slug($newService->name);
        $newService->status = 'draft';

        // 슬러그 중복 처리
        $originalSlug = $newService->slug;
        $counter = 1;
        while (Service::where('slug', $newService->slug)->exists()) {
            $newService->slug = $originalSlug . '-' . $counter++;
        }

        $newService->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Service duplicated successfully',
            'data' => ['service' => $newService]
        ], 200);
    }

    private function calculateStatistics(): array
    {
        return [
            'total_services' => Service::count(),
            'active_services' => Service::where('status', 'active')->count(),
            'draft_services' => Service::where('status', 'draft')->count(),
            'services_with_trials' => Service::whereNotNull('trial_config')->count(),
        ];
    }

    private function logAdminActivity(string $action, array $data): void
    {
        \DB::table('admin_activity_logs')->insert([
            'admin_id' => auth()->id(),
            'action' => $action,
            'data' => json_encode($data),
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);
    }
}
```

### 2. Customer Service Catalog Controller

```php
<?php

namespace Jiny\Service\Http\Controllers\Customer;

use Illuminate\Http\Request;
use Jiny\Service\Models\Service;
use Jiny\Service\Models\ServiceCategory;

class ServiceCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::where('status', 'active')
                       ->with(['category']);

        // 카테고리 필터
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        // 가격 범위 필터
        if ($priceMin = $request->get('price_min')) {
            $query->where('base_price', '>=', $priceMin);
        }
        if ($priceMax = $request->get('price_max')) {
        }

        // 무료 체험 가능 서비스
        if ($request->boolean('trial_available')) {
            $query->whereNotNull('trial_config');
        }

        // 위치 기반 필터 (향후 구현)
        if ($location = $request->get('location')) {
            // 지역 기반 필터링 로직
        }

        // 정렬
        $sortBy = $request->get('sort', 'sort_order');
        $direction = $request->get('direction', 'asc');

        if ($sortBy === 'popularity') {
            // 구독 수 기준 정렬
            $query->withCount('subscriptions')
                  ->orderBy('subscriptions_count', 'desc');
        } elseif ($sortBy === 'rating') {
            // 평점 기준 정렬
            $query->leftJoin('service_executions', 'services.id', '=', 'service_executions.subscription_id')
                  ->select('services.*')
                  ->selectRaw('AVG(service_executions.customer_rating) as avg_rating')
                  ->groupBy('services.id')
                  ->orderBy('avg_rating', 'desc');
        } else {
            $query->orderBy($sortBy, $direction);
        }

        $services = $query->paginate($request->get('per_page', 12));

        // 서비스 데이터 변환 (고객용)
        $transformedServices = $services->getCollection()->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'category' => $service->category,
                'base_price' => $service->base_price,
                'features' => json_decode($service->features, true),
                'trial_available' => !is_null($service->trial_config),
                'trial_config' => $service->trial_config ? json_decode($service->trial_config, true) : null,
                'image_url' => $service->image_url,
                'pricing_options' => $this->generatePricingOptions($service),
                'rating' => $this->getServiceRating($service->id),
                'reviews_count' => $this->getReviewsCount($service->id)
            ];
        });

        // 필터 옵션
        $filters = [
            'categories' => ServiceCategory::where('is_active', true)
                                          ->orderBy('sort_order')
                                          ->pluck('name'),
            'price_range' => [
                'min' => Service::where('status', 'active')->min('base_price'),
                'max' => Service::where('status', 'active')->max('base_price')
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'services' => $transformedServices,
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'total_pages' => $services->lastPage(),
                    'total_count' => $services->total()
                ],
                'filters' => $filters
            ]
        ], 200);
    }

    public function show(Service $service)
    {
        // 활성 서비스만 조회 가능
        if ($service->status !== 'active') {
            return response()->json(['error' => 'Service not found'], 404);
        }

        // 서비스 상세 정보
        $serviceData = [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'category' => $service->category,
            'pricing_model' => $service->pricing_model,
            'base_price' => $service->base_price,
            'features' => json_decode($service->features, true),
            'trial_available' => !is_null($service->trial_config),
            'trial_config' => $service->trial_config ? json_decode($service->trial_config, true) : null,
            'image_url' => $service->image_url
        ];

        // 리뷰 및 평점
        $reviews = [
            'average_rating' => $this->getServiceRating($service->id),
            'total_reviews' => $this->getReviewsCount($service->id),
            'rating_distribution' => $this->getRatingDistribution($service->id),
            'recent_reviews' => $this->getRecentReviews($service->id, 5)
        ];

        // 가격 옵션
        $pricingOptions = $this->generatePricingOptions($service);

        // 비슷한 서비스
        $similarServices = Service::where('category', $service->category)
                                 ->where('id', '!=', $service->id)
                                 ->where('status', 'active')
                                 ->limit(4)
                                 ->get(['id', 'name', 'base_price', 'image_url']);

        // 조회수 증가 (비동기로 처리)
        $this->incrementViewCount($service->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'service' => $serviceData,
                'reviews' => $reviews,
                'pricing_options' => $pricingOptions,
                'similar_services' => $similarServices
            ]
        ], 200);
    }

    public function trialInfo(Service $service)
    {
        if (!$service->trial_config) {
            return response()->json(['error' => 'Trial not available for this service'], 404);
        }

        $trialConfig = json_decode($service->trial_config, true);

        // 고객별 개인화 체험 설정 (JWT 토큰에서 고객 정보 추출)
        $customer = $request->input('authenticated_customer');
        if ($customer) {
            $trialConfig = $this->personalizeTrialConfig($trialConfig, $customer);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'trial_config' => $trialConfig,
                'terms_and_conditions' => $this->getTrialTerms(),
                'estimated_value' => $this->calculateTrialValue($service, $trialConfig)
            ]
        ], 200);
    }

    private function generatePricingOptions(Service $service): array
    {
        $basePrice = $service->base_price;

        return [
            'monthly' => [
                'price' => $basePrice,
                'savings' => 0,
                'total' => $basePrice
            ],
            'quarterly' => [
                'price' => $basePrice * 3 * 0.95, // 5% 할인
                'savings' => $basePrice * 3 * 0.05,
                'total' => $basePrice * 3 * 0.95
            ],
            'yearly' => [
                'price' => $basePrice * 12 * 0.85, // 15% 할인
                'savings' => $basePrice * 12 * 0.15,
                'total' => $basePrice * 12 * 0.85
            ]
        ];
    }

    private function getServiceRating(int $serviceId): float
    {
        return \DB::table('service_executions')
                  ->join('subscriptions', 'service_executions.subscription_id', '=', 'subscriptions.id')
                  ->where('subscriptions.service_id', $serviceId)
                  ->whereNotNull('service_executions.customer_rating')
                  ->avg('service_executions.customer_rating') ?? 0;
    }

    private function getReviewsCount(int $serviceId): int
    {
        return \DB::table('service_executions')
                  ->join('subscriptions', 'service_executions.subscription_id', '=', 'subscriptions.id')
                  ->where('subscriptions.service_id', $serviceId)
                  ->whereNotNull('service_executions.customer_feedback')
                  ->count();
    }

    private function getRatingDistribution(int $serviceId): array
    {
        $ratings = \DB::table('service_executions')
                     ->join('subscriptions', 'service_executions.subscription_id', '=', 'subscriptions.id')
                     ->where('subscriptions.service_id', $serviceId)
                     ->whereNotNull('service_executions.customer_rating')
                     ->select('customer_rating', \DB::raw('count(*) as count'))
                     ->groupBy('customer_rating')
                     ->pluck('count', 'customer_rating')
                     ->toArray();

        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $ratings[$i] ?? 0;
        }

        return $distribution;
    }

    private function getRecentReviews(int $serviceId, int $limit = 5): array
    {
        return \DB::table('service_executions')
                  ->join('subscriptions', 'service_executions.subscription_id', '=', 'subscriptions.id')
                  ->where('subscriptions.service_id', $serviceId)
                  ->whereNotNull('service_executions.customer_feedback')
                  ->whereNotNull('service_executions.customer_rating')
                  ->select([
                      'service_executions.customer_rating',
                      'service_executions.customer_feedback',
                      'service_executions.completed_at'
                  ])
                  ->orderBy('service_executions.completed_at', 'desc')
                  ->limit($limit)
                  ->get()
                  ->toArray();
    }

    private function incrementViewCount(int $serviceId): void
    {
        // 비동기 작업으로 조회수 증가
        \DB::table('service_view_logs')->insert([
            'service_id' => $serviceId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'viewed_at' => now()
        ]);
    }

    private function personalizeTrialConfig(array $config, object $customer): array
    {
        // 고객의 이전 서비스 이용 이력에 따른 개인화
        // 이는 향후 AI/ML 기반 개인화로 확장 가능
        return $config;
    }

    private function getTrialTerms(): array
    {
        return [
            'duration_limit' => 'Trial is limited to the specified duration only',
            'auto_conversion' => 'Trial will automatically convert to paid subscription unless cancelled',
            'cancellation_policy' => 'You can cancel anytime during the trial period',
            'refund_policy' => 'Full refund available if cancelled within trial period'
        ];
    }

    private function calculateTrialValue(Service $service, array $trialConfig): array
    {
        $basePrice = $service->base_price;
        $trialDuration = $trialConfig['duration'] ?? 7;

        return [
            'trial_value' => $basePrice * ($trialDuration / 30), // 일할 계산
            'full_service_value' => $basePrice,
            'savings' => $basePrice * ($trialDuration / 30)
        ];
    }
}
```

## 모델 구현

### Service Model
```php
<?php

namespace Jiny\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'category', 'pricing_model',
        'base_price', 'features', 'trial_config', 'status',
        'image_url', 'sort_order'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'features' => 'array',
        'trial_config' => 'array',
        'sort_order' => 'integer'
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category', 'name');
    }

    public function serviceExecutions()
    {
        return $this->hasManyThrough(
            ServiceExecution::class,
            Subscription::class,
            'service_id',
            'subscription_id'
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithTrials($query)
    {
        return $query->whereNotNull('trial_config');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getFeaturesListAttribute()
    {
        return $this->features ?? [];
    }

    public function getTrialConfigDataAttribute()
    {
        return $this->trial_config ?? [];
    }

    public function getHasTrialAttribute()
    {
        return !is_null($this->trial_config);
    }

    // Helper Methods
    public function getActiveSubscriptionsCount(): int
    {
        return $this->subscriptions()->where('status', 'active')->count();
    }

    public function getTotalRevenue(): float
    {
        return $this->subscriptions()
                   ->join('subscription_billings', 'subscriptions.id', '=', 'subscription_billings.subscription_id')
                   ->where('subscription_billings.status', 'paid')
                   ->sum('subscription_billings.total_amount');
    }

    public function getAverageRating(): float
    {
        return $this->serviceExecutions()
                   ->whereNotNull('customer_rating')
                   ->avg('customer_rating') ?? 0;
    }
}
```

## 구현 체크리스트

### Admin 서비스 관리
- [ ] **서비스 목록 조회** (`GET /admin/service/catalog`)
  - [ ] HTTP 200 응답 검증
  - [ ] 검색/필터 기능
  - [ ] 페이지네이션
  - [ ] 정렬 기능
  - [ ] 통계 데이터 포함

- [ ] **서비스 생성** (`POST /admin/service/catalog`)
  - [ ] HTTP 200 응답 검증
  - [ ] 입력 검증 (422 오류)
  - [ ] 슬러그 자동 생성
  - [ ] 중복 이름 방지
  - [ ] JSON 필드 처리

- [ ] **서비스 상세 조회** (`GET /admin/service/catalog/{id}`)
  - [ ] HTTP 200 응답 검증
  - [ ] 404 오류 처리
  - [ ] 구독 통계 계산
  - [ ] 수익 통계 계산
  - [ ] 평점 통계 계산

- [ ] **서비스 수정** (`PUT /admin/service/catalog/{id}`)
  - [ ] HTTP 200 응답 검증
  - [ ] 검증 로직
  - [ ] 슬러그 업데이트
  - [ ] 변경 이력 로그

- [ ] **서비스 삭제** (`DELETE /admin/service/catalog/{id}`)
  - [ ] HTTP 200 응답 검증
  - [ ] 활성 구독 확인
  - [ ] 소프트/하드 삭제 로직
  - [ ] 삭제 이력 로그

### Customer 공개 카탈로그
- [ ] **공개 카탈로그 조회** (`GET /home/service/catalog`)
  - [ ] HTTP 200 응답 검증
  - [ ] 활성 서비스만 표시
  - [ ] 카테고리/가격 필터
  - [ ] 정렬 옵션
  - [ ] 필터 메타데이터

- [ ] **서비스 상세 조회** (`GET /home/service/catalog/{id}`)
  - [ ] HTTP 200 응답 검증
  - [ ] 비활성 서비스 404 처리
  - [ ] 리뷰/평점 표시
  - [ ] 가격 옵션 계산
  - [ ] 비슷한 서비스 추천

- [ ] **체험 정보 조회** (`GET /home/service/catalog/{id}/trial-info`)
  - [ ] HTTP 200 응답 검증
  - [ ] 체험 불가능한 서비스 404 처리
  - [ ] 개인화된 체험 설정
  - [ ] 체험 가치 계산

### 데이터 모델
- [ ] **Service 모델**
  - [ ] 관계 설정 (Subscription, ServiceCategory)
  - [ ] 스코프 메서드
  - [ ] 접근자 메서드
  - [ ] 헬퍼 메서드

- [ ] **ServiceCategory 모델**
  - [ ] 기본 카테고리 시딩
  - [ ] 정렬 순서 관리

## 완료 기준

### 기능적 검증
- [ ] 모든 엔드포인트 HTTP 200 반환
- [ ] 관리자 CRUD 작업 정상 동작
- [ ] 고객 카탈로그 조회 정상 동작
- [ ] 검색/필터/정렬 기능 작동
- [ ] 입력 검증 및 오류 처리 완료

### 성능 검증
- [ ] 카탈로그 조회 < 200ms
- [ ] 검색 기능 < 300ms
- [ ] 대량 데이터 페이지네이션 최적화
- [ ] 데이터베이스 쿼리 최적화

---

**이전 태스크**: [003. 인증 시스템](003_authentication_system.md)
**다음 태스크**: [005. 구독 관리 시스템](005_subscription_management.md)