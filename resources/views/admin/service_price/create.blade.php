@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">새 가격 옵션 추가</h2>
                    <p class="text-muted mb-0">{{ $service->title }}에 새로운 가격 옵션을 추가합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.site.services.price.index', $service->id) }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 폼 -->
    <form method="POST" action="{{ route('admin.site.services.price.store', $service->id) }}">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">기본 정보</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">가격 옵션명 <span class="text-danger">*</span></label>
                                    <input type="text"
                                           id="name"
                                           name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}"
                                           placeholder="예: 기본형, 스탠다드, 프리미엄"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="code" class="form-label">가격 코드</label>
                                    <input type="text"
                                           id="code"
                                           name="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code') }}"
                                           placeholder="예: basic, standard, premium">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">설명</label>
                            <textarea id="description"
                                      name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3"
                                      placeholder="가격 옵션에 대한 설명을 입력하세요">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="price" class="form-label">정가</label>
                                    <input type="number"
                                           id="price"
                                           name="price"
                                           class="form-control @error('price') is-invalid @enderror"
                                           value="{{ old('price') }}"
                                           min="0"
                                           step="0.01"
                                           placeholder="무료인 경우 비워두세요">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">빈 값 또는 0이면 무료 가격으로 설정됩니다.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="sale_price" class="form-label">할인가</label>
                                    <input type="number"
                                           id="sale_price"
                                           name="sale_price"
                                           class="form-control @error('sale_price') is-invalid @enderror"
                                           value="{{ old('sale_price') }}"
                                           min="0"
                                           step="0.01">
                                    @error('sale_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="currency" class="form-label">통화 <span class="text-danger">*</span></label>
                                    <select id="currency"
                                            name="currency"
                                            class="form-control @error('currency') is-invalid @enderror"
                                            required>
                                        <option value="KRW" {{ old('currency', 'KRW') === 'KRW' ? 'selected' : '' }}>원 (KRW)</option>
                                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>달러 (USD)</option>
                                        <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>유로 (EUR)</option>
                                        <option value="JPY" {{ old('currency') === 'JPY' ? 'selected' : '' }}>엔 (JPY)</option>
                                    </select>
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="billing_period" class="form-label">결제 주기 <span class="text-danger">*</span></label>
                                    <select id="billing_period"
                                            name="billing_period"
                                            class="form-control @error('billing_period') is-invalid @enderror"
                                            required>
                                        <option value="monthly" {{ old('billing_period', 'monthly') === 'monthly' ? 'selected' : '' }}>월간</option>
                                        <option value="quarterly" {{ old('billing_period') === 'quarterly' ? 'selected' : '' }}>분기</option>
                                        <option value="yearly" {{ old('billing_period') === 'yearly' ? 'selected' : '' }}>연간</option>
                                        <option value="once" {{ old('billing_period') === 'once' ? 'selected' : '' }}>일회성</option>
                                    </select>
                                    @error('billing_period')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="billing_cycle_count" class="form-label">결제 주기 횟수 <span class="text-danger">*</span></label>
                                    <input type="number"
                                           id="billing_cycle_count"
                                           name="billing_cycle_count"
                                           class="form-control @error('billing_cycle_count') is-invalid @enderror"
                                           value="{{ old('billing_cycle_count', 1) }}"
                                           min="1"
                                           required>
                                    @error('billing_cycle_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="setup_fee" class="form-label">설치비/설정비</label>
                                    <input type="number"
                                           id="setup_fee"
                                           name="setup_fee"
                                           class="form-control @error('setup_fee') is-invalid @enderror"
                                           value="{{ old('setup_fee', 0) }}"
                                           min="0"
                                           step="0.01">
                                    @error('setup_fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="trial_days" class="form-label">무료체험 일수</label>
                                    <input type="number"
                                           id="trial_days"
                                           name="trial_days"
                                           class="form-control @error('trial_days') is-invalid @enderror"
                                           value="{{ old('trial_days', 0) }}"
                                           min="0">
                                    @error('trial_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="min_quantity" class="form-label">최소 수량</label>
                                    <input type="number"
                                           id="min_quantity"
                                           name="min_quantity"
                                           class="form-control @error('min_quantity') is-invalid @enderror"
                                           value="{{ old('min_quantity', 1) }}"
                                           min="1">
                                    @error('min_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="max_quantity" class="form-label">최대 수량</label>
                                    <input type="number"
                                           id="max_quantity"
                                           name="max_quantity"
                                           class="form-control @error('max_quantity') is-invalid @enderror"
                                           value="{{ old('max_quantity') }}"
                                           min="1"
                                           placeholder="제한 없음">
                                    @error('max_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">옵션 설정</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="pos" class="form-label">정렬 순서 <span class="text-danger">*</span></label>
                            <input type="number"
                                   id="pos"
                                   name="pos"
                                   class="form-control @error('pos') is-invalid @enderror"
                                   value="{{ old('pos', 0) }}"
                                   min="0"
                                   required>
                            @error('pos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   id="auto_renewal"
                                   name="auto_renewal"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('auto_renewal', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_renewal">
                                자동 갱신
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   id="is_popular"
                                   name="is_popular"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('is_popular') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_popular">
                                인기 옵션
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   id="is_recommended"
                                   name="is_recommended"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('is_recommended') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_recommended">
                                추천 옵션
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   id="enable"
                                   name="enable"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('enable', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable">
                                활성 상태
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 저장 버튼 -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fe fe-save me-2"></i>가격 옵션 저장
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

