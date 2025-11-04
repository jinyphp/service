@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $plan->plan_name }} 플랜 수정</h2>
                    <p class="text-muted mb-0">플랜의 설정과 가격 정보를 수정합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.service.plan.show', $plan->id) }}" class="btn btn-outline-info me-2">
                        <i class="fe fe-eye me-2"></i>상세보기
                    </a>
                    <a href="{{ route('admin.service.plan.index') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($subscribersCount > 0)
    <div class="alert alert-warning" role="alert">
        <i class="fe fe-alert-triangle me-2"></i>
        <strong>주의:</strong> 이 플랜은 현재 {{ $subscribersCount }}명의 구독자가 있습니다.
        중요한 변경사항은 기존 구독자에게 영향을 줄 수 있습니다.
    </div>
    @endif

    <!-- 오류 메시지 표시 -->
    @if($errors->any())
    <div class="alert alert-danger" role="alert">
        <h6><i class="fe fe-alert-circle me-2"></i>오류가 발생했습니다:</h6>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success" role="alert">
        <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning" role="alert">
        <i class="fe fe-alert-triangle me-2"></i>{{ session('warning') }}
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info" role="alert">
        <i class="fe fe-info me-2"></i>{{ session('info') }}
    </div>
    @endif

    <!-- 서비스 플랜 수정 폼 -->
    <form method="POST" action="{{ route('admin.service.plan.update', $plan->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- 메인 콘텐츠 -->
            <div class="col-lg-8">
                <!-- 기본 정보 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">기본 정보</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="service_id" class="form-label">서비스 <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <select name="service_id" id="service_id" class="form-control @error('service_id') is-invalid @enderror" required>
                                        <option value="">서비스를 선택하세요</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}"
                                                {{ (old('service_id', $plan->service_id) == $service->id) ? 'selected' : '' }}>
                                                {{ $service->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('admin.site.services.index') }}" class="btn btn-outline-info ml-2" title="서비스 관리">
                                        ⚙️
                                    </a>
                                </div>
                                @if($services->count() === 0)
                                    <small class="text-muted">
                                        ℹ️
                                        사용 가능한 서비스가 없습니다. <a href="{{ route('admin.site.services.index') }}">서비스를 먼저 생성하세요</a>
                                    </small>
                                @endif
                                @error('service_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="plan_name" class="form-label">플랜명 <span class="text-danger">*</span></label>
                                <input type="text" name="plan_name" id="plan_name"
                                       class="form-control @error('plan_name') is-invalid @enderror"
                                       value="{{ old('plan_name', $plan->plan_name) }}" required>
                                @error('plan_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="plan_code" class="form-label">플랜 코드 <span class="text-danger">*</span></label>
                                <input type="text" name="plan_code" id="plan_code"
                                       class="form-control @error('plan_code') is-invalid @enderror"
                                       value="{{ old('plan_code', $plan->plan_code) }}" required>
                                <small class="form-text text-muted">고유한 플랜 식별 코드 (예: basic-monthly, premium-yearly)</small>
                                @error('plan_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">정렬 순서</label>
                                <input type="number" name="sort_order" id="sort_order"
                                       class="form-control @error('sort_order') is-invalid @enderror"
                                       value="{{ old('sort_order', $plan->sort_order) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">플랜 설명</label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $plan->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>


                <!-- 기능 및 제한사항 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">기능 및 제한사항</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">포함 기능</label>
                            <div class="row">
                                @foreach($defaultFeatures as $key => $label)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="features[]" id="feature_{{ $key }}"
                                               class="form-check-input" value="{{ $key }}"
                                               {{ (is_array(old('features', $plan->features)) && in_array($key, old('features', $plan->features))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="feature_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_users" class="form-label">최대 사용자 수</label>
                                <input type="number" name="max_users" id="max_users"
                                       class="form-control @error('max_users') is-invalid @enderror"
                                       value="{{ old('max_users', $plan->max_users) }}" min="0">
                                @error('max_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_projects" class="form-label">최대 프로젝트 수</label>
                                <input type="number" name="max_projects" id="max_projects"
                                       class="form-control @error('max_projects') is-invalid @enderror"
                                       value="{{ old('max_projects', $plan->max_projects) }}" min="0">
                                @error('max_projects')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="storage_limit_gb" class="form-label">저장공간 제한 (GB)</label>
                                <input type="number" name="storage_limit_gb" id="storage_limit_gb"
                                       class="form-control @error('storage_limit_gb') is-invalid @enderror"
                                       value="{{ old('storage_limit_gb', $plan->storage_limit_gb) }}" min="0" step="0.1">
                                @error('storage_limit_gb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="api_calls_per_month" class="form-label">월간 API 호출 제한</label>
                                <input type="number" name="api_calls_per_month" id="api_calls_per_month"
                                       class="form-control @error('api_calls_per_month') is-invalid @enderror"
                                       value="{{ old('api_calls_per_month', $plan->api_calls_per_month) }}" min="0">
                                @error('api_calls_per_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 플랜 변경 경로 -->
                @if($availablePlans->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">플랜 변경 경로</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">업그레이드 가능한 플랜</label>
                                @foreach($availablePlans as $availablePlan)
                                <div class="form-check">
                                    <input type="checkbox" name="upgrade_paths[]" id="upgrade_{{ $availablePlan->id }}"
                                           class="form-check-input" value="{{ $availablePlan->plan_code }}"
                                           {{ (is_array(old('upgrade_paths', $plan->upgrade_paths)) && in_array($availablePlan->plan_code, old('upgrade_paths', $plan->upgrade_paths))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="upgrade_{{ $availablePlan->id }}">
                                        {{ $availablePlan->plan_name }} (₩{{ number_format($availablePlan->monthly_price) }}/월)
                                    </label>
                                </div>
                                @endforeach
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">다운그레이드 가능한 플랜</label>
                                @foreach($availablePlans as $availablePlan)
                                <div class="form-check">
                                    <input type="checkbox" name="downgrade_paths[]" id="downgrade_{{ $availablePlan->id }}"
                                           class="form-check-input" value="{{ $availablePlan->plan_code }}"
                                           {{ (is_array(old('downgrade_paths', $plan->downgrade_paths)) && in_array($availablePlan->plan_code, old('downgrade_paths', $plan->downgrade_paths))) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="downgrade_{{ $availablePlan->id }}">
                                        {{ $availablePlan->plan_name }} (₩{{ number_format($availablePlan->monthly_price) }}/월)
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- 사이드바 설정 -->
            <div class="col-lg-4">
                <!-- 플랜 설정 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">플랜 설정</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="plan_type" class="form-label">플랜 타입 <span class="text-danger">*</span></label>
                            <select name="plan_type" id="plan_type" class="form-control @error('plan_type') is-invalid @enderror" required>
                                @foreach($planTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('plan_type', $plan->plan_type) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="billing_type" class="form-label">결제 타입 <span class="text-danger">*</span></label>
                            <select name="billing_type" id="billing_type" class="form-control @error('billing_type') is-invalid @enderror" required>
                                @foreach($billingTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('billing_type', $plan->billing_type) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('billing_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="color_code" class="form-label">색상 코드</label>
                            <input type="color" name="color_code" id="color_code"
                                   class="form-control @error('color_code') is-invalid @enderror"
                                   value="{{ old('color_code', $plan->color_code ?: '#007bff') }}">
                            @error('color_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="icon" class="form-label">아이콘 클래스</label>
                            <input type="text" name="icon" id="icon"
                                   class="form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon', $plan->icon) }}" placeholder="예: fas fa-star">
                            <small class="form-text text-muted">Font Awesome 아이콘 클래스</small>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 상태 설정 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">상태 설정</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_active" id="is_active"
                                   class="form-check-input" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                활성화
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_featured" id="is_featured"
                                   class="form-check-input" value="1" {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                추천 플랜
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_popular" id="is_popular"
                                   class="form-check-input" value="1" {{ old('is_popular', $plan->is_popular) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_popular">
                                인기 플랜
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="allow_trial" id="allow_trial"
                                   class="form-check-input" value="1" {{ old('allow_trial', $plan->allow_trial) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_trial">
                                무료 체험 허용
                            </label>
                        </div>

                        <div class="mb-3" id="trial_period_container" style="{{ old('allow_trial', $plan->allow_trial) ? '' : 'display: none;' }}">
                            <label for="trial_period_days" class="form-label">무료 체험 기간 (일)</label>
                            <input type="number" name="trial_period_days" id="trial_period_days"
                                   class="form-control @error('trial_period_days') is-invalid @enderror"
                                   value="{{ old('trial_period_days', $plan->trial_period_days) }}" min="0" max="365">
                            @error('trial_period_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="auto_renewal" id="auto_renewal"
                                   class="form-check-input" value="1" {{ old('auto_renewal', $plan->auto_renewal) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_renewal">
                                자동 갱신
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="immediate_upgrade" id="immediate_upgrade"
                                   class="form-check-input" value="1" {{ old('immediate_upgrade', $plan->immediate_upgrade) ? 'checked' : '' }}>
                            <label class="form-check-label" for="immediate_upgrade">
                                즉시 업그레이드
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="immediate_downgrade" id="immediate_downgrade"
                                   class="form-check-input" value="1" {{ old('immediate_downgrade', $plan->immediate_downgrade) ? 'checked' : '' }}>
                            <label class="form-check-label" for="immediate_downgrade">
                                즉시 다운그레이드
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 현재 플랜 통계 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">현재 플랜 통계</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">구독자 수</small>
                            <div class="font-weight-bold">{{ $subscribersCount }}명</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">생성일</small>
                            <div class="font-weight-bold">{{ $plan->created_at->format('Y-m-d') }}</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">마지막 수정</small>
                            <div class="font-weight-bold">{{ $plan->updated_at->format('Y-m-d H:i') }}</div>
                        </div>
                    </div>
                </div>

                <!-- 액션 버튼 -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>변경사항 저장
                            </button>
                            <a href="{{ route('admin.service.plan.show', $plan->id) }}" class="btn btn-outline-info">
                                <i class="fe fe-eye me-2"></i>상세보기
                            </a>
                            <a href="{{ route('admin.service.plan.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-x me-2"></i>취소
                            </a>

                            @if($canDelete)
                                <hr class="my-3">
                                <button type="button" class="btn btn-outline-danger" onclick="deletePlan()">
                                    <i class="fe fe-trash-2 me-2"></i>플랜 삭제
                                </button>
                            @else
                                <hr class="my-3">
                                <button type="button" class="btn btn-outline-danger" disabled>
                                    <i class="fe fe-trash-2 me-2"></i>플랜 삭제
                                </button>
                                <small class="text-muted">{{ $deleteReason }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
                <p><strong>{{ $plan->plan_name }}</strong> 플랜을 삭제하시겠습니까?</p>
                <p class="text-danger small">
                    <i class="fe fe-alert-triangle me-1"></i>
                    삭제된 데이터는 복구할 수 없습니다.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <form id="deleteForm" method="POST" action="{{ route('admin.service.plan.destroy', $plan->id) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">삭제</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 삭제 확인
function deletePlan() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// 무료 체험 옵션 토글
document.getElementById('allow_trial').addEventListener('change', function() {
    const trialPeriodContainer = document.getElementById('trial_period_container');
    if (this.checked) {
        trialPeriodContainer.style.display = '';
    } else {
        trialPeriodContainer.style.display = 'none';
        document.getElementById('trial_period_days').value = '0';
    }
});

</script>
@endpush