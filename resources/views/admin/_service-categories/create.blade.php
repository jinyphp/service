@extends($layout ?? 'jiny-service::layouts.admin.sidebar')

@section('title', $title . ' 추가')

@section('content')
<div class="container-fluid">
    <!-- Page header -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3">
                <div class="mb-2 mb-lg-0">
                    <h1 class="mb-0 h2 fw-bold">{{ $title }} 추가</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/admin/service">대시보드</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.' . $routePrefix . '.index') }}">{{ $title }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">추가</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="row">
        <div class="col-lg-8 col-md-12 col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ $title }} 정보</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.' . $routePrefix . '.store') }}" method="POST">
                        @csrf

                        <!-- 카테고리명 -->
                        <div class="mb-3">
                            <label for="name" class="form-label">카테고리명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 설명 -->
                        <div class="mb-3">
                            <label for="description" class="form-label">설명</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 이미지 URL -->
                        <div class="mb-3">
                            <label for="image" class="form-label">이미지 URL</label>
                            <input type="url" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" value="{{ old('image') }}">
                            @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">카테고리 이미지의 URL을 입력하세요.</div>
                        </div>

                        <!-- 아이콘 -->
                        <div class="mb-3">
                            <label for="icon" class="form-label">아이콘 클래스</label>
                            <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                   id="icon" name="icon" value="{{ old('icon') }}" placeholder="fe fe-home">
                            @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">FontAwesome 또는 Feather 아이콘 클래스를 입력하세요.</div>
                        </div>

                        <!-- 색상 -->
                        <div class="mb-3">
                            <label for="color" class="form-label">색상</label>
                            <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror"
                                   id="color" name="color" value="{{ old('color', '#007bff') }}">
                            @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 정렬 순서 -->
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">정렬 순서</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                            @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">낮은 숫자일수록 먼저 표시됩니다.</div>
                        </div>

                        <!-- 활성 상태 -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    활성 상태
                                </label>
                            </div>
                            <div class="form-text">비활성화하면 사이트에서 표시되지 않습니다.</div>
                        </div>

                        <!-- 버튼 -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.' . $routePrefix . '.index') }}" class="btn btn-light">
                                <i class="fe fe-arrow-left me-2"></i>목록으로
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>저장하기
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 미리보기 패널 -->
        <div class="col-lg-4 col-md-12 col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">미리보기</h4>
                </div>
                <div class="card-body">
                    <div class="preview-category text-center p-3 border rounded" id="preview">
                        <div class="category-icon mb-3">
                            <i class="fe fe-folder fs-1 text-primary" id="preview-icon"></i>
                        </div>
                        <h5 class="category-name mb-2" id="preview-name">카테고리명</h5>
                        <p class="category-description text-muted mb-0" id="preview-description">카테고리 설명</p>
                    </div>
                    <div class="mt-3">
                        <h6>사용법</h6>
                        <ul class="list-unstyled small text-muted">
                            <li>• 아이콘: <code>fe fe-*</code> 또는 <code>fas fa-*</code></li>
                            <li>• 이미지가 있으면 아이콘 대신 표시됩니다</li>
                            <li>• 색상은 배경이나 테두리에 적용됩니다</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// 실시간 미리보기
function updatePreview() {
    const name = document.getElementById('name').value || '카테고리명';
    const description = document.getElementById('description').value || '카테고리 설명';
    const icon = document.getElementById('icon').value || 'fe fe-folder';
    const color = document.getElementById('color').value || '#007bff';

    document.getElementById('preview-name').textContent = name;
    document.getElementById('preview-description').textContent = description;
    document.getElementById('preview-icon').className = icon + ' fs-1';
    document.getElementById('preview-icon').style.color = color;
}

// 입력 필드에 이벤트 리스너 추가
document.getElementById('name').addEventListener('input', updatePreview);
document.getElementById('description').addEventListener('input', updatePreview);
document.getElementById('icon').addEventListener('input', updatePreview);
document.getElementById('color').addEventListener('input', updatePreview);

// 초기 미리보기 업데이트
updatePreview();
</script>
@endsection
