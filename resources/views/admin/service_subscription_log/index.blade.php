@extends('jiny-service::layouts.admin.sidebar')

@section('title', '구독 로그 관리')

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 fw-bold text-dark">구독 로그 관리</h1>
                    <p class="text-muted mb-0">서비스 구독자들의 모든 활동과 상태 변경을 추적합니다.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.service.subscription-logs.stats') }}" class="btn btn-info">
                        <i class="fe fe-bar-chart-2 me-2"></i>통계 보기
                    </a>
                    <a href="{{ route('admin.service.subscription-logs.export', ['format' => 'csv']) }}" class="btn btn-success">
                        <i class="fe fe-download me-2"></i>CSV 내보내기
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fe fe-activity" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title mb-1">{{ number_format($stats['total']) }}</h5>
                    <small class="text-muted">전체 로그</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fe fe-clock" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title mb-1">{{ number_format($stats['today']) }}</h5>
                    <small class="text-muted">오늘</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fe fe-calendar" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title mb-1">{{ number_format($stats['this_week']) }}</h5>
                    <small class="text-muted">이번 주</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2">
                        <i class="fe fe-bar-chart" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title mb-1">{{ number_format($stats['this_month']) }}</h5>
                    <small class="text-muted">이번 달</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fe fe-check-circle" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title mb-1">{{ number_format($stats['successful']) }}</h5>
                    <small class="text-muted">성공</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="fe fe-x-circle" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title mb-1">{{ number_format($stats['failed']) }}</h5>
                    <small class="text-muted">실패</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-filter me-2"></i>필터 및 검색
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">검색</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   class="form-control"
                                   placeholder="사용자명, 이메일, 액션 제목...">
                        </div>
                        <div class="col-md-2">
                            <label for="service_id" class="form-label">서비스</label>
                            <select id="service_id" name="service_id" class="form-select">
                                <option value="">전체</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                        {{ $service->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="action" class="form-label">액션</label>
                            <select id="action" name="action" class="form-select">
                                <option value="">전체</option>
                                @foreach($actions as $key => $label)
                                    <option value="{{ $key }}" {{ request('action') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="result" class="form-label">결과</label>
                            <select id="result" name="result" class="form-select">
                                <option value="">전체</option>
                                <option value="success" {{ request('result') == 'success' ? 'selected' : '' }}>성공</option>
                                <option value="failed" {{ request('result') == 'failed' ? 'selected' : '' }}>실패</option>
                                <option value="pending" {{ request('result') == 'pending' ? 'selected' : '' }}>대기</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="processed_by" class="form-label">처리자</label>
                            <select id="processed_by" name="processed_by" class="form-select">
                                <option value="">전체</option>
                                <option value="system" {{ request('processed_by') == 'system' ? 'selected' : '' }}>시스템</option>
                                <option value="admin" {{ request('processed_by') == 'admin' ? 'selected' : '' }}>관리자</option>
                                <option value="user" {{ request('processed_by') == 'user' ? 'selected' : '' }}>사용자</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 로그 목록 -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fe fe-list me-2"></i>구독 로그 목록
                        </h6>
                        <small class="text-muted">총 {{ number_format($logs->total()) }}개</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">ID</th>
                                    <th>사용자</th>
                                    <th>서비스</th>
                                    <th>액션</th>
                                    <th>상태 변경</th>
                                    <th>금액</th>
                                    <th>처리자</th>
                                    <th>결과</th>
                                    <th>일시</th>
                                    <th class="pe-3">작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-medium">#{{ $log->id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">{{ $log->serviceUser->user_name ?? 'N/A' }}</span>
                                                <small class="text-muted">{{ $log->serviceUser->user_email ?? $log->user_uuid }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $log->service->title ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">{{ $log->action_title }}</span>
                                                <small class="text-muted">{{ $actions[$log->action] ?? $log->action }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($log->status_before || $log->status_after)
                                                <div class="d-flex align-items-center">
                                                    @if($log->status_before)
                                                        <span class="badge bg-secondary">{{ $log->status_before }}</span>
                                                    @endif
                                                    @if($log->status_before && $log->status_after)
                                                        <i class="fe fe-arrow-right mx-2 text-muted"></i>
                                                    @endif
                                                    @if($log->status_after)
                                                        <span class="badge bg-primary">{{ $log->status_after }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->amount != 0)
                                                <span class="fw-medium {{ $log->amount > 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount) }}{{ $log->currency }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $log->processed_by === 'admin' ? 'warning' : ($log->processed_by === 'user' ? 'info' : 'secondary') }}">
                                                {{ $log->processed_by === 'admin' ? '관리자' : ($log->processed_by === 'user' ? '사용자' : '시스템') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $log->result === 'success' ? 'success' : ($log->result === 'failed' ? 'danger' : 'warning') }}">
                                                {{ $log->result === 'success' ? '성공' : ($log->result === 'failed' ? '실패' : '대기') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span>{{ $log->created_at->format('m/d H:i') }}</span>
                                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                            </div>
                                        </td>
                                        <td class="pe-3">
                                            <a href="{{ route('admin.service.subscription-logs.show', $log->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fe fe-inbox mb-3" style="font-size: 3rem;"></i>
                                                <p class="mb-0">로그가 없습니다.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($logs->hasPages())
                    <div class="card-footer bg-white">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 액션 통계 -->
    @if($actionStats->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-pie-chart me-2"></i>액션별 통계
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($actionStats as $stat)
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="text-center">
                                    <h5 class="mb-1">{{ number_format($stat->count) }}</h5>
                                    <small class="text-muted">{{ $actions[$stat->action] ?? $stat->action }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 필터 자동 제출 (디바운스 적용)
    let searchTimeout;
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    }

    // 다른 필터 요소들 즉시 제출
    const filterElements = ['service_id', 'action', 'result', 'processed_by'];
    filterElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function() {
                this.form.submit();
            });
        }
    });
});
</script>
@endpush