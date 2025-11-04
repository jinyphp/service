@extends('jiny-service::layouts.admin.sidebar')

@section('title', '결제 통계')

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
                    <h1 class="h2 fw-bold text-dark">결제 통계</h1>
                    <p class="text-muted mb-0">
                        {{ $startDate->format('Y-m-d') }} ~ {{ $endDate->format('Y-m-d') }} 기간 데이터
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.service.payments.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i>결제 목록
                    </a>
                    <a href="{{ route('admin.service.payments.export', ['period' => $period, 'format' => 'csv']) }}" class="btn btn-success">
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
                            <label for="status" class="form-label">상태 필터</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">전체 상태</option>
                                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>결제 완료</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>결제 대기</option>
                                <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>결제 실패</option>
                                <option value="refunded" {{ $status === 'refunded' ? 'selected' : '' }}>환불 완료</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="payment_method" class="form-label">결제 방법</label>
                            <select id="payment_method" name="payment_method" class="form-select">
                                <option value="">전체 방법</option>
                                <option value="credit_card" {{ $payment_method === 'credit_card' ? 'selected' : '' }}>신용카드</option>
                                <option value="bank_transfer" {{ $payment_method === 'bank_transfer' ? 'selected' : '' }}>계좌이체</option>
                                <option value="virtual_account" {{ $payment_method === 'virtual_account' ? 'selected' : '' }}>가상계좌</option>
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

    <!-- 상태별 통계 -->
    @if($statusStats->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-pie-chart me-2"></i>상태별 통계
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($statusStats as $stat)
                            @php
                                $statusText = match($stat->status) {
                                    'completed' => '결제 완료',
                                    'pending' => '결제 대기',
                                    'processing' => '결제 진행 중',
                                    'failed' => '결제 실패',
                                    'cancelled' => '결제 취소',
                                    'refunded' => '환불 완료',
                                    'partially_refunded' => '부분 환불',
                                    default => $stat->status
                                };
                                $colorClass = match($stat->status) {
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'failed' => 'danger',
                                    'cancelled' => 'secondary',
                                    'refunded', 'partially_refunded' => 'dark',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="mb-1 text-{{ $colorClass }}">{{ number_format($stat->count) }}</h4>
                                    <small class="text-muted">{{ $statusText }}</small>
                                    <div class="mt-2">
                                        <small class="text-muted">{{ number_format($stat->total_amount) }} 원</small>
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

    <!-- 결제 방법별 & 결제 유형별 통계 -->
    <div class="row mb-4">
        @if($methodStats->count() > 0)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-credit-card me-2"></i>결제 방법별 통계
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($methodStats as $stat)
                        @php
                            $methodText = match($stat->payment_method) {
                                'credit_card' => '신용카드',
                                'debit_card' => '체크카드',
                                'bank_transfer' => '계좌이체',
                                'virtual_account' => '가상계좌',
                                'mobile_payment' => '모바일결제',
                                'crypto' => '암호화폐',
                                'paypal' => '페이팔',
                                'stripe' => '스트라이프',
                                default => '기타'
                            };
                            $percentage = $statusStats->sum('count') > 0 ? round(($stat->count / $statusStats->sum('count')) * 100, 1) : 0;
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge bg-secondary">{{ $methodText }}</span>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ number_format($stat->count) }}건</div>
                                <small class="text-muted">{{ number_format($stat->total_amount) }}원</small>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-secondary" style="width: {{ $percentage }}%"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($typeStats->count() > 0)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-tag me-2"></i>결제 유형별 통계
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($typeStats as $stat)
                        @php
                            $typeText = match($stat->payment_type) {
                                'subscription' => '정기 구독',
                                'one_time' => '일회성 결제',
                                'upgrade' => '업그레이드',
                                'extension' => '연장',
                                'late_fee' => '연체료',
                                'setup_fee' => '설치비',
                                default => $stat->payment_type
                            };
                            $percentage = $statusStats->sum('count') > 0 ? round(($stat->count / $statusStats->sum('count')) * 100, 1) : 0;
                            $colorClass = match($stat->payment_type) {
                                'subscription' => 'primary',
                                'upgrade' => 'success',
                                'one_time' => 'info',
                                default => 'secondary'
                            };
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge bg-{{ $colorClass }}">{{ $typeText }}</span>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ number_format($stat->count) }}건</div>
                                <small class="text-muted">{{ number_format($stat->total_amount) }}원</small>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-{{ $colorClass }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

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
                                        <small class="text-muted">{{ number_format($stat->count) }}건</small>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="mb-0 text-primary">{{ number_format($stat->total_amount) }}원</h6>
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

    <!-- 환불 및 실패 통계 -->
    <div class="row mb-4">
        @if($refundStats->count() > 0)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-rotate-ccw me-2"></i>환불 통계
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($refundStats as $stat)
                        @php
                            $statusText = match($stat->status) {
                                'refunded' => '완전 환불',
                                'partially_refunded' => '부분 환불',
                                default => $stat->status
                            };
                            $colorClass = match($stat->status) {
                                'refunded' => 'warning',
                                'partially_refunded' => 'info',
                                default => 'secondary'
                            };
                        @endphp
                        <div class="card border-{{ $colorClass }} mb-3">
                            <div class="card-body text-center">
                                <h6 class="card-title text-{{ $colorClass }}">{{ $statusText }}</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">건수</small>
                                            <div class="fw-bold">{{ number_format($stat->count) }}건</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-2">
                                            <small class="text-muted">총 환불액</small>
                                            <div class="fw-bold">{{ number_format($stat->total_refunded) }}원</div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <small class="text-muted">평균 환불액</small>
                                    <div class="fw-bold">{{ number_format($stat->avg_refunded) }}원</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($failureStats->count() > 0)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-x-circle me-2"></i>실패 원인별 통계
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($failureStats as $stat)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <code class="bg-danger text-white px-2 py-1 rounded">{{ $stat->failure_code }}</code>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-danger">{{ number_format($stat->count) }}건</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- 월별 수익 추이 -->
    @if($monthlyRevenue->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-trending-up me-2"></i>월별 수익 추이 (최근 12개월)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>월</th>
                                    <th>결제 건수</th>
                                    <th>총 수익</th>
                                    <th>평균 결제액</th>
                                    <th>성장률</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyRevenue as $index => $revenue)
                                    @php
                                        $previousRevenue = $index > 0 ? $monthlyRevenue[$index-1]->revenue : 0;
                                        $growthRate = $previousRevenue > 0 ? round((($revenue->revenue - $previousRevenue) / $previousRevenue) * 100, 1) : 0;
                                        $avgPayment = $revenue->count > 0 ? $revenue->revenue / $revenue->count : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($revenue->month . '-01')->format('Y년 m월') }}</td>
                                        <td><strong>{{ number_format($revenue->count) }}</strong></td>
                                        <td><strong>{{ number_format($revenue->revenue) }}원</strong></td>
                                        <td>{{ number_format($avgPayment) }}원</td>
                                        <td>
                                            @if($growthRate > 0)
                                                <span class="text-success">+{{ $growthRate }}%</span>
                                            @elseif($growthRate < 0)
                                                <span class="text-danger">{{ $growthRate }}%</span>
                                            @else
                                                <span class="text-muted">0%</span>
                                            @endif
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

    <!-- 일별 활동 -->
    @if($dailyStats->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-calendar me-2"></i>일별 결제 활동
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>날짜</th>
                                    <th>결제 건수</th>
                                    <th>총 결제액</th>
                                    <th>평균 결제액</th>
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
                                        <td><strong>{{ number_format($stat->total_amount) }}원</strong></td>
                                        <td>{{ number_format($stat->avg_amount) }}원</td>
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
                        <i class="fe fe-clock me-2"></i>시간대별 결제 활동
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
    const filterElements = ['period', 'status', 'payment_method'];
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