@extends('jiny-service::layouts.admin.sidebar')

@section('title', '구독 로그 통계')

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
                    <h1 class="h2 fw-bold text-dark">구독 로그 통계</h1>
                    <p class="text-muted mb-0">
                        {{ $startDate->format('Y-m-d') }} ~ {{ $endDate->format('Y-m-d') }} 기간 데이터
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.service.subscription-logs.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i>로그 목록
                    </a>
                    <a href="{{ route('admin.service.subscription-logs.export', ['period' => $period, 'format' => 'csv']) }}" class="btn btn-success">
                        <i class="fe fe-download me-2"></i>CSV 내보내기
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 기간 필터 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="period" class="form-label">기간</label>
                            <select id="period" name="period" class="form-select">
                                <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>최근 7일</option>
                                <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>최근 30일</option>
                                <option value="3months" {{ $period === '3months' ? 'selected' : '' }}>최근 3개월</option>
                                <option value="6months" {{ $period === '6months' ? 'selected' : '' }}>최근 6개월</option>
                                <option value="1year" {{ $period === '1year' ? 'selected' : '' }}>최근 1년</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="action" class="form-label">액션 필터</label>
                            <select id="action" name="action" class="form-select">
                                <option value="">전체 액션</option>
                                <option value="subscribe" {{ $action === 'subscribe' ? 'selected' : '' }}>구독 신청</option>
                                <option value="payment_success" {{ $action === 'payment_success' ? 'selected' : '' }}>결제 성공</option>
                                <option value="payment_failed" {{ $action === 'payment_failed' ? 'selected' : '' }}>결제 실패</option>
                                <option value="cancel" {{ $action === 'cancel' ? 'selected' : '' }}>구독 취소</option>
                                <option value="upgrade" {{ $action === 'upgrade' ? 'selected' : '' }}>업그레이드</option>
                                <option value="refund" {{ $action === 'refund' ? 'selected' : '' }}>환불</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-filter me-2"></i>필터 적용
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 액션별 통계 -->
    @if($actionStats->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-bar-chart-2 me-2"></i>액션별 통계
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($actionStats as $stat)
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="mb-1 text-primary">{{ number_format($stat->count) }}</h4>
                                    <small class="text-muted">
                                        @switch($stat->action)
                                            @case('subscribe') 구독 신청 @break
                                            @case('activate') 구독 활성화 @break
                                            @case('cancel') 구독 취소 @break
                                            @case('payment_success') 결제 성공 @break
                                            @case('payment_failed') 결제 실패 @break
                                            @case('refund') 환불 @break
                                            @case('upgrade') 업그레이드 @break
                                            @case('downgrade') 다운그레이드 @break
                                            @case('extend') 구독 연장 @break
                                            @case('suspend') 일시정지 @break
                                            @case('admin_action') 관리자 조치 @break
                                            @default {{ $stat->action }} @break
                                        @endswitch
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 결과별 통계 -->
    @if($resultStats->count() > 0)
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-pie-chart me-2"></i>결과별 통계
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($resultStats as $stat)
                        @php
                            $percentage = $actionStats->sum('count') > 0 ? round(($stat->count / $actionStats->sum('count')) * 100, 1) : 0;
                            $colorClass = match($stat->result) {
                                'success' => 'success',
                                'failed' => 'danger',
                                'pending' => 'warning',
                                default => 'secondary'
                            };
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge bg-{{ $colorClass }}">
                                    {{ $stat->result === 'success' ? '성공' : ($stat->result === 'failed' ? '실패' : '대기') }}
                                </span>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ number_format($stat->count) }}</div>
                                <small class="text-muted">{{ $percentage }}%</small>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-{{ $colorClass }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 처리자별 통계 -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-users me-2"></i>처리자별 통계
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($processedByStats as $stat)
                        @php
                            $percentage = $actionStats->sum('count') > 0 ? round(($stat->count / $actionStats->sum('count')) * 100, 1) : 0;
                            $colorClass = match($stat->processed_by) {
                                'system' => 'primary',
                                'admin' => 'warning',
                                'user' => 'info',
                                default => 'secondary'
                            };
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge bg-{{ $colorClass }}">
                                    {{ $stat->processed_by === 'system' ? '시스템' : ($stat->processed_by === 'admin' ? '관리자' : '사용자') }}
                                </span>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ number_format($stat->count) }}</div>
                                <small class="text-muted">{{ $percentage }}%</small>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-{{ $colorClass }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 서비스별 통계 -->
    @if($serviceStats->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-layers me-2"></i>서비스별 통계 (상위 10개)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($serviceStats as $stat)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <div>
                                        <div class="fw-medium">{{ $stat->title }}</div>
                                        <small class="text-muted">로그 수</small>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="mb-0 text-primary">{{ number_format($stat->count) }}</h5>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 금액 통계 -->
    @if($paymentStats->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-dollar-sign me-2"></i>금액 통계 (결제 관련)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($paymentStats as $stat)
                            @php
                                $actionName = match($stat->action) {
                                    'payment_success' => '결제 성공',
                                    'payment_failed' => '결제 실패',
                                    'refund' => '환불',
                                    default => $stat->action
                                };
                                $colorClass = match($stat->action) {
                                    'payment_success' => 'success',
                                    'payment_failed' => 'danger',
                                    'refund' => 'warning',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="col-md-4 mb-3">
                                <div class="card border-{{ $colorClass }} h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-{{ $colorClass }}">{{ $actionName }}</h6>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-2">
                                                    <small class="text-muted">건수</small>
                                                    <div class="fw-bold">{{ number_format($stat->count) }}건</div>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">총 금액</small>
                                                    <div class="fw-bold">{{ number_format($stat->total_amount) }}원</div>
                                                </div>
                                                <div>
                                                    <small class="text-muted">평균 금액</small>
                                                    <div class="fw-bold">{{ number_format($stat->avg_amount) }}원</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 일별 활동 -->
    @if($dailyStats->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-calendar me-2"></i>일별 활동
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>날짜</th>
                                    <th>로그 수</th>
                                    <th>활동량</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyStats as $stat)
                                    @php
                                        $maxCount = $dailyStats->max('count');
                                        $percentage = $maxCount > 0 ? round(($stat->count / $maxCount) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($stat->date)->format('m/d (D)') }}</td>
                                        <td><strong>{{ number_format($stat->count) }}</strong></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $percentage }}%">
                                                    {{ $percentage }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 시간대별 활동 -->
    @if($hourlyStats->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-clock me-2"></i>시간대별 활동
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($hourlyStats->chunk(6) as $chunk)
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>시간</th>
                                                <th>활동</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($chunk as $stat)
                                                @php
                                                    $maxCount = $hourlyStats->max('count');
                                                    $percentage = $maxCount > 0 ? round(($stat->count / $maxCount) * 100) : 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ sprintf('%02d:00', $stat->hour) }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 me-2" style="height: 15px;">
                                                                <div class="progress-bar bg-info" style="width: {{ $percentage }}%"></div>
                                                            </div>
                                                            <small class="text-muted">{{ $stat->count }}</small>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
    // 필터 변경 시 자동 제출
    const filterElements = ['period', 'action'];
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