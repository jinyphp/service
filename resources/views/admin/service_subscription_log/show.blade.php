@extends('jiny-service::layouts.admin.sidebar')

@section('title', '구독 로그 상세')

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
                    <h1 class="h2 fw-bold text-dark">구독 로그 상세</h1>
                    <p class="text-muted mb-0">로그 ID: {{ $log->id }} - {{ $log->created_at->format('Y년 m월 d일 H:i:s') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.service.subscription-logs.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                    @if($log->serviceUser)
                        <a href="{{ route('admin.service.users.show', $log->serviceUser->id) }}" class="btn btn-info">
                            <i class="fe fe-user me-2"></i>사용자 보기
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 로그 상세 정보 -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-info me-2"></i>로그 상세 정보
                    </h6>
                </div>
                <div class="card-body">
                    <!-- 기본 정보 -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">기본 정보</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td width="120" class="text-muted">로그 ID</td>
                                    <td><strong>#{{ $log->id }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">액션</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $log->action }}</span>
                                        @if($log->action_title)
                                            <span class="ms-2">{{ $log->action_title }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">결과</td>
                                    <td>
                                        <span class="badge bg-{{ $log->result === 'success' ? 'success' : ($log->result === 'failed' ? 'danger' : 'warning') }}">
                                            {{ $log->result === 'success' ? '성공' : ($log->result === 'failed' ? '실패' : '대기') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">처리자</td>
                                    <td>
                                        <span class="badge bg-{{ $log->processed_by === 'admin' ? 'warning' : ($log->processed_by === 'user' ? 'info' : 'secondary') }}">
                                            {{ $log->processed_by === 'admin' ? '관리자' : ($log->processed_by === 'user' ? '사용자' : '시스템') }}
                                        </span>
                                        @if($log->processor_name)
                                            <span class="ms-2">{{ $log->processor_name }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">일시</td>
                                    <td>
                                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                                        <small class="text-muted">({{ $log->created_at->diffForHumans() }})</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">사용자 정보</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td width="120" class="text-muted">사용자 UUID</td>
                                    <td><code>{{ $log->user_uuid }}</code></td>
                                </tr>
                                @if($log->serviceUser)
                                    <tr>
                                        <td class="text-muted">사용자명</td>
                                        <td><strong>{{ $log->serviceUser->user_name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">이메일</td>
                                        <td>{{ $log->serviceUser->user_email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">샤드</td>
                                        <td><code>{{ $log->serviceUser->user_shard }}</code></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">서비스</td>
                                    <td>
                                        @if($log->service)
                                            <span class="badge bg-light text-dark">{{ $log->service->title }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- 액션 설명 -->
                    @if($log->action_description)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">액션 설명</h6>
                            <div class="alert alert-light">
                                <i class="fe fe-message-square me-2"></i>
                                {{ $log->action_description }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 상태 변경 정보 -->
                    @if($log->status_before || $log->status_after || $log->plan_before || $log->plan_after)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">변경 정보</h6>
                            <div class="row">
                                @if($log->status_before || $log->status_after)
                                <div class="col-md-6">
                                    <label class="form-label">상태 변경</label>
                                    <div class="d-flex align-items-center">
                                        @if($log->status_before)
                                            <span class="badge bg-secondary">{{ $log->status_before }}</span>
                                        @endif
                                        @if($log->status_before && $log->status_after)
                                            <i class="fe fe-arrow-right mx-3 text-muted"></i>
                                        @endif
                                        @if($log->status_after)
                                            <span class="badge bg-primary">{{ $log->status_after }}</span>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if($log->plan_before || $log->plan_after)
                                <div class="col-md-6">
                                    <label class="form-label">플랜 변경</label>
                                    <div class="d-flex align-items-center">
                                        @if($log->plan_before)
                                            <span class="badge bg-secondary">{{ $log->plan_before }}</span>
                                        @endif
                                        @if($log->plan_before && $log->plan_after)
                                            <i class="fe fe-arrow-right mx-3 text-muted"></i>
                                        @endif
                                        @if($log->plan_after)
                                            <span class="badge bg-primary">{{ $log->plan_after }}</span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 금액 정보 -->
                    @if($log->amount != 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">금액 정보</h6>
                            <div class="alert alert-{{ $log->amount > 0 ? 'success' : 'warning' }}">
                                <div class="d-flex align-items-center">
                                    <i class="fe fe-{{ $log->amount > 0 ? 'plus' : 'minus' }}-circle me-2"></i>
                                    <span class="fs-5 fw-bold">
                                        {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount) }} {{ $log->currency }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 만료일 변경 정보 -->
                    @if($log->expires_before || $log->expires_after)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">만료일 변경</h6>
                            <div class="d-flex align-items-center">
                                @if($log->expires_before)
                                    <div class="text-center">
                                        <div class="text-muted small">이전</div>
                                        <div>{{ $log->expires_before->format('Y-m-d H:i') }}</div>
                                    </div>
                                @endif
                                @if($log->expires_before && $log->expires_after)
                                    <i class="fe fe-arrow-right mx-4 text-muted"></i>
                                @endif
                                @if($log->expires_after)
                                    <div class="text-center">
                                        <div class="text-muted small">이후</div>
                                        <div class="fw-bold">{{ $log->expires_after->format('Y-m-d H:i') }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 오류 메시지 -->
                    @if($log->error_message)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">오류 정보</h6>
                            <div class="alert alert-danger">
                                <i class="fe fe-alert-triangle me-2"></i>
                                {{ $log->error_message }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 추가 데이터 -->
                    @if($log->action_data)
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">추가 데이터</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0"><code>{{ json_encode($log->action_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 사이드바 -->
        <div class="col-lg-4">
            <!-- 기술적 정보 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-settings me-2"></i>기술적 정보
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        @if($log->ip_address)
                        <tr>
                            <td class="text-muted">IP 주소</td>
                            <td><code>{{ $log->ip_address }}</code></td>
                        </tr>
                        @endif
                        @if($log->processor_id)
                        <tr>
                            <td class="text-muted">처리자 ID</td>
                            <td><code>{{ $log->processor_id }}</code></td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">생성일시</td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">수정일시</td>
                            <td>{{ $log->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- 관련 로그 -->
            @if($relatedLogs->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-user me-2"></i>동일 사용자 최근 로그
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($relatedLogs as $relatedLog)
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ $relatedLog->action_title ?: $relatedLog->action }}</div>
                                <small class="text-muted">{{ $relatedLog->created_at->format('m/d H:i') }}</small>
                            </div>
                            <div class="ms-2">
                                <span class="badge bg-{{ $relatedLog->result === 'success' ? 'success' : ($relatedLog->result === 'failed' ? 'danger' : 'warning') }}">
                                    {{ $relatedLog->result === 'success' ? '성공' : ($relatedLog->result === 'failed' ? '실패' : '대기') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                    <div class="text-center">
                        <a href="{{ route('admin.service.subscription-logs.index', ['user_uuid' => $log->user_uuid]) }}" class="btn btn-sm btn-outline-primary">
                            전체 보기
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- 유사 액션 -->
            @if($similarLogs->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fe fe-repeat me-2"></i>유사 액션 최근 로그
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($similarLogs as $similarLog)
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ $similarLog->serviceUser->user_name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $similarLog->created_at->format('m/d H:i') }}</small>
                            </div>
                            <div class="ms-2">
                                <span class="badge bg-{{ $similarLog->result === 'success' ? 'success' : ($similarLog->result === 'failed' ? 'danger' : 'warning') }}">
                                    {{ $similarLog->result === 'success' ? '성공' : ($similarLog->result === 'failed' ? '실패' : '대기') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                    <div class="text-center">
                        <a href="{{ route('admin.service.subscription-logs.index', ['action' => $log->action]) }}" class="btn btn-sm btn-outline-primary">
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
    // User-Agent 정보가 있다면 툴팁으로 표시
    @if($log->user_agent)
    const userAgentInfo = {!! json_encode($log->user_agent) !!};
    // 툴팁 또는 모달로 User-Agent 정보 표시할 수 있음
    @endif
});
</script>
@endpush