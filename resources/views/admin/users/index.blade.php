@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">서비스 구독 사용자 관리</h1>
            <p class="mb-0 text-muted">서비스를 구독 중인 사용자들을 관리합니다.</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.service.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> 새 구독 사용자 추가
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">전체 구독자</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">활성 구독자</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">만료 임박</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['expiring_soon']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">월 매출</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₩{{ number_format($stats['monthly_revenue']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-won-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.service.users.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="service_id" class="form-label">서비스</label>
                        <select name="service_id" id="service_id" class="form-control">
                            <option value="">전체 서비스</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}"
                                    {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="plan_name" class="form-label">플랜</label>
                        <select name="plan_name" id="plan_name" class="form-control">
                            <option value="">전체 플랜</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->plan_name }}"
                                    {{ request('plan_name') === $plan->plan_name ? 'selected' : '' }}>
                                    {{ $plan->plan_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">상태</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">전체 상태</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>활성</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>만료</option>
                            <option value="expiring_soon" {{ request('status') === 'expiring_soon' ? 'selected' : '' }}>만료 임박</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>취소</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>일시중지</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="billing_cycle" class="form-label">결제 주기</label>
                        <select name="billing_cycle" id="billing_cycle" class="form-control">
                            <option value="">전체 주기</option>
                            <option value="monthly" {{ request('billing_cycle') === 'monthly' ? 'selected' : '' }}>월간</option>
                            <option value="quarterly" {{ request('billing_cycle') === 'quarterly' ? 'selected' : '' }}>분기</option>
                            <option value="yearly" {{ request('billing_cycle') === 'yearly' ? 'selected' : '' }}>연간</option>
                            <option value="lifetime" {{ request('billing_cycle') === 'lifetime' ? 'selected' : '' }}>평생</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9 mb-3">
                        <label for="search" class="form-label">검색</label>
                        <input type="text" name="search" id="search" class="form-control"
                               placeholder="사용자 UUID, 이메일, 이름 검색" value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary mr-2">
                            <i class="fas fa-search"></i> 검색
                        </button>
                        <a href="{{ route('admin.service.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i> 초기화
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">구독 사용자 목록</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>사용자 정보</th>
                            <th>서비스/플랜</th>
                            <th>상태</th>
                            <th>결제 정보</th>
                            <th>구독 기간</th>
                            <th>결제 총액</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $user->user_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $user->user_email }}</small>
                                    <br>
                                    <small class="text-muted">UUID: {{ Str::limit($user->user_uuid, 12) }}</small>
                                    @if($user->user_shard)
                                        <br><small class="badge badge-secondary">{{ $user->user_shard }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $user->service->name ?? $user->service_title }}</strong>
                                    <br>
                                    <span class="badge badge-{{
                                        strpos($user->plan_name, 'Basic') !== false ? 'secondary' :
                                        (strpos($user->plan_name, 'Standard') !== false ? 'primary' :
                                        (strpos($user->plan_name, 'Premium') !== false ? 'success' : 'info'))
                                    }}">
                                        {{ $user->plan_name }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ ucfirst($user->billing_cycle) }}
                                        @if($user->auto_renewal)
                                            <i class="fas fa-sync-alt text-success" title="자동 갱신"></i>
                                        @endif
                                    </small>
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($user->status) {
                                        'active' => 'success',
                                        'expired' => 'danger',
                                        'cancelled' => 'secondary',
                                        'suspended' => 'warning',
                                        default => 'info'
                                    };
                                @endphp
                                <span class="badge badge-{{ $statusClass }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                                @if($user->is_expiring_soon)
                                    <br><small class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 만료 임박
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>₩{{ number_format($user->plan_price) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $user->payment_method ?? 'N/A' }}</small>
                                    @php
                                        $paymentStatusClass = match($user->payment_status) {
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <br>
                                    <span class="badge badge-{{ $paymentStatusClass }}">
                                        {{ ucfirst($user->payment_status ?? 'unknown') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <small class="text-muted">시작: {{ $user->started_at?->format('Y-m-d') }}</small>
                                    <br>
                                    <strong>만료: {{ $user->expires_at?->format('Y-m-d') }}</strong>
                                    @if($user->expires_at)
                                        <br>
                                        @if($user->days_until_expiry > 0)
                                            <small class="text-info">{{ $user->days_until_expiry }}일 남음</small>
                                        @elseif($user->days_until_expiry < 0)
                                            <small class="text-danger">{{ abs($user->days_until_expiry) }}일 경과</small>
                                        @else
                                            <small class="text-warning">오늘 만료</small>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong>₩{{ number_format($user->total_paid) }}</strong>
                                    @if($user->refund_amount > 0)
                                        <br>
                                        <small class="text-danger">
                                            환불: ₩{{ number_format($user->refund_amount) }}
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="btn-group-vertical btn-group-sm" role="group">
                                    <a href="{{ route('admin.service.users.edit', $user->id) }}"
                                       class="btn btn-outline-primary btn-sm" title="상세 보기/수정">
                                        <i class="fas fa-edit"></i> 관리
                                    </a>
                                    @if($user->status === 'active')
                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                                onclick="extendSubscription({{ $user->id }})" title="기간 연장">
                                            <i class="fas fa-calendar-plus"></i> 연장
                                        </button>
                                    @endif
                                    @if($user->status === 'cancelled')
                                        <button type="button" class="btn btn-outline-success btn-sm"
                                                onclick="reactivateSubscription({{ $user->id }})" title="재활성화">
                                            <i class="fas fa-play"></i> 활성화
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">등록된 구독 사용자가 없습니다.</p>
                                <a href="{{ route('admin.service.users.create') }}" class="btn btn-primary">
                                    첫 번째 구독 사용자 추가
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Activities -->
    @if($recentActivities->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">최근 활동</h6>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">
                @foreach($recentActivities as $activity)
                <div class="list-group-item border-0 px-0">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="font-weight-bold text-gray-800">
                                {{ $activity->user_name }} ({{ $activity->user_email }})
                            </div>
                            <div class="text-xs text-gray-600">
                                {{ $activity->service->name ?? $activity->service_title }} - {{ $activity->plan_name }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="text-xs text-gray-500">
                                {{ $activity->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

<!-- Modals and Scripts -->
@push('scripts')
<script>
function extendSubscription(userId) {
    // You can implement a modal for extension or redirect to a page
    window.location.href = `/admin/service/users/${userId}/edit#extend`;
}

function reactivateSubscription(userId) {
    if (confirm('이 구독을 재활성화하시겠습니까?')) {
        // Implement reactivation logic
        fetch(`/admin/service/process/cancel/${userId}/reactivate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reactivate_reason: '관리자에 의한 재활성화'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('구독이 재활성화되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + data.message);
            }
        })
        .catch(error => {
            alert('오류가 발생했습니다.');
            console.error(error);
        });
    }
}
</script>
@endpush
@endsection