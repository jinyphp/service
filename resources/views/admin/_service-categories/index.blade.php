@extends($layout ?? 'jiny-service::layouts.admin.sidebar')

@section('title', $title . ' 관리')

@section('content')
<div class="container-fluid">
    <!-- Page header -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex align-items-center justify-content-between">
                <div class="mb-2 mb-lg-0">
                    <h1 class="mb-0 h2 fw-bold">{{ $title }} 관리</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/admin/service">대시보드</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.' . $routePrefix . '.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>{{ $title }} 추가
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and filters -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">검색</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ $searchValue ?? '' }}" placeholder="카테고리명으로 검색">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-search me-2"></i>검색
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <a href="{{ route('admin.' . $routePrefix . '.index') }}" class="btn btn-light">
                                    <i class="fe fe-refresh-ccw me-2"></i>초기화
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ $title }} 목록</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>카테고리명</th>
                                    <th>설명</th>
                                    <th>상태</th>
                                    <th>정렬순서</th>
                                    <th>생성일</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->image)
                                            <img src="{{ $item->image }}" alt="{{ $item->name }}"
                                                 class="avatar avatar-sm rounded me-2">
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $item->name }}</h6>
                                                @if($item->icon)
                                                <small class="text-muted">
                                                    <i class="{{ $item->icon }}"></i> {{ $item->icon }}
                                                </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate" style="max-width: 200px; display: inline-block;">
                                            {{ $item->description }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->is_active)
                                        <span class="badge bg-success">활성</span>
                                        @else
                                        <span class="badge bg-secondary">비활성</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->sort_order ?? '-' }}</td>
                                    <td>{{ $item->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.' . $routePrefix . '.show', $item->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.' . $routePrefix . '.edit', $item->id) }}"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.' . $routePrefix . '.destroy', $item->id) }}"
                                                  method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('정말 삭제하시겠습니까?')">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fe fe-inbox fs-1 text-muted mb-3"></i>
                                            <h5 class="text-muted">{{ $title }}이 없습니다</h5>
                                            <p class="text-muted mb-3">새로운 {{ $title }}을 추가해보세요.</p>
                                            <a href="{{ route('admin.' . $routePrefix . '.create') }}" class="btn btn-primary">
                                                <i class="fe fe-plus me-2"></i>{{ $title }} 추가
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($items->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            총 {{ $items->total() }}개 중 {{ $items->firstItem() }}-{{ $items->lastItem() }}개 표시
                        </small>
                        {{ $items->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto submit search form on enter
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});
</script>
@endsection
