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
                    <a href="{{ route('admin.service.dashboard') }}" class="btn btn-outline-secondary me-2">
                        <i class="fe fe-arrow-left me-2"></i>대시보드
                    </a>
                    <a href="{{ route('admin.service.categories.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>새 카테고리 생성
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-folder text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">전체 카테고리</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">활성화</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['enabled'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-x-circle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">비활성화</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['disabled'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-folder-plus text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">상위 카테고리</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['parent_categories'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-secondary bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-folder-minus text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">하위 카테고리</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['sub_categories'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.service.categories.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">검색</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   class="form-control"
                                   placeholder="카테고리명, 설명으로 검색..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="enable">상태</label>
                            <select id="enable" name="enable" class="form-control">
                                <option value="">전체</option>
                                <option value="1" {{ request('enable') === '1' ? 'selected' : '' }}>활성화</option>
                                <option value="0" {{ request('enable') === '0' ? 'selected' : '' }}>비활성화</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="parent">상위 카테고리</label>
                            <select id="parent" name="parent" class="form-control">
                                <option value="all">전체</option>
                                <option value="0" {{ request('parent') === '0' ? 'selected' : '' }}>최상위 카테고리</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fe fe-search me-1"></i>검색
                        </button>
                        <a href="{{ route('admin.service.categories.index') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-refresh-cw me-1"></i>초기화
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 카테고리 목록 -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">카테고리 목록</h5>
        </div>
        <div class="card-body p-0">
            @if($categories->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">ID</th>
                                <th>카테고리 정보</th>
                                <th>상위 카테고리</th>
                                <th width="100">서비스 수</th>
                                <th width="80">정렬</th>
                                <th width="100">상태</th>
                                <th width="150">등록일</th>
                                <th width="120">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($category->image)
                                            <img src="{{ $category->image }}"
                                                 alt="{{ $category->title }}"
                                                 class="me-3 rounded"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="me-3 rounded bg-light d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <i class="fe fe-folder text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $category->title }}</strong>
                                            <br><small class="text-muted">{{ $category->code }}</small>
                                            @if($category->description)
                                                <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($category->parent_name)
                                        <span class="badge bg-light text-dark">{{ $category->parent_name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $category->service_count }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $category->pos }}</span>
                                </td>
                                <td>
                                    @if($category->enable)
                                        <span class="badge bg-success">활성화</span>
                                    @else
                                        <span class="badge bg-secondary">비활성화</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($category->created_at)->format('Y-m-d H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.service.categories.edit', $category->id) }}"
                                           class="btn btn-outline-primary"
                                           title="수정">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                title="삭제"
                                                onclick="deleteCategory({{ $category->id }})">
                                            <i class="fe fe-trash-2"></i>
                                        </button>
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
                            전체 {{ $categories->total() }}개 중
                            {{ $categories->firstItem() }}~{{ $categories->lastItem() }}개 표시
                        </div>
                        <div>
                            {{ $categories->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fe fe-folder fe-3x text-muted mb-3"></i>
                    <h5 class="text-muted">등록된 카테고리가 없습니다</h5>
                    <p class="text-muted">새 카테고리를 생성해보세요.</p>
                    <a href="{{ route('admin.service.categories.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>새 카테고리 생성
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
                <h5 class="modal-title">카테고리 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>이 카테고리를 삭제하시겠습니까?</p>
                <p class="text-danger small">
                    <i class="fe fe-alert-triangle me-1"></i>
                    카테고리에 속한 서비스나 하위 카테고리가 있으면 삭제할 수 없습니다.
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

/* 카드 그림자 효과 */
.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
</style>
@endpush

@push('scripts')
<script>
// 삭제 확인
function deleteCategory(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/admin/service/categories/${id}`;
    modal.show();
}
</script>
@endpush
