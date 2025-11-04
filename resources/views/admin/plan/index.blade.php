@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">서비스 플랜 관리</h2>
                    <p class="text-muted mb-0">서비스별 요금제 플랜을 관리합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.service.plan.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>새 플랜 추가
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
                                <i class="fe fe-package text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">전체 플랜</h6>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
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
                                <i class="fe fe-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">활성 플랜</h6>
                            <h4 class="mb-0">{{ $stats['active'] }}</h4>
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
                                <i class="fe fe-star text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">추천 플랜</h6>
                            <h4 class="mb-0">{{ $stats['featured'] }}</h4>
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
                                <i class="fe fe-gift text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">무료체험 제공</h6>
                            <h4 class="mb-0">{{ $stats['with_trial'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.service.plan.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">검색</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   class="form-control"
                                   placeholder="플랜명, 코드 검색..."
                                   value="{{ request('search') }}">
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
                            <label for="plan_type">플랜 타입</label>
                            <select id="plan_type" name="plan_type" class="form-control">
                                <option value="">전체</option>
                                <option value="basic" {{ request('plan_type') === 'basic' ? 'selected' : '' }}>Basic</option>
                                <option value="standard" {{ request('plan_type') === 'standard' ? 'selected' : '' }}>Standard</option>
                                <option value="premium" {{ request('plan_type') === 'premium' ? 'selected' : '' }}>Premium</option>
                                <option value="enterprise" {{ request('plan_type') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                <option value="custom" {{ request('plan_type') === 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">상태</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">전체</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>활성</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>비활성</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fe fe-search me-1"></i>검색
                        </button>
                        <a href="{{ route('admin.service.plan.index') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-refresh-cw me-1"></i>초기화
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 서비스 플랜 목록 -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">서비스 플랜 목록</h5>
        </div>
        <div class="card-body p-0">
            @if($plans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="80">정렬순서</th>
                                <th>플랜명</th>
                                <th>서비스</th>
                                <th>타입</th>
                                <th>상태</th>
                                <th>구독자</th>
                                <th width="140">관리</th>
                            </tr>
                        </thead>
                    <tbody>
                        @foreach($plans as $plan)
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $plan->sort_order }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($plan->icon)
                                        <i class="{{ $plan->icon }} text-muted me-2"></i>
                                    @else
                                        <i class="fe fe-package text-muted me-2"></i>
                                    @endif
                                    <div>
                                        <strong>{{ $plan->plan_name }}</strong>
                                        @if($plan->is_featured)
                                            <span class="badge bg-warning text-dark ms-1">추천</span>
                                        @endif
                                        @if($plan->is_popular)
                                            <span class="badge bg-success ms-1">인기</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $plan->plan_code }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $plan->service->title ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{
                                    $plan->plan_type === 'basic' ? 'secondary' :
                                    ($plan->plan_type === 'standard' ? 'primary' :
                                    ($plan->plan_type === 'premium' ? 'success' :
                                    ($plan->plan_type === 'enterprise' ? 'dark' : 'info')))
                                }}">
                                    {{ ucfirst($plan->plan_type) }}
                                </span>
                            </td>
                            <td>
                                @if($plan->is_active)
                                    <span class="badge bg-success">활성</span>
                                @else
                                    <span class="badge bg-secondary">비활성</span>
                                @endif
                                @if($plan->allow_trial && $plan->trial_period_days > 0)
                                    <br><small class="text-info">{{ $plan->trial_period_days }}일 무료체험</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $plan->serviceUsers()->count() }}</strong>명
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.service.plan.show', $plan->id) }}"
                                       class="btn btn-outline-info btn-square"
                                       title="상세보기">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.service.plan.edit', $plan->id) }}"
                                       class="btn btn-outline-primary btn-square"
                                       title="수정">
                                        <i class="fe fe-edit"></i>
                                    </a>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-square dropdown-toggle" data-bs-toggle="dropdown" title="관리">
                                            <i class="fe fe-settings"></i>
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
                                    @if($plan->serviceUsers()->count() === 0)
                                    <button type="button"
                                            class="btn btn-outline-danger btn-square"
                                            title="삭제"
                                            onclick="deletePlan({{ $plan->id }})">
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
                        전체 {{ $plans->total() }}개 중
                        {{ $plans->firstItem() }}~{{ $plans->lastItem() }}개 표시
                    </div>
                    <div>
                        {{ $plans->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fe fe-package fe-3x text-muted mb-3"></i>
                <h5 class="text-muted">등록된 서비스 플랜이 없습니다</h5>
                <p class="text-muted">새로운 서비스 플랜을 등록해보세요.</p>
                <a href="{{ route('admin.service.plan.create') }}" class="btn btn-primary">
                    <i class="fe fe-plus me-2"></i>첫 번째 플랜 만들기
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
                <h5 class="modal-title">플랜 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>이 서비스 플랜을 삭제하시겠습니까?</p>
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

/* 정가각형 버튼 스타일 */
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

/* 드롭다운 토글 버튼 스타일 조정 */
.btn-square.dropdown-toggle::after {
    display: none;
}
</style>
@endpush

@push('scripts')
<script>
// 삭제 확인
function deletePlan(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/admin/service/plan/${id}`;
    modal.show();
}
</script>
@endpush