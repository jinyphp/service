@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">서비스 구독자 관리</h2>
                    <p class="text-muted mb-0">서비스를 구독하고 있는 사용자들을 관리합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.service.users.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>새 구독자 추가
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-users text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">전체 구독자</h6>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">활성 구독자</h6>
                            <h4 class="mb-0">{{ $stats['active'] }}</h4>
                            <small class="text-success">{{ $stats['active_rate'] }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-clock text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">곧 만료</h6>
                            <h4 class="mb-0">{{ $stats['expiring_soon'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-x-circle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">만료됨</h6>
                            <h4 class="mb-0">{{ $stats['expired'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-user-plus text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">이번 달</h6>
                            <h4 class="mb-0">{{ $stats['this_month'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-dark bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">월 수익</h6>
                            <h4 class="mb-0">₩{{ number_format($stats['total_revenue']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.service.users.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="search">검색</label>
                            <input type="text" id="search" name="search" class="form-control"
                                   placeholder="이메일, 이름, UUID 검색..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">상태</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">전체</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="service_id">서비스</label>
                            <select id="service_id" name="service_id" class="form-control">
                                <option value="">전체</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                        {{ $service->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="billing_cycle">결제 주기</label>
                            <select id="billing_cycle" name="billing_cycle" class="form-control">
                                <option value="">전체</option>
                                @foreach($billingCycleOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('billing_cycle') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="payment_status">결제 상태</label>
                            <select id="payment_status" name="payment_status" class="form-control">
                                <option value="">전체</option>
                                @foreach($paymentStatusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('payment_status') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fe fe-search me-1"></i>검색
                        </button>
                        <a href="{{ route('admin.service.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-refresh-cw me-1"></i>초기화
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 구독자 목록 -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">구독자 목록</h5>
        </div>
        <div class="card-body p-0">
            @if($serviceUsers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>사용자</th>
                                <th>서비스</th>
                                <th>플랜</th>
                                <th>상태</th>
                                <th>결제 주기</th>
                                <th>만료일</th>
                                <th>월 결제</th>
                                <th width="120">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceUsers as $serviceUser)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="icon-circle bg-primary">
                                                <i class="fe fe-user text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $serviceUser->user_name ?? '이름 없음' }}</strong>
                                            <div class="small text-muted">{{ $serviceUser->user_email }}</div>
                                            <div class="small text-muted">Shard: {{ $serviceUser->user_shard }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $serviceUser->service->title ?? $serviceUser->service_title }}</strong>
                                </td>
                                <td>
                                    @if($serviceUser->plan_name)
                                        <strong>{{ $serviceUser->plan_name }}</strong>
                                        @if($serviceUser->plan_price > 0)
                                            <div class="small text-muted">₩{{ number_format($serviceUser->plan_price) }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{
                                        $serviceUser->status === 'active' ? 'success' :
                                        ($serviceUser->status === 'pending' ? 'warning' :
                                        ($serviceUser->status === 'suspended' ? 'info' :
                                        ($serviceUser->status === 'cancelled' ? 'secondary' : 'danger')))
                                    }}">
                                        {{
                                            $serviceUser->status === 'active' ? '활성' :
                                            ($serviceUser->status === 'pending' ? '대기' :
                                            ($serviceUser->status === 'suspended' ? '일시정지' :
                                            ($serviceUser->status === 'cancelled' ? '취소' : '만료')))
                                        }}
                                    </span>
                                    @if($serviceUser->is_expiring_soon && $serviceUser->status === 'active')
                                        <div class="small text-warning">곧 만료</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{
                                            $serviceUser->billing_cycle === 'monthly' ? '월간' :
                                            ($serviceUser->billing_cycle === 'quarterly' ? '분기' :
                                            ($serviceUser->billing_cycle === 'yearly' ? '연간' : '평생'))
                                        }}
                                    </span>
                                </td>
                                <td>
                                    @if($serviceUser->expires_at)
                                        <span class="{{ $serviceUser->is_expired ? 'text-danger' : ($serviceUser->is_expiring_soon ? 'text-warning' : '') }}">
                                            {{ $serviceUser->expires_at->format('Y-m-d') }}
                                        </span>
                                        <div class="small text-muted">
                                            {{ $serviceUser->days_until_expiry }}일 {{ $serviceUser->days_until_expiry >= 0 ? '남음' : '지남' }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($serviceUser->monthly_price > 0)
                                        <strong>₩{{ number_format($serviceUser->monthly_price) }}</strong>
                                        <div class="small">
                                            <span class="badge bg-{{ $serviceUser->payment_status === 'paid' ? 'success' : ($serviceUser->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ $paymentStatusOptions[$serviceUser->payment_status] ?? $serviceUser->payment_status }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-success">무료</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.service.users.show', $serviceUser->id) }}"
                                           class="btn btn-outline-info btn-square" title="상세보기">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.service.users.edit', $serviceUser->id) }}"
                                           class="btn btn-outline-primary btn-square" title="수정">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        @if($serviceUser->status !== 'active')
                                        <button type="button" class="btn btn-outline-danger btn-square"
                                                onclick="deleteServiceUser({{ $serviceUser->id }})" title="삭제">
                                            <i class="fe fe-trash-2"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            전체 {{ $serviceUsers->total() }}개 중
                            {{ $serviceUsers->firstItem() }}~{{ $serviceUsers->lastItem() }}개 표시
                        </div>
                        <div>
                            {{ $serviceUsers->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fe fe-users fe-3x text-muted mb-3"></i>
                    <h5 class="text-muted">등록된 구독자가 없습니다</h5>
                    <p class="text-muted">새로운 서비스 구독자를 추가해보세요.</p>
                    <a href="{{ route('admin.service.users.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>첫 번째 구독자 추가
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">구독자 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>이 구독자를 삭제하시겠습니까?</p>
                <p class="text-danger small">
                    <i class="fe fe-alert-triangle me-1"></i>
                    삭제된 데이터는 복구할 수 없습니다.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">삭제</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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

.icon-circle {
    height: 2rem;
    width: 2rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-square {
    width: 32px !important;
    height: 32px !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 4px !important;
}

.btn-square i {
    font-size: 14px;
}
</style>
@endpush

@push('scripts')
<script>
function deleteServiceUser(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `{{ route('admin.service.users.index') }}/${id}`;
    modal.show();
}
</script>
@endpush