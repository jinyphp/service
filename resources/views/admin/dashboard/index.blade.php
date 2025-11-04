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
                    <a href="/admin/service/plan" class="btn btn-outline-primary me-2">
                        <i class="fe fe-list me-2"></i>서비스 관리
                    </a>
                    <a href="{{ route('admin.site.services.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>새 서비스 등록
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 메인 통계 카드 -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-briefcase text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">전체 서비스</h6>
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
                            <h6 class="mb-0 text-muted">서비스 중</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['published'] }}</h3>
                            <small class="text-success">{{ $stats['published_rate'] }}%</small>
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
                                <i class="fe fe-edit text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">준비중</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['draft'] }}</h3>
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
                            <div class="bg-info bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-star text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">추천 서비스</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['featured'] }}</h3>
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
                            <div class="bg-secondary bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-folder text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">카테고리</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['categories'] }}</h3>
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
                            <div class="bg-dark bg-gradient rounded-circle p-3 stat-circle">
                                <i class="fe fe-calendar text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">이번 달</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['this_month'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 최근 등록된 서비스 -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">최근 등록된 서비스</h5>
                        <a href="{{ route('admin.site.services.index') }}" class="btn btn-sm btn-outline-primary">
                            전체보기 <i class="fe fe-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentServices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>서비스</th>
                                        <th>카테고리</th>
                                        <th>상태</th>
                                        <th>등록일</th>
                                        <th width="80">관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentServices as $service)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($service->image)
                                                    <img src="{{ $service->image }}"
                                                         alt="{{ $service->title }}"
                                                         class="me-3 rounded"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="me-3 rounded bg-light d-flex align-items-center justify-content-center"
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fe fe-briefcase text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <a href="{{ route('admin.site.services.show', $service->id) }}"
                                                       class="text-decoration-none fw-medium">
                                                        {{ Str::limit($service->title, 25) }}
                                                    </a>
                                                    @if($service->featured)
                                                        <span class="badge bg-warning text-dark ms-1">추천</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($service->category_name)
                                                <span class="badge bg-light text-dark">{{ $service->category_name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->enable)
                                                <span class="badge bg-success">서비스중</span>
                                            @else
                                                <span class="badge bg-secondary">준비중</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($service->created_at)->format('m-d H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.site.services.edit', $service->id) }}"
                                                   class="btn btn-outline-primary btn-sm"
                                                   title="수정">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fe fe-briefcase fe-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">등록된 서비스가 없습니다</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 카테고리별 통계 & 월별 통계 -->
        <div class="col-md-4">
            <!-- 카테고리별 통계 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-white">
                    <h5 class="mb-0">카테고리별 서비스</h5>
                </div>
                <div class="card-body">
                    @if($categoryStats->count() > 0)
                        @foreach($categoryStats as $category)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">{{ $category->title }}</h6>
                                <small class="text-muted">
                                    서비스중: {{ $category->published_count }} / 전체: {{ $category->service_count }}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">{{ $category->service_count }}</span>
                            </div>
                        </div>
                        @if(!$loop->last)
                        <hr>
                        @endif
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fe fe-folder fe-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">카테고리가 없습니다</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 월별 등록 통계 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-white">
                    <h5 class="mb-0">월별 등록 현황</h5>
                    <small class="text-muted">최근 6개월</small>
                </div>
                <div class="card-body">
                    @if(count($monthlyStats) > 0)
                        @foreach($monthlyStats as $month)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">{{ $month['month_name'] }}</span>
                            <div class="d-flex align-items-center">
                                <div class="progress me-2" style="width: 60px; height: 6px;">
                                    <div class="progress-bar bg-primary"
                                         style="width: {{ $month['count'] > 0 ? min(($month['count'] / max(array_column($monthlyStats, 'count'))) * 100, 100) : 0 }}%">
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark">{{ $month['count'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fe fe-bar-chart-2 fe-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">등록 데이터가 없습니다</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 빠른 작업 -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-white">
                    <h5 class="mb-0">빠른 작업</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.site.services.create') }}" class="card text-decoration-none quick-action-card">
                                <div class="card-body text-center">
                                    <i class="fe fe-plus fe-2x text-primary mb-2"></i>
                                    <h6 class="mb-0">새 서비스 등록</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.service.categories.index') }}" class="card text-decoration-none quick-action-card">
                                <div class="card-body text-center">
                                    <i class="fe fe-folder fe-2x text-success mb-2"></i>
                                    <h6 class="mb-0">카테고리 관리</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.site.services.index', ['enable' => '0']) }}" class="card text-decoration-none quick-action-card">
                                <div class="card-body text-center">
                                    <i class="fe fe-edit fe-2x text-warning mb-2"></i>
                                    <h6 class="mb-0">준비중인 서비스</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.site.services.index', ['featured' => '1']) }}" class="card text-decoration-none quick-action-card">
                                <div class="card-body text-center">
                                    <i class="fe fe-star fe-2x text-info mb-2"></i>
                                    <h6 class="mb-0">추천 서비스</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
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

/* 빠른 작업 카드 스타일 */
.quick-action-card {
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.quick-action-card:hover {
    border-color: #007bff;
    box-shadow: 0 0.25rem 0.5rem rgba(0, 123, 255, 0.1);
    transform: translateY(-2px);
}

.quick-action-card .card-body {
    padding: 1.5rem;
}

/* 카드 그림자 효과 */
.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
</style>
@endpush
