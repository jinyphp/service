@extends('jiny-service::layouts.admin.sidebar')

@section('title', '결제 내역 관리')

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
                    <h1 class="h2 fw-bold text-dark">결제 내역 관리</h1>
                    <p class="text-muted mb-0">
                        총 {{ number_format($payments->total()) }}건의 결제 내역
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.service.payments.stats') }}" class="btn btn-info">
                        <i class="fe fe-bar-chart-2 me-2"></i>통계 보기
                    </a>
                    <a href="{{ route('admin.service.payments.export', array_filter(request()->all())) }}" class="btn btn-success">
                        <i class="fe fe-download me-2"></i>CSV 내보내기
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fe fe-credit-card fs-2"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format($stats['total']) }}</h4>
                    <small class="text-muted">전체 결제</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fe fe-check-circle fs-2"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format($stats['completed']) }}</h4>
                    <small class="text-muted">결제 완료 ({{ $stats['success_rate'] }}%)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fe fe-clock fs-2"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format($stats['pending']) }}</h4>
                    <small class="text-muted">결제 대기</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fe fe-dollar-sign fs-2"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format($stats['total_revenue']) }}</h4>
                    <small class="text-muted">총 수익 (원)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 검색 및 필터 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">검색</label>
                            <input type="text" id="search" name="search" class="form-control"
                                   placeholder="사용자, 결제ID, 거래ID"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">상태</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">전체</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="payment_method" class="form-label">결제 방법</label>
                            <select id="payment_method" name="payment_method" class="form-select">
                                <option value="">전체</option>
                                @foreach($paymentMethodOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('payment_method') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="payment_type" class="form-label">결제 유형</label>
                            <select id="payment_type" name="payment_type" class="form-select">
                                <option value="">전체</option>
                                @foreach($paymentTypeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('payment_type') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
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
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 결제 목록 -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-list me-2"></i>결제 목록
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>결제 정보</th>
                                        <th>사용자</th>
                                        <th>서비스</th>
                                        <th>금액</th>
                                        <th>결제 방법</th>
                                        <th>상태</th>
                                        <th>결제일시</th>
                                        <th>작업</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">
                                                    <a href="{{ route('admin.service.payments.show', $payment) }}" class="text-decoration-none">
                                                        #{{ $payment->id }}
                                                    </a>
                                                </div>
                                                <small class="text-muted">{{ $payment->payment_uuid }}</small>
                                                @if($payment->transaction_id)
                                                    <br><small class="text-muted">거래: {{ $payment->transaction_id }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->serviceUser)
                                                    <div class="fw-medium">{{ $payment->serviceUser->user_name }}</div>
                                                    <small class="text-muted">{{ $payment->serviceUser->user_email }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->service)
                                                    <span class="badge bg-light text-dark">{{ $payment->service->title }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ number_format($payment->final_amount) }} {{ $payment->currency }}</div>
                                                @if($payment->amount != $payment->final_amount)
                                                    <small class="text-muted">원가: {{ number_format($payment->amount) }}</small>
                                                @endif
                                                @if($payment->refunded_amount > 0)
                                                    <br><small class="text-danger">환불: {{ number_format($payment->refunded_amount) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $paymentMethodOptions[$payment->payment_method] ?? $payment->payment_method }}
                                                </span>
                                                @if($payment->payment_provider)
                                                    <br><small class="text-muted">{{ $payment->payment_provider }}</small>
                                                @endif
                                            </td>
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
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">
                                                    {{ $statusOptions[$payment->status] ?? $payment->status }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($payment->paid_at)
                                                    <div>{{ $payment->paid_at->format('Y-m-d') }}</div>
                                                    <small class="text-muted">{{ $payment->paid_at->format('H:i') }}</small>
                                                @else
                                                    <div>{{ $payment->created_at->format('Y-m-d') }}</div>
                                                    <small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.service.payments.show', $payment) }}"
                                                       class="btn btn-outline-primary" title="상세 보기">
                                                        <i class="fe fe-eye"></i>
                                                    </a>
                                                    @if($payment->serviceUser)
                                                        <a href="{{ route('admin.service.users.show', $payment->serviceUser) }}"
                                                           class="btn btn-outline-info" title="사용자 보기">
                                                            <i class="fe fe-user"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- 페이지네이션 -->
                        <div class="card-footer bg-white">
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-credit-card text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">결제 내역이 없습니다</h5>
                            <p class="text-muted">검색 조건을 변경해보세요.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 자동 검색 기능
    const searchInput = document.getElementById('search');
    const statusSelect = document.getElementById('status');
    const methodSelect = document.getElementById('payment_method');
    const typeSelect = document.getElementById('payment_type');
    const serviceSelect = document.getElementById('service_id');

    [statusSelect, methodSelect, typeSelect, serviceSelect].forEach(element => {
        if (element) {
            element.addEventListener('change', function() {
                this.form.submit();
            });
        }
    });

    // 검색 입력 지연 처리
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    }
});
</script>
@endpush