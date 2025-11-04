@extends('jiny-service::layouts.admin.sidebar')

@section('title', '서비스 구독자 상세 정보')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- 헤더 -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">서비스 구독자 상세 정보</h1>
                <p class="text-gray-600 mt-1">{{ $serviceUser->user_name }}님의 구독 정보를 확인합니다.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.service.users.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    목록으로
                </a>
                <a href="{{ route('admin.service.users.edit', $serviceUser->id) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    편집
                </a>
            </div>
        </div>
    </div>

    <!-- 상태 카드 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">구독 상태</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            @switch($serviceUser->status)
                                @case('active')
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">활성</span>
                                    @break
                                @case('pending')
                                    <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">대기중</span>
                                    @break
                                @case('suspended')
                                    <span class="px-2 py-1 text-xs font-semibold bg-orange-100 text-orange-800 rounded-full">일시정지</span>
                                    @break
                                @case('cancelled')
                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">취소</span>
                                    @break
                                @case('expired')
                                    <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">만료</span>
                                    @break
                            @endswitch
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">결제 상태</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            @switch($serviceUser->payment_status)
                                @case('paid')
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">결제완료</span>
                                    @break
                                @case('pending')
                                    <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">대기중</span>
                                    @break
                                @case('failed')
                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">실패</span>
                                    @break
                                @case('refunded')
                                    <span class="px-2 py-1 text-xs font-semibold bg-orange-100 text-orange-800 rounded-full">환불</span>
                                    @break
                            @endswitch
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">청구 주기</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            @switch($serviceUser->billing_cycle)
                                @case('monthly') 월간 @break
                                @case('quarterly') 분기 @break
                                @case('yearly') 연간 @break
                                @case('lifetime') 평생 @break
                            @endswitch
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">총 결제금액</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            ₩{{ number_format($serviceUser->total_paid ?? 0) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- 메인 정보 그리드 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- 사용자 정보 -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">사용자 정보</h3>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">이름</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $serviceUser->user_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">이메일</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $serviceUser->user_email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">사용자 ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $serviceUser->user_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">사용자 샤드</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $serviceUser->user_shard }}</dd>
                    </div>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">UUID</dt>
                    <dd class="mt-1 text-sm text-gray-900 break-all">{{ $serviceUser->user_uuid }}</dd>
                </div>
            </div>
        </div>

        <!-- 서비스 정보 -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">서비스 정보</h3>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">서비스</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $serviceUser->service->title ?? $serviceUser->service_title }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">플랜</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $serviceUser->plan_name ?? '미설정' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">플랜 가격</dt>
                        <dd class="mt-1 text-sm text-gray-900">₩{{ number_format($serviceUser->plan_price ?? 0) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">월 가격</dt>
                        <dd class="mt-1 text-sm text-gray-900">₩{{ number_format($serviceUser->monthly_price ?? 0) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">결제 방법</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $serviceUser->payment_method ?? '미설정' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">자동 갱신</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($serviceUser->auto_renewal)
                                <span class="text-green-600">활성</span>
                            @else
                                <span class="text-gray-500">비활성</span>
                            @endif
                        </dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 구독 일정 정보 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">구독 일정</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">시작일</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $serviceUser->started_at ? $serviceUser->started_at->format('Y-m-d H:i') : '미설정' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">만료일</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $serviceUser->expires_at ? $serviceUser->expires_at->format('Y-m-d H:i') : '미설정' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">다음 결제일</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $serviceUser->next_billing_at ? $serviceUser->next_billing_at->format('Y-m-d H:i') : '미설정' }}
                    </dd>
                </div>
            </div>
        </div>
    </div>

    <!-- 관리 작업 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">관리 작업</h3>
        </div>
        <div class="px-6 py-4">
            <div class="flex flex-wrap gap-3">
                @if(in_array($serviceUser->status, ['pending', 'suspended']))
                    <form action="{{ route('admin.service.users.activate', $serviceUser->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                            활성화
                        </button>
                    </form>
                @endif

                @if($serviceUser->status === 'active')
                    <button type="button" onclick="openSuspendModal()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        일시정지
                    </button>
                @endif

                @if(in_array($serviceUser->status, ['active', 'suspended', 'pending']))
                    <button type="button" onclick="openCancelModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                        구독 취소
                    </button>
                @endif

                <button type="button" onclick="openExtendModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    기간 연장
                </button>

                <form action="{{ route('admin.service.users.update-cache', $serviceUser->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        캐시 업데이트
                    </button>
                </form>

                @if($serviceUser->status !== 'active')
                    <form action="{{ route('admin.service.users.destroy', $serviceUser->id) }}" method="POST" class="inline" onsubmit="return confirm('정말로 삭제하시겠습니까?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                            삭제
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- 관리자 메모 -->
    @if($serviceUser->admin_notes)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">관리자 메모</h3>
        </div>
        <div class="px-6 py-4">
            <pre class="whitespace-pre-wrap text-sm text-gray-700">{{ $serviceUser->admin_notes }}</pre>
        </div>
    </div>
    @endif

    <!-- 환불 정보 -->
    @if($serviceUser->refund_amount || $serviceUser->refunded_at)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">환불 정보</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">환불 금액</dt>
                    <dd class="mt-1 text-sm text-gray-900">₩{{ number_format($serviceUser->refund_amount ?? 0) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">환불일</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $serviceUser->refunded_at ? $serviceUser->refunded_at->format('Y-m-d H:i') : '미설정' }}
                    </dd>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- 일시정지 모달 -->
<div id="suspendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">구독 일시정지</h3>
            <form action="{{ route('admin.service.users.suspend', $serviceUser->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="suspend_reason" class="block text-sm font-medium text-gray-700 mb-2">정지 사유</label>
                    <textarea id="suspend_reason" name="reason" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="정지 사유를 입력하세요"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeSuspendModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        취소
                    </button>
                    <button type="submit"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                        일시정지
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 취소 모달 -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">구독 취소</h3>
            <form action="{{ route('admin.service.users.cancel', $serviceUser->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="cancel_reason" class="block text-sm font-medium text-gray-700 mb-2">취소 사유</label>
                    <textarea id="cancel_reason" name="reason" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                              placeholder="취소 사유를 입력하세요"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCancelModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        취소
                    </button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                        구독 취소
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 연장 모달 -->
<div id="extendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">기간 연장</h3>
            <form action="{{ route('admin.service.users.extend', $serviceUser->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="extend_days" class="block text-sm font-medium text-gray-700 mb-2">연장 일수</label>
                    <input type="number" id="extend_days" name="days" min="1" max="365"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="연장할 일수를 입력하세요" required>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeExtendModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        취소
                    </button>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        연장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSuspendModal() {
    document.getElementById('suspendModal').classList.remove('hidden');
}

function closeSuspendModal() {
    document.getElementById('suspendModal').classList.add('hidden');
}

function openCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}

function openExtendModal() {
    document.getElementById('extendModal').classList.remove('hidden');
}

function closeExtendModal() {
    document.getElementById('extendModal').classList.add('hidden');
}

// 모달 외부 클릭 시 닫기
document.addEventListener('click', function(event) {
    const modals = ['suspendModal', 'cancelModal', 'extendModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>
@endsection
