@extends('jiny-service::layouts.admin.sidebar')

@section('title', '결제 상세 정보')

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
                    <h1 class="h2 fw-bold text-dark">결제 상세 정보</h1>
                    <p class="text-muted mb-0">결제 ID: {{ $payment->id }} - {{ $payment->created_at->format('Y년 m월 d일 H:i:s') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.service.payments.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                    @if($payment->serviceUser)
                        <a href="{{ route('admin.service.users.show', $payment->serviceUser->id) }}" class="btn btn-info">
                            <i class="fe fe-user me-2"></i>사용자 보기
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 결제 상세 정보 -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-credit-card me-2"></i>결제 상세 정보
                    </h6>
                </div>
                <div class="card-body">
                    <!-- 기본 정보 -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">기본 정보</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td width="120" class="text-muted">결제 ID</td>
                                    <td><strong>#{{ $payment->id }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">결제 UUID</td>
                                    <td><code>{{ $payment->payment_uuid }}</code></td>
                                </tr>
                                @if($payment->transaction_id)
                                <tr>
                                    <td class="text-muted">거래 ID</td>
                                    <td><code>{{ $payment->transaction_id }}</code></td>
                                </tr>
                                @endif
                                @if($payment->order_id)
                                <tr>
                                    <td class="text-muted">주문 ID</td>
                                    <td><code>{{ $payment->order_id }}</code></td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">상태</td>
                                    <td>
                                        @php
                                            $statusClass = match($payment->status) {
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'failed' => 'danger',
                                                'cancelled' => 'secondary',
                                                'refunded', 'partially_refunded' => 'dark',
                                                default => 'secondary'
                                            };
                                            $statusText = match($payment->status) {
                                                'pending' => '결제 대기',
                                                'processing' => '결제 진행 중',
                                                'completed' => '결제 완료',
                                                'failed' => '결제 실패',
                                                'cancelled' => '결제 취소',
                                                'refunded' => '환불 완료',
                                                'partially_refunded' => '부분 환불',
                                                default => $payment->status
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">결제 유형</td>
                                    <td>
                                        @php
                                            $typeText = match($payment->payment_type) {
                                                'subscription' => '정기 구독',
                                                'one_time' => '일회성 결제',
                                                'upgrade' => '업그레이드',
                                                'extension' => '연장',
                                                'late_fee' => '연체료',
                                                'setup_fee' => '설치비',
                                                default => $payment->payment_type
                                            };
                                        @endphp
                                        <span class="badge bg-primary">{{ $typeText }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">사용자 정보</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td width="120" class="text-muted">사용자 UUID</td>
                                    <td><code>{{ $payment->user_uuid }}</code></td>
                                </tr>
                                @if($payment->serviceUser)
                                    <tr>
                                        <td class="text-muted">사용자명</td>
                                        <td><strong>{{ $payment->serviceUser->user_name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">이메일</td>
                                        <td>{{ $payment->serviceUser->user_email }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">서비스</td>
                                    <td>
                                        @if($payment->service)
                                            <span class="badge bg-light text-dark">{{ $payment->service->title }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- 결제 금액 정보 -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">결제 금액 정보</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="text-muted">기본 금액</td>
                                            <td class="text-end">{{ number_format($payment->amount) }} {{ $payment->currency }}</td>
                                        </tr>
                                        @if($payment->tax_amount > 0)
                                        <tr>
                                            <td class="text-muted">세금</td>
                                            <td class="text-end">{{ number_format($payment->tax_amount) }} {{ $payment->currency }}</td>
                                        </tr>
                                        @endif
                                        @if($payment->discount_amount > 0)
                                        <tr>
                                            <td class="text-muted">할인</td>
                                            <td class="text-end text-success">-{{ number_format($payment->discount_amount) }} {{ $payment->currency }}</td>
                                        </tr>
                                        @endif
                                        <tr class="table-primary">
                                            <td class="fw-bold">최종 결제 금액</td>
                                            <td class="text-end fw-bold">{{ number_format($payment->final_amount) }} {{ $payment->currency }}</td>
                                        </tr>
                                        @if($payment->refunded_amount > 0)
                                        <tr>
                                            <td class="text-muted">환불 금액</td>
                                            <td class="text-end text-danger">{{ number_format($payment->refunded_amount) }} {{ $payment->currency }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 결제 방법 정보 -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">결제 방법 정보</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="text-muted">결제 방법</td>
                                            <td>
                                                @php
                                                    $methodText = match($payment->payment_method) {
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
                                                @endphp
                                                <span class="badge bg-secondary">{{ $methodText }}</span>
                                            </td>
                                        </tr>
                                        @if($payment->payment_provider)
                                        <tr>
                                            <td class="text-muted">결제 제공업체</td>
                                            <td>{{ $payment->payment_provider }}</td>
                                        </tr>
                                        @endif
                                        @if($payment->billing_cycle)
                                        <tr>
                                            <td class="text-muted">청구 주기</td>
                                            <td>
                                                @php
                                                    $cycleText = match($payment->billing_cycle) {
                                                        'monthly' => '월간',
                                                        'quarterly' => '분기',
                                                        'yearly' => '연간',
                                                        'lifetime' => '평생',
                                                        default => $payment->billing_cycle
                                                    };
                                                @endphp
                                                {{ $cycleText }}
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 결제 일시 정보 -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">결제 일시 정보</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="text-muted">생성일시</td>
                                            <td>{{ $payment->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @if($payment->paid_at)
                                        <tr>
                                            <td class="text-muted">결제 완료일시</td>
                                            <td>{{ $payment->paid_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @endif
                                        @if($payment->refunded_at)
                                        <tr>
                                            <td class="text-muted">환불일시</td>
                                            <td>{{ $payment->refunded_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @endif
                                        @if($payment->due_date)
                                        <tr>
                                            <td class="text-muted">결제 마감일</td>
                                            <td>{{ $payment->due_date->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 실패 정보 (실패한 경우만) -->
                    @if($payment->status === 'failed' && ($payment->failure_code || $payment->failure_message))
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">실패 정보</h6>
                            <div class="alert alert-danger">
                                @if($payment->failure_code)
                                    <div><strong>실패 코드:</strong> {{ $payment->failure_code }}</div>
                                @endif
                                @if($payment->failure_message)
                                    <div><strong>실패 메시지:</strong> {{ $payment->failure_message }}</div>
                                @endif
                                <div><strong>재시도 횟수:</strong> {{ $payment->retry_count }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 환불 정보 (환불된 경우만) -->
                    @if($payment->refunded_amount > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">환불 정보</h6>
                            <div class="alert alert-warning">
                                <div><strong>환불 금액:</strong> {{ number_format($payment->refunded_amount) }} {{ $payment->currency }}</div>
                                @if($payment->refund_reason)
                                    <div><strong>환불 사유:</strong> {{ $payment->refund_reason }}</div>
                                @endif
                                @if($payment->refund_transaction_id)
                                    <div><strong>환불 거래 ID:</strong> {{ $payment->refund_transaction_id }}</div>
                                @endif
                                @if($payment->refunded_at)
                                    <div><strong>환불일시:</strong> {{ $payment->refunded_at->format('Y-m-d H:i:s') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 추가 메타데이터 -->
                    @if($payment->metadata)
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">추가 정보</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0"><code>{{ json_encode($payment->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 사이드바 -->
        <div class="col-lg-4">
            <!-- 빠른 작업 -->
            @if($canRefund || $canRetry)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-settings me-2"></i>빠른 작업
                    </h6>
                </div>
                <div class="card-body">
                    @if($canRefund)
                        <button class="btn btn-warning btn-sm mb-2 w-100" onclick="processRefund()">
                            <i class="fe fe-rotate-ccw me-2"></i>환불 처리
                        </button>
                    @endif
                    @if($canRetry)
                        <button class="btn btn-info btn-sm mb-2 w-100" onclick="retryPayment()">
                            <i class="fe fe-refresh-cw me-2"></i>결제 재시도
                        </button>
                    @endif
                </div>
            </div>
            @endif

            <!-- 동일 사용자 결제 내역 -->
            @if($relatedPayments->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-user me-2"></i>동일 사용자 최근 결제
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($relatedPayments as $relatedPayment)
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ number_format($relatedPayment->final_amount) }} {{ $relatedPayment->currency }}</div>
                                <small class="text-muted">{{ $relatedPayment->service->title ?? 'N/A' }}</small>
                                <br><small class="text-muted">{{ $relatedPayment->created_at->format('m/d H:i') }}</small>
                            </div>
                            <div class="ms-2">
                                @php
                                    $statusClass = match($relatedPayment->status) {
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ $relatedPayment->status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                    <div class="text-center">
                        <a href="{{ route('admin.service.payments.index', ['search' => $payment->user_uuid]) }}" class="btn btn-sm btn-outline-primary">
                            전체 보기
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- 동일 서비스 결제 내역 -->
            @if($servicePayments->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-layers me-2"></i>동일 서비스 최근 결제
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($servicePayments as $servicePayment)
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ $servicePayment->serviceUser->user_name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ number_format($servicePayment->final_amount) }} {{ $servicePayment->currency }}</small>
                                <br><small class="text-muted">{{ $servicePayment->created_at->format('m/d H:i') }}</small>
                            </div>
                            <div class="ms-2">
                                @php
                                    $statusClass = match($servicePayment->status) {
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ $servicePayment->status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                    <div class="text-center">
                        <a href="{{ route('admin.service.payments.index', ['service_id' => $payment->service_id]) }}" class="btn btn-sm btn-outline-primary">
                            전체 보기
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 환불 처리 함수
    window.processRefund = function() {
        if (confirm('정말로 환불 처리하시겠습니까?')) {
            // 여기에 환불 처리 로직 구현
            alert('환불 처리 기능은 추후 구현 예정입니다.');
        }
    };

    // 결제 재시도 함수
    window.retryPayment = function() {
        if (confirm('결제를 재시도하시겠습니까?')) {
            // 여기에 재시도 로직 구현
            alert('결제 재시도 기능은 추후 구현 예정입니다.');
        }
    };
});
</script>
@endpush