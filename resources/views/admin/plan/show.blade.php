@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $plan->plan_name }} 플랜</h2>
                    <p class="text-muted mb-0">플랜의 상세 정보와 구독 현황을 확인합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.service.plan.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fe fe-settings me-2"></i>관리
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.service.plan.detail.index', $plan->id) }}">
                                    <i class="fe fe-list me-2"></i>상세 기능 관리
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.service.plan.price.index', $plan->id) }}">
                                    <i class="fe fe-dollar-sign me-2"></i>가격 옵션 관리
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('admin.service.plan.edit', $plan->id) }}" class="btn btn-primary">
                        <i class="fe fe-edit me-2"></i>수정하기
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-users text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">총 구독자</h6>
                            <h4 class="mb-0">{{ $subscribersCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-user-check text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">활성 구독자</h6>
                            <h4 class="mb-0">{{ $activeSubscribers }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-user-plus text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">이번 달 신규</h6>
                            <h4 class="mb-0">{{ $newSubscribersThisMonth }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">이번 달 수익</h6>
                            <h4 class="mb-0">₩{{ number_format($thisMonthRevenue) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 기본 정보 -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">기본 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">플랜명</label>
                                <p class="text-gray-800">{{ $plan->plan_name }}</p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">플랜 코드</label>
                                <p class="text-gray-800">{{ $plan->plan_code }}</p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">서비스</label>
                                <p class="text-gray-800">{{ $plan->service->title ?? '-' }}</p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">플랜 타입</label>
                                <p>
                                    <span class="badge bg-{{
                                        $plan->plan_type === 'basic' ? 'secondary' :
                                        ($plan->plan_type === 'standard' ? 'primary' :
                                        ($plan->plan_type === 'premium' ? 'success' :
                                        ($plan->plan_type === 'enterprise' ? 'dark' : 'info')))
                                    }}">
                                        {{ ucfirst($plan->plan_type) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">결제 타입</label>
                                <p class="text-gray-800">{{ ucfirst($plan->billing_type) }}</p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">상태</label>
                                <p>
                                    @if($plan->is_active)
                                        <span class="badge bg-success">활성</span>
                                    @else
                                        <span class="badge bg-secondary">비활성</span>
                                    @endif
                                    @if($plan->is_featured)
                                        <span class="badge bg-warning text-dark ms-1">추천</span>
                                    @endif
                                    @if($plan->is_popular)
                                        <span class="badge bg-info ms-1">인기</span>
                                    @endif
                                </p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">정렬 순서</label>
                                <p class="text-gray-800">{{ $plan->sort_order }}</p>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">무료 체험</label>
                                <p>
                                    @if($plan->allow_trial && $plan->trial_period_days > 0)
                                        <span class="badge bg-success">{{ $plan->trial_period_days }}일 제공</span>
                                    @else
                                        <span class="badge bg-secondary">제공 안함</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($plan->description)
                    <div class="form-group">
                        <label class="font-weight-bold">설명</label>
                        <div class="text-gray-800">{{ $plan->description }}</div>
                    </div>
                    @endif
                </div>
            </div>


            <!-- 기능 -->
            @if($plan->features && count($plan->features) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">기능</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($plan->features as $feature)
                        <div class="col-md-6 mb-2">
                            <i class="fe fe-check text-success me-2"></i>{{ $feature }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- 플랜 상세 정보 -->
            @if(isset($planDetails) && $planDetails->count() > 0)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">플랜 상세 기능</h5>
                    <a href="{{ route('admin.service.plan.detail.index', $plan->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fe fe-edit me-1"></i>관리
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>타입</th>
                                    <th>제목</th>
                                    <th>값</th>
                                    <th>카테고리</th>
                                    <th>상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($planDetails as $detail)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $detail->typeDisplay }}</span>
                                        @if($detail->is_highlighted)
                                            <span class="badge bg-warning text-dark ms-1">강조</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($detail->icon)
                                                <i class="{{ $detail->icon_class }} {{ $detail->color_class }} me-2"></i>
                                            @endif
                                            <div>
                                                <strong>{{ $detail->title }}</strong>
                                                @if($detail->description)
                                                    <div class="small text-muted">{{ Str::limit($detail->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $detail->formatted_value }}</span>
                                        @if($detail->tooltip)
                                            <i class="fe fe-help-circle text-muted ms-1" title="{{ $detail->tooltip }}"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($detail->category)
                                            <span class="badge bg-secondary">{{ $detail->category_display }}</span>
                                        @endif
                                        @if($detail->group_name)
                                            <div class="small text-muted mt-1">{{ $detail->group_name }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $detail->enable ? 'success' : 'secondary' }}">
                                            {{ $detail->enable ? '활성' : '비활성' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- 플랜 가격 옵션 -->
            @if(isset($planPrices) && $planPrices->count() > 0)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">가격 옵션</h5>
                    <a href="{{ route('admin.service.plan.price.index', $plan->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fe fe-dollar-sign me-1"></i>관리
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>가격명</th>
                                    <th>기간</th>
                                    <th>가격</th>
                                    <th>수량</th>
                                    <th>상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($planPrices as $price)
                                <tr>
                                    <td>
                                        <strong>{{ $price->title }}</strong>
                                        @if($price->is_featured)
                                            <span class="badge bg-warning text-dark ms-1">추천</span>
                                        @endif
                                        @if($price->description)
                                            <div class="small text-muted">{{ Str::limit($price->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $price->billing_period_display }}</span>
                                    </td>
                                    <td>
                                        @if($price->is_free)
                                            <span class="text-success fw-bold">무료</span>
                                        @else
                                            <strong>₩{{ number_format($price->price) }}</strong>
                                            @if($price->original_price && $price->original_price > $price->price)
                                                <div class="small">
                                                    <del class="text-muted">₩{{ number_format($price->original_price) }}</del>
                                                    <span class="text-success">{{ $price->discount_percentage }}% 할인</span>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($price->min_quantity || $price->max_quantity)
                                            <div class="small text-muted">
                                                @if($price->min_quantity)최소: {{ $price->min_quantity }}@endif
                                                @if($price->min_quantity && $price->max_quantity) / @endif
                                                @if($price->max_quantity)최대: {{ $price->max_quantity }}@endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $price->enable ? 'success' : 'secondary' }}">
                                            {{ $price->enable ? '활성' : '비활성' }}
                                        </span>
                                        @if($price->auto_renewal)
                                            <div class="small text-info">자동 갱신</div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- 제한사항 및 할당량 -->
            @if($plan->max_users || $plan->max_projects || $plan->storage_limit_gb || $plan->api_calls_per_month)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">제한사항 및 할당량</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if($plan->max_users)
                            <div class="form-group">
                                <label class="font-weight-bold">최대 사용자</label>
                                <p class="text-gray-800">{{ number_format($plan->max_users) }}명</p>
                            </div>
                            @endif
                            @if($plan->max_projects)
                            <div class="form-group">
                                <label class="font-weight-bold">최대 프로젝트</label>
                                <p class="text-gray-800">{{ number_format($plan->max_projects) }}개</p>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($plan->storage_limit_gb)
                            <div class="form-group">
                                <label class="font-weight-bold">스토리지 한도</label>
                                <p class="text-gray-800">{{ number_format($plan->storage_limit_gb) }}GB</p>
                            </div>
                            @endif
                            @if($plan->api_calls_per_month)
                            <div class="form-group">
                                <label class="font-weight-bold">월 API 호출</label>
                                <p class="text-gray-800">{{ number_format($plan->api_calls_per_month) }}회</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- 사이드바 -->
        <div class="col-lg-4">
            <!-- 플랜 변경 경로 -->
            @if($upgradePlans->count() > 0 || $downgradePlans->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">플랜 변경 경로</h5>
                </div>
                <div class="card-body">
                    @if($upgradePlans->count() > 0)
                    <div class="mb-3">
                        <label class="font-weight-bold text-success">업그레이드 가능</label>
                        @foreach($upgradePlans as $upgradePlan)
                        <div class="mb-1">
                            <small class="text-muted">→</small> {{ $upgradePlan->plan_name }}
                            <small class="text-muted">(₩{{ number_format($upgradePlan->monthly_price) }}/월)</small>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($downgradePlans->count() > 0)
                    <div>
                        <label class="font-weight-bold text-warning">다운그레이드 가능</label>
                        @foreach($downgradePlans as $downgradePlan)
                        <div class="mb-1">
                            <small class="text-muted">→</small> {{ $downgradePlan->plan_name }}
                            <small class="text-muted">(₩{{ number_format($downgradePlan->monthly_price) }}/월)</small>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- 최근 구독자 -->
            @if($recentSubscribers->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">최근 구독자</h5>
                </div>
                <div class="card-body">
                    @foreach($recentSubscribers as $subscriber)
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-3">
                            <div class="icon-circle bg-primary">
                                <i class="fe fe-user text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small font-weight-bold">{{ $subscriber->user->name ?? '사용자' }}</div>
                            <div class="small text-muted">{{ $subscriber->created_at->format('Y-m-d') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- 월별 구독자 추이 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">월별 구독자 추이</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" width="100" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* 통계 카드 원형 아이콘 스타일 */
.stat-circle {
    width: 48px !important;
    height: 48px !important;
    min-width: 48px;
    min-height: 48px;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
}

.stat-circle i {
    font-size: 20px;
}

/* 아이콘 원형 스타일 */
.icon-circle {
    height: 2rem;
    width: 2rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyStats = @json($monthlyStats);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyStats.map(stat => stat.month_name),
            datasets: [{
                label: '신규 구독자',
                data: monthlyStats.map(stat => stat.count),
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush