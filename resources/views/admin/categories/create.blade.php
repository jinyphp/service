@extends($layout ?? 'jiny-service::layouts.admin.sidebar')

@section('title', $config['title'])

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $config['title'] }}</h2>
                    <p class="text-muted mb-0">{{ $config['subtitle'] }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.service.categories.index') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 카테고리 생성 폼 -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">카테고리 정보</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.service.categories.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label for="title" class="form-label">카테고리명 <span class="text-danger">*</span></label>
                                    <input type="text"
                                           id="title"
                                           name="title"
                                           class="form-control @error('title') is-invalid @enderror"
                                           placeholder="카테고리명을 입력하세요"
                                           value="{{ old('title') }}"
                                           required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="code" class="form-label">코드</label>
                                    <input type="text"
                                           id="code"
                                           name="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           placeholder="자동 생성됩니다"
                                           value="{{ old('code') }}">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">비워두면 카테고리명으로 자동 생성됩니다.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">설명</label>
                            <textarea id="description"
                                      name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4"
                                      placeholder="카테고리에 대한 설명을 입력하세요">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="parent_id" class="form-label">상위 카테고리</label>
                                    <select id="parent_id"
                                            name="parent_id"
                                            class="form-control @error('parent_id') is-invalid @enderror">
                                        <option value="">최상위 카테고리</option>
                                        @foreach($parentCategories as $parent)
                                            <option value="{{ $parent->id }}"
                                                    {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                                {{ $parent->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="pos" class="form-label">정렬 순서</label>
                                    <input type="number"
                                           id="pos"
                                           name="pos"
                                           class="form-control @error('pos') is-invalid @enderror"
                                           placeholder="0"
                                           value="{{ old('pos', 0) }}"
                                           min="0">
                                    @error('pos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">숫자가 작을수록 먼저 표시됩니다.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="image" class="form-label">이미지</label>
                            <input type="text"
                                   id="image"
                                   name="image"
                                   class="form-control @error('image') is-invalid @enderror"
                                   placeholder="이미지 URL을 입력하세요"
                                   value="{{ old('image') }}">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <h6 class="mb-3">SEO 설정</h6>

                        <div class="form-group mb-3">
                            <label for="meta_title" class="form-label">메타 제목</label>
                            <input type="text"
                                   id="meta_title"
                                   name="meta_title"
                                   class="form-control @error('meta_title') is-invalid @enderror"
                                   placeholder="검색엔진에 표시될 제목"
                                   value="{{ old('meta_title') }}">
                            @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="meta_description" class="form-label">메타 설명</label>
                            <textarea id="meta_description"
                                      name="meta_description"
                                      class="form-control @error('meta_description') is-invalid @enderror"
                                      rows="3"
                                      placeholder="검색엔진에 표시될 설명">{{ old('meta_description') }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <h6 class="mb-3">설정</h6>

                        <div class="form-group mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label for="enable" class="form-label mb-1">카테고리 활성화</label>
                                    <small class="text-muted d-block">비활성화하면 사용자에게 표시되지 않습니다.</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           role="switch"
                                           id="enable"
                                           name="enable"
                                           value="1"
                                           {{ old('enable', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable"></label>
                                </div>
                            </div>
                            @error('enable')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.service.categories.index') }}" class="btn btn-secondary">
                                <i class="fe fe-x me-2"></i>취소
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>저장
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">안내</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <h6><i class="fe fe-info me-2"></i>카테고리 생성 안내</h6>
                        <ul class="mb-0 small">
                            <li>카테고리명은 필수 입력 항목입니다.</li>
                            <li>URL 슬러그를 비워두면 카테고리명으로 자동 생성됩니다.</li>
                            <li>상위 카테고리를 선택하면 하위 카테고리가 됩니다.</li>
                            <li>정렬 순서는 작은 숫자가 먼저 표시됩니다.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 카테고리명 입력시 자동으로 코드 생성
    const titleInput = document.getElementById('title');
    const codeInput = document.getElementById('code');

    titleInput.addEventListener('input', function() {
        if (!codeInput.value || codeInput.dataset.autoGenerated === 'true') {
            const code = this.value
                .toLowerCase()
                .replace(/[^a-z0-9가-힣\s-]/g, '')
                .replace(/\s+/g, '-')
                .trim();
            codeInput.value = code;
            codeInput.dataset.autoGenerated = 'true';
        }
    });

    codeInput.addEventListener('input', function() {
        if (this.value) {
            this.dataset.autoGenerated = 'false';
        }
    });
});
</script>
@endpush
