@extends('jiny-service::layouts.admin.sidebar')

@section('title', '서비스 구독자 편집')

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
                    <h1 class="h2 fw-bold text-dark">서비스 구독자 편집</h1>
                    <p class="text-muted mb-0">{{ $serviceUser->user_name }}님의 구독 정보를 수정합니다.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.service.users.index') }}"
                       class="btn btn-secondary">
                        <i class="fe fe-list me-2"></i>목록으로
                    </a>
                    <a href="{{ route('admin.service.users.show', $serviceUser->id) }}"
                       class="btn btn-info">
                        <i class="fe fe-eye me-2"></i>상세보기
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 오류 메시지 -->
    @if ($errors->any())
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="fe fe-alert-circle"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-2">입력 오류가 발생했습니다:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 폼 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.service.users.update', $serviceUser->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- 사용자 정보 섹션 -->
                        <div class="border-bottom pb-4 mb-4">
                            <h5 class="card-title">사용자 정보</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="user_uuid" class="form-label">사용자 UUID *</label>
                                    <input type="text"
                                           id="user_uuid"
                                           name="user_uuid"
                                           value="{{ old('user_uuid', $serviceUser->user_uuid) }}"
                                           class="form-control"
                                           readonly>
                                </div>

                                <div class="col-md-6">
                                    <label for="user_shard" class="form-label">사용자 샤드 *</label>
                                    <input type="text"
                                           id="user_shard"
                                           name="user_shard"
                                           value="{{ old('user_shard', $serviceUser->user_shard) }}"
                                           class="form-control"
                                           readonly>
                                </div>

                                <div class="col-md-6">
                                    <label for="user_id" class="form-label">사용자 ID *</label>
                                    <input type="number"
                                           id="user_id"
                                           name="user_id"
                                           value="{{ old('user_id', $serviceUser->user_id) }}"
                                           class="form-control"
                                           readonly>
                                </div>

                                <div class="col-md-6">
                                    <label for="user_email" class="form-label">이메일 *</label>
                                    <input type="email"
                                           id="user_email"
                                           name="user_email"
                                           value="{{ old('user_email', $serviceUser->user_email) }}"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-12">
                                    <label for="user_name" class="form-label">사용자 이름 *</label>
                                    <input type="text"
                                           id="user_name"
                                           name="user_name"
                                           value="{{ old('user_name', $serviceUser->user_name) }}"
                                           class="form-control"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- 서비스 및 구독 정보 섹션 -->
                        <div class="border-bottom pb-4 mb-4">
                            <h5 class="card-title">서비스 및 구독 정보</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="service_id" class="form-label">서비스 *</label>
                                    <select id="service_id"
                                            name="service_id"
                                            class="form-select"
                                            required>
                                        <option value="">서비스를 선택하세요</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ old('service_id', $serviceUser->service_id) == $service->id ? 'selected' : '' }}>
                                                {{ $service->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label">구독 상태 *</label>
                                    <select id="status"
                                            name="status"
                                            class="form-select"
                                            required>
                                        <option value="pending" {{ old('status', $serviceUser->status) == 'pending' ? 'selected' : '' }}>대기중</option>
                                        <option value="active" {{ old('status', $serviceUser->status) == 'active' ? 'selected' : '' }}>활성</option>
                                        <option value="suspended" {{ old('status', $serviceUser->status) == 'suspended' ? 'selected' : '' }}>일시정지</option>
                                        <option value="cancelled" {{ old('status', $serviceUser->status) == 'cancelled' ? 'selected' : '' }}>취소</option>
                                        <option value="expired" {{ old('status', $serviceUser->status) == 'expired' ? 'selected' : '' }}>만료</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="billing_cycle" class="form-label">청구 주기 *</label>
                                    <select id="billing_cycle"
                                            name="billing_cycle"
                                            class="form-select"
                                            required>
                                        <option value="monthly" {{ old('billing_cycle', $serviceUser->billing_cycle) == 'monthly' ? 'selected' : '' }}>월간</option>
                                        <option value="quarterly" {{ old('billing_cycle', $serviceUser->billing_cycle) == 'quarterly' ? 'selected' : '' }}>분기</option>
                                        <option value="yearly" {{ old('billing_cycle', $serviceUser->billing_cycle) == 'yearly' ? 'selected' : '' }}>연간</option>
                                        <option value="lifetime" {{ old('billing_cycle', $serviceUser->billing_cycle) == 'lifetime' ? 'selected' : '' }}>평생</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="payment_status" class="form-label">결제 상태 *</label>
                                    <select id="payment_status"
                                            name="payment_status"
                                            class="form-select"
                                            required>
                                        <option value="pending" {{ old('payment_status', $serviceUser->payment_status) == 'pending' ? 'selected' : '' }}>대기중</option>
                                        <option value="paid" {{ old('payment_status', $serviceUser->payment_status) == 'paid' ? 'selected' : '' }}>결제완료</option>
                                        <option value="failed" {{ old('payment_status', $serviceUser->payment_status) == 'failed' ? 'selected' : '' }}>결제실패</option>
                                        <option value="refunded" {{ old('payment_status', $serviceUser->payment_status) == 'refunded' ? 'selected' : '' }}>환불</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 구독 일정 섹션 -->
                        <div class="border-bottom pb-4 mb-4">
                            <h5 class="card-title">구독 일정</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="started_at" class="form-label">시작일</label>
                                    <input type="datetime-local"
                                           id="started_at"
                                           name="started_at"
                                           value="{{ old('started_at', $serviceUser->started_at ? $serviceUser->started_at->format('Y-m-d\TH:i') : '') }}"
                                           class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="expires_at" class="form-label">만료일</label>
                                    <input type="datetime-local"
                                           id="expires_at"
                                           name="expires_at"
                                           value="{{ old('expires_at', $serviceUser->expires_at ? $serviceUser->expires_at->format('Y-m-d\TH:i') : '') }}"
                                           class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="next_billing_at" class="form-label">다음 결제일</label>
                                    <input type="datetime-local"
                                           id="next_billing_at"
                                           name="next_billing_at"
                                           value="{{ old('next_billing_at', $serviceUser->next_billing_at ? $serviceUser->next_billing_at->format('Y-m-d\TH:i') : '') }}"
                                           class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- 플랜 및 가격 정보 섹션 -->
                        <div class="border-bottom pb-4 mb-4">
                            <h5 class="card-title">플랜 및 가격 정보</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="plan_name" class="form-label">플랜 이름</label>
                                    <input type="text"
                                           id="plan_name"
                                           name="plan_name"
                                           value="{{ old('plan_name', $serviceUser->plan_name) }}"
                                           class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="plan_price" class="form-label">플랜 가격</label>
                                    <input type="number"
                                           id="plan_price"
                                           name="plan_price"
                                           value="{{ old('plan_price', $serviceUser->plan_price) }}"
                                           step="0.01"
                                           min="0"
                                           class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="monthly_price" class="form-label">월 가격</label>
                                    <input type="number"
                                           id="monthly_price"
                                           name="monthly_price"
                                           value="{{ old('monthly_price', $serviceUser->monthly_price) }}"
                                           step="0.01"
                                           min="0"
                                           class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="total_paid" class="form-label">총 결제 금액</label>
                                    <input type="number"
                                           id="total_paid"
                                           name="total_paid"
                                           value="{{ old('total_paid', $serviceUser->total_paid) }}"
                                           step="0.01"
                                           min="0"
                                           class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">결제 방법</label>
                                    <input type="text"
                                           id="payment_method"
                                           name="payment_method"
                                           value="{{ old('payment_method', $serviceUser->payment_method) }}"
                                           class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- 환불 정보 섹션 -->
                        <div class="border-bottom pb-4 mb-4">
                            <h5 class="card-title">환불 정보</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="refund_amount" class="form-label">환불 금액</label>
                                    <input type="number"
                                           id="refund_amount"
                                           name="refund_amount"
                                           value="{{ old('refund_amount', $serviceUser->refund_amount) }}"
                                           step="0.01"
                                           min="0"
                                           class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="refunded_at" class="form-label">환불일</label>
                                    <input type="datetime-local"
                                           id="refunded_at"
                                           name="refunded_at"
                                           value="{{ old('refunded_at', $serviceUser->refunded_at ? $serviceUser->refunded_at->format('Y-m-d\TH:i') : '') }}"
                                           class="form-control">
                                </div>

                                <div class="col-12">
                                    <label for="cancel_reason" class="form-label">취소/환불 사유</label>
                                    <textarea id="cancel_reason"
                                              name="cancel_reason"
                                              rows="3"
                                              class="form-control">{{ old('cancel_reason', $serviceUser->cancel_reason) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 옵션 섹션 -->
                        <div class="border-bottom pb-4 mb-4">
                            <h5 class="card-title">옵션</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox"
                                               id="auto_renewal"
                                               name="auto_renewal"
                                               value="1"
                                               {{ old('auto_renewal', $serviceUser->auto_renewal) ? 'checked' : '' }}
                                               class="form-check-input">
                                        <label for="auto_renewal" class="form-check-label">
                                            자동 갱신
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox"
                                               id="auto_upgrade"
                                               name="auto_upgrade"
                                               value="1"
                                               {{ old('auto_upgrade', $serviceUser->auto_upgrade) ? 'checked' : '' }}
                                               class="form-check-input">
                                        <label for="auto_upgrade" class="form-check-label">
                                            자동 업그레이드
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 관리자 메모 섹션 -->
                        <div class="mb-4">
                            <h5 class="card-title">관리자 메모</h5>
                            <div>
                                <label for="admin_notes" class="form-label">메모</label>
                                <textarea id="admin_notes"
                                          name="admin_notes"
                                          rows="4"
                                          class="form-control">{{ old('admin_notes', $serviceUser->admin_notes) }}</textarea>
                            </div>
                        </div>

                        <!-- 버튼 -->
                        <div class="d-flex justify-content-end gap-2 pt-3">
                            <a href="{{ route('admin.service.users.show', $serviceUser->id) }}"
                               class="btn btn-secondary">
                                <i class="fe fe-x me-2"></i>취소
                            </a>
                            <button type="submit"
                                    class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>수정 저장
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection