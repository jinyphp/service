@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">새 서비스 플랜 만들기</h1>
            <p class="mb-0 text-muted">새로운 서비스 요금제 플랜을 생성합니다.</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.service.plan.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> 목록으로
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.service.plan.store') }}">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">기본 정보</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="service_id" class="form-label">서비스 <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <select name="service_id" id="service_id" class="form-control @error('service_id') is-invalid @enderror" required>
                                        <option value="">서비스를 선택하세요</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
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
                                       value="{{ old('plan_name') }}" required>
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
                                       value="{{ old('plan_code') }}" required>
                                <small class="form-text text-muted">고유한 플랜 식별 코드 (예: basic-monthly, premium-yearly)</small>
                                @error('plan_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">정렬 순서</label>
                                <input type="number" name="sort_order" id="sort_order"
                                       class="form-control @error('sort_order') is-invalid @enderror"
                                       value="{{ old('sort_order', 0) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">플랜 설명</label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">가격 정보</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="monthly_price" class="form-label">월 요금</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₩</span>
                                    </div>
                                    <input type="number" name="monthly_price" id="monthly_price"
                                           class="form-control @error('monthly_price') is-invalid @enderror"
                                           value="{{ old('monthly_price', 0) }}" min="0" step="100">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <input type="checkbox" name="monthly_available" value="1"
                                                   {{ old('monthly_available') ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                @error('monthly_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="quarterly_price" class="form-label">3개월 요금</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₩</span>
                                    </div>
                                    <input type="number" name="quarterly_price" id="quarterly_price"
                                           class="form-control @error('quarterly_price') is-invalid @enderror"
                                           value="{{ old('quarterly_price', 0) }}" min="0" step="100">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <input type="checkbox" name="quarterly_available" value="1"
                                                   {{ old('quarterly_available') ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                @error('quarterly_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="yearly_price" class="form-label">연간 요금</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₩</span>
                                    </div>
                                    <input type="number" name="yearly_price" id="yearly_price"
                                           class="form-control @error('yearly_price') is-invalid @enderror"
                                           value="{{ old('yearly_price', 0) }}" min="0" step="100">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <input type="checkbox" name="yearly_available" value="1"
                                                   {{ old('yearly_available') ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                @error('yearly_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lifetime_price" class="form-label">평생 요금</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₩</span>
                                    </div>
                                    <input type="number" name="lifetime_price" id="lifetime_price"
                                           class="form-control @error('lifetime_price') is-invalid @enderror"
                                           value="{{ old('lifetime_price', 0) }}" min="0" step="100">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <input type="checkbox" name="lifetime_available" value="1"
                                                   {{ old('lifetime_available') ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                @error('lifetime_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="setup_fee" class="form-label">설치비</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₩</span>
                                    </div>
                                    <input type="number" name="setup_fee" id="setup_fee"
                                           class="form-control @error('setup_fee') is-invalid @enderror"
                                           value="{{ old('setup_fee', 0) }}" min="0" step="100">
                                </div>
                                @error('setup_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="trial_period_days" class="form-label">무료 체험 기간 (일)</label>
                                <input type="number" name="trial_period_days" id="trial_period_days"
                                       class="form-control @error('trial_period_days') is-invalid @enderror"
                                       value="{{ old('trial_period_days', 0) }}" min="0" max="365">
                                @error('trial_period_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">기능 및 제한사항</h6>
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
                                               {{ is_array(old('features')) && in_array($key, old('features')) ? 'checked' : '' }}>
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
                                       value="{{ old('max_users') }}" min="0">
                                @error('max_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_projects" class="form-label">최대 프로젝트 수</label>
                                <input type="number" name="max_projects" id="max_projects"
                                       class="form-control @error('max_projects') is-invalid @enderror"
                                       value="{{ old('max_projects') }}" min="0">
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
                                       value="{{ old('storage_limit_gb') }}" min="0" step="0.1">
                                @error('storage_limit_gb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="api_calls_per_month" class="form-label">월간 API 호출 제한</label>
                                <input type="number" name="api_calls_per_month" id="api_calls_per_month"
                                       class="form-control @error('api_calls_per_month') is-invalid @enderror"
                                       value="{{ old('api_calls_per_month') }}" min="0">
                                @error('api_calls_per_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Plan Configuration -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">플랜 설정</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="plan_type" class="form-label">플랜 타입 <span class="text-danger">*</span></label>
                            <select name="plan_type" id="plan_type" class="form-control @error('plan_type') is-invalid @enderror" required>
                                @foreach($planTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('plan_type') == $key ? 'selected' : '' }}>
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
                                    <option value="{{ $key }}" {{ old('billing_type') == $key ? 'selected' : '' }}>
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
                                   value="{{ old('color_code', '#007bff') }}">
                            @error('color_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="icon" class="form-label">아이콘 클래스</label>
                            <input type="text" name="icon" id="icon"
                                   class="form-control @error('icon') is-invalid @enderror"
                                   value="{{ old('icon') }}" placeholder="예: fas fa-star">
                            <small class="form-text text-muted">Font Awesome 아이콘 클래스</small>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Status Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">상태 설정</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_active" id="is_active"
                                   class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                활성화
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_featured" id="is_featured"
                                   class="form-check-input" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                추천 플랜
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_popular" id="is_popular"
                                   class="form-check-input" value="1" {{ old('is_popular') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_popular">
                                인기 플랜
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="allow_trial" id="allow_trial"
                                   class="form-check-input" value="1" {{ old('allow_trial', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_trial">
                                무료 체험 허용
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="auto_renewal" id="auto_renewal"
                                   class="form-check-input" value="1" {{ old('auto_renewal', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_renewal">
                                자동 갱신
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="immediate_upgrade" id="immediate_upgrade"
                                   class="form-check-input" value="1" {{ old('immediate_upgrade', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="immediate_upgrade">
                                즉시 업그레이드
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="immediate_downgrade" id="immediate_downgrade"
                                   class="form-check-input" value="1" {{ old('immediate_downgrade') ? 'checked' : '' }}>
                            <label class="form-check-label" for="immediate_downgrade">
                                즉시 다운그레이드
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> 플랜 생성
                        </button>
                        <a href="{{ route('admin.service.plan.index') }}" class="btn btn-outline-secondary btn-block mt-2">
                            <i class="fas fa-times"></i> 취소
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h6>오류가 발생했습니다:</h6>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif
@endsection

@push('scripts')
<script>
// Auto-generate plan code from plan name
document.getElementById('plan_name').addEventListener('input', function() {
    const planName = this.value;
    const planCode = planName.toLowerCase()
                            .replace(/[^a-z0-9\s]/g, '')
                            .replace(/\s+/g, '-')
                            .replace(/-+/g, '-')
                            .replace(/^-|-$/g, '');

    if (!document.getElementById('plan_code').value) {
        document.getElementById('plan_code').value = planCode;
    }
});

// Calculate discount percentages
function calculateDiscounts() {
    const monthlyPrice = parseFloat(document.getElementById('monthly_price').value) || 0;
    const quarterlyPrice = parseFloat(document.getElementById('quarterly_price').value) || 0;
    const yearlyPrice = parseFloat(document.getElementById('yearly_price').value) || 0;

    if (monthlyPrice > 0) {
        if (quarterlyPrice > 0) {
            const quarterlyExpected = monthlyPrice * 3;
            const quarterlyDiscount = Math.round(((quarterlyExpected - quarterlyPrice) / quarterlyExpected) * 100);
            if (quarterlyDiscount > 0) {
                document.getElementById('quarterly_price').title = `${quarterlyDiscount}% 할인`;
            }
        }

        if (yearlyPrice > 0) {
            const yearlyExpected = monthlyPrice * 12;
            const yearlyDiscount = Math.round(((yearlyExpected - yearlyPrice) / yearlyExpected) * 100);
            if (yearlyDiscount > 0) {
                document.getElementById('yearly_price').title = `${yearlyDiscount}% 할인`;
            }
        }
    }
}

document.getElementById('monthly_price').addEventListener('input', calculateDiscounts);
document.getElementById('quarterly_price').addEventListener('input', calculateDiscounts);
document.getElementById('yearly_price').addEventListener('input', calculateDiscounts);
</script>
@endpush