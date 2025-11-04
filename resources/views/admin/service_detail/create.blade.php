@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">새 상세 정보 추가</h2>
                    <p class="text-muted mb-0">{{ $service->title }}에 새로운 상세 정보를 추가합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.site.services.detail.index', $service->id) }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 폼 -->
    <form method="POST" action="{{ route('admin.site.services.detail.store', $service->id) }}">
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
                                    <label for="detail_type" class="form-label">상세 타입 <span class="text-danger">*</span></label>
                                    <select id="detail_type"
                                            name="detail_type"
                                            class="form-control @error('detail_type') is-invalid @enderror"
                                            required>
                                        <option value="">선택하세요</option>
                                        @foreach($detailTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('detail_type') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('detail_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                                    <input type="text"
                                           id="title"
                                           name="title"
                                           class="form-control @error('title') is-invalid @enderror"
                                           value="{{ old('title') }}"
                                           placeholder="예: 페이지 수, 저장 공간"
                                           required>
                                    @error('title')
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
                                      placeholder="상세 정보에 대한 설명을 입력하세요">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="value_type" class="form-label">값 타입 <span class="text-danger">*</span></label>
                                    <select id="value_type"
                                            name="value_type"
                                            class="form-control @error('value_type') is-invalid @enderror"
                                            required>
                                        @foreach($valueTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('value_type', 'text') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('value_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="value" class="form-label">값</label>
                                    <input type="text"
                                           id="value"
                                           name="value"
                                           class="form-control @error('value') is-invalid @enderror"
                                           value="{{ old('value') }}"
                                           placeholder="예: 10, 무제한, true">
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="unit" class="form-label">단위</label>
                                    <input type="text"
                                           id="unit"
                                           name="unit"
                                           class="form-control @error('unit') is-invalid @enderror"
                                           value="{{ old('unit') }}"
                                           placeholder="예: 개, GB, 회">
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="icon" class="form-label">아이콘 클래스</label>
                                    <input type="text"
                                           id="icon"
                                           name="icon"
                                           class="form-control @error('icon') is-invalid @enderror"
                                           value="{{ old('icon') }}"
                                           placeholder="예: fas fa-check-circle">
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="color" class="form-label">색상 클래스</label>
                                    <input type="text"
                                           id="color"
                                           name="color"
                                           class="form-control @error('color') is-invalid @enderror"
                                           value="{{ old('color') }}"
                                           placeholder="예: text-success, text-warning">
                                    @error('color')
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
                        <h5 class="mb-0">분류 및 표시 설정</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="category" class="form-label">카테고리</label>
                            <select id="category"
                                    name="category"
                                    class="form-control @error('category') is-invalid @enderror">
                                <option value="">선택하세요</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="group_name" class="form-label">그룹명</label>
                            <input type="text"
                                   id="group_name"
                                   name="group_name"
                                   class="form-control @error('group_name') is-invalid @enderror"
                                   value="{{ old('group_name') }}"
                                   placeholder="예: 기본 기능, 스토리지"
                                   list="existing_groups">
                            <datalist id="existing_groups">
                                @foreach($existingGroups as $group)
                                    <option value="{{ $group }}">
                                @endforeach
                            </datalist>
                            @error('group_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="group_order" class="form-label">그룹 순서 <span class="text-danger">*</span></label>
                                    <input type="number"
                                           id="group_order"
                                           name="group_order"
                                           class="form-control @error('group_order') is-invalid @enderror"
                                           value="{{ old('group_order', 0) }}"
                                           min="0"
                                           required>
                                    @error('group_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="pos" class="form-label">정렬 순서 <span class="text-danger">*</span></label>
                                    <input type="number"
                                           id="pos"
                                           name="pos"
                                           class="form-control @error('pos') is-invalid @enderror"
                                           value="{{ old('pos', $nextPos) }}"
                                           min="0"
                                           required>
                                    @error('pos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   id="is_highlighted"
                                   name="is_highlighted"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('is_highlighted') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_highlighted">
                                강조 표시
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   id="show_in_comparison"
                                   name="show_in_comparison"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('show_in_comparison', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_in_comparison">
                                비교표에 표시
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   id="show_in_summary"
                                   name="show_in_summary"
                                   class="form-check-input"
                                   value="1"
                                   {{ old('show_in_summary') ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_in_summary">
                                요약에 표시
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
                            <i class="fe fe-save me-2"></i>상세 정보 저장
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection