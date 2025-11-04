@extends('jiny-service::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $service->title }} - 상세 정보 관리</h2>
                    <p class="text-muted mb-0">서비스의 상세 기능과 정보를 관리합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.site.services.show', $service->id) }}" class="btn btn-outline-secondary me-2">
                        <i class="fe fe-arrow-left me-2"></i>서비스 상세
                    </a>
                    <a href="{{ route('admin.site.services.detail.create', $service->id) }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>상세 정보 추가
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 서비스 정보 카드 -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $service->title }}</h5>
                    <p class="text-muted mb-2">{{ $service->category ?? '-' }} | {{ $service->code }}</p>
                    <p class="mb-0">{{ $service->description }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-{{ $service->enable ? 'success' : 'secondary' }} mb-2">
                        {{ $service->enable ? '활성' : '비활성' }}
                    </span>
                    @if($service->featured)
                        <span class="badge bg-warning text-dark mb-2">추천</span>
                    @endif
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
                                <i class="fe fe-list text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">전체 항목</h6>
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
                                <i class="fe fe-check text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">활성 항목</h6>
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
                            <div class="bg-info bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-star text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">기능</h6>
                            <h4 class="mb-0">{{ $stats['features'] }}</h4>
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
                                <i class="fe fe-alert-triangle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">제한사항</h6>
                            <h4 class="mb-0">{{ $stats['limitations'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 및 검색 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.site.services.detail.index', $service->id) }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="detail_type" class="form-label">상세 타입</label>
                            <select name="detail_type" id="detail_type" class="form-control">
                                <option value="">전체</option>
                                @foreach($detailTypes as $key => $label)
                                    <option value="{{ $key }}" {{ request('detail_type') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="category" class="form-label">카테고리</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">전체</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="group_name" class="form-label">그룹</label>
                            <select name="group_name" id="group_name" class="form-control">
                                <option value="">전체</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group }}" {{ request('group_name') === $group ? 'selected' : '' }}>
                                        {{ $group }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status" class="form-label">상태</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">전체</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>활성</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>비활성</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="search" class="form-label">검색</label>
                            <input type="text" name="search" id="search" class="form-control"
                                   value="{{ request('search') }}" placeholder="제목, 설명, 값으로 검색">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="display" class="form-label">표시 옵션</label>
                            <select name="display" id="display" class="form-control">
                                <option value="">전체</option>
                                <option value="comparison" {{ request('display') === 'comparison' ? 'selected' : '' }}>비교표 표시</option>
                                <option value="summary" {{ request('display') === 'summary' ? 'selected' : '' }}>요약 표시</option>
                                <option value="highlighted" {{ request('display') === 'highlighted' ? 'selected' : '' }}>강조 표시</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fe fe-search me-1"></i>검색
                        </button>
                        <a href="{{ route('admin.site.services.detail.index', $service->id) }}" class="btn btn-outline-secondary">
                            <i class="fe fe-refresh-cw me-1"></i>초기화
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 상세 정보 목록 -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">상세 정보 목록</h5>
        </div>
        <div class="card-body">
            @if($details->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>타입</th>
                                <th>제목</th>
                                <th>값</th>
                                <th>카테고리/그룹</th>
                                <th>표시 옵션</th>
                                <th>순서</th>
                                <th>상태</th>
                                <th width="120">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $detail)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">{{ $detail->typeDisplay }}</span>
                                    @if($detail->is_highlighted)
                                        <span class="badge bg-warning text-dark ms-1">강조</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($detail->icon)
                                            <i class="{{ $detail->icon_class }} {{ $detail->color_class }} me-2"></i>
                                        @endif
                                        <div>
                                            <strong>{{ $detail->title }}</strong>
                                            @if($detail->description)
                                                <div class="small text-muted">{{ Str::limit($detail->description, 50) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $detail->formatted_value }}</span>
                                    @if($detail->tooltip)
                                        <i class="fe fe-help-circle text-muted ms-1" title="{{ $detail->tooltip }}"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($detail->category)
                                        <span class="badge bg-secondary">{{ $detail->category_display }}</span>
                                    @endif
                                    @if($detail->group_name)
                                        <div class="small text-muted mt-1">{{ $detail->group_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if($detail->show_in_comparison)
                                            <span class="badge bg-info mb-1">비교표</span>
                                        @endif
                                        @if($detail->show_in_summary)
                                            <span class="badge bg-success mb-1">요약</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        그룹: {{ $detail->group_order }}<br>
                                        항목: {{ $detail->pos }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $detail->enable ? 'success' : 'secondary' }}">
                                        {{ $detail->enable ? '활성' : '비활성' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.site.services.detail.edit', [$service->id, $detail->id]) }}"
                                           class="btn btn-outline-primary"
                                           title="수정">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                onclick="deleteDetail({{ $detail->id }})"
                                                title="삭제">
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
                <div class="d-flex justify-content-center">
                    {{ $details->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fe fe-info fe-3x text-muted mb-3"></i>
                    <h5 class="text-muted">등록된 상세 정보가 없습니다</h5>
                    <p class="text-muted mb-4">서비스의 상세 기능과 정보를 추가해보세요.</p>
                    <a href="{{ route('admin.site.services.detail.create', $service->id) }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>첫 번째 상세 정보 추가
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
                <h5 class="modal-title">상세 정보 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>정말로 이 상세 정보를 삭제하시겠습니까?</p>
                <p class="text-danger"><strong>이 작업은 되돌릴 수 없습니다.</strong></p>
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

<script>
function deleteDetail(detailId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `{{ route('admin.site.services.detail.index', $service->id) }}/${detailId}`;

    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<style>
.stat-circle {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection