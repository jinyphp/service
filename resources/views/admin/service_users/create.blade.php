@extends('jiny-service::layouts.admin.sidebar')

@section('title', '새 서비스 구독자 추가')

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
                    <h1 class="h2 fw-bold text-dark">새 서비스 구독자 추가</h1>
                    <p class="text-muted mb-0">새로운 서비스 구독자를 등록합니다.</p>
                </div>
                <a href="{{ route('admin.service.users.index') }}"
                   class="btn btn-secondary">
                    <i class="fe fe-arrow-left me-2"></i>목록으로 돌아가기
                </a>
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
                    <form action="{{ route('admin.service.users.store') }}" method="POST">
                        @csrf

                        <!-- 사용자 검색 섹션 -->
                        <div class="border-bottom pb-4 mb-4">
                            <h5 class="card-title">사용자 검색</h5>
                            <div class="alert alert-info" role="alert">
                                <p class="mb-3">
                                    <i class="fe fe-info-circle me-2"></i>
                                    이메일 주소를 입력하면 샤드 회원 목록에서 자동으로 사용자 정보를 검색하여 입력합니다.
                                </p>

                                <div class="row g-3">
                                    <div class="col">
                                        <input type="email"
                                               id="search_email"
                                               class="form-control"
                                               placeholder="검색할 이메일 주소를 입력하세요">
                                    </div>
                                    <div class="col-auto">
                                        <button type="button"
                                                id="search_user_btn"
                                                class="btn btn-primary">
                                            <span class="btn-text">사용자 검색</span>
                                            <span class="btn-loading d-none">
                                                <i class="fe fe-loader spinner-border spinner-border-sm me-1" role="status"></i>검색 중...
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <!-- 검색 결과 표시 영역 -->
                                <div id="search_result" class="mt-3 d-none"></div>
                            </div>
                        </div>

                        <!-- 사용자 정보 섹션 -->
                        <div class="border-bottom pb-4 mb-4">
                            <h5 class="card-title">
                                사용자 정보
                                <small class="text-muted">(검색으로 자동 입력되거나 직접 입력)</small>
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="user_email" class="form-label">이메일 *</label>
                                    <input type="email"
                                           id="user_email"
                                           name="user_email"
                                           value="{{ old('user_email') }}"
                                           placeholder="user@example.com"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label for="user_name" class="form-label">사용자 이름 *</label>
                                    <input type="text"
                                           id="user_name"
                                           name="user_name"
                                           value="{{ old('user_name') }}"
                                           placeholder="사용자 이름"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label for="user_uuid" class="form-label">사용자 UUID *</label>
                                    <input type="text"
                                           id="user_uuid"
                                           name="user_uuid"
                                           value="{{ old('user_uuid') }}"
                                           placeholder="예: 550e8400-e29b-41d4-a716-446655440000"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label for="user_shard" class="form-label">사용자 샤드 *</label>
                                    <input type="text"
                                           id="user_shard"
                                           name="user_shard"
                                           value="{{ old('user_shard') }}"
                                           placeholder="예: user_001"
                                           class="form-control"
                                           required>
                                </div>

                                <div class="col-12">
                                    <label for="user_id" class="form-label">사용자 ID *</label>
                                    <input type="number"
                                           id="user_id"
                                           name="user_id"
                                           value="{{ old('user_id') }}"
                                           placeholder="숫자 ID"
                                           class="form-control"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- 서비스 계층 선택 섹션 -->
                        <div class="border-bottom pb-4 mb-4" data-section="service-info">
                            <h5 class="card-title">서비스 선택</h5>
                            <p class="text-muted mb-4">
                                단계별로 서비스를 선택하면 자동으로 가격이 계산됩니다.
                            </p>

                            <!-- 1단계: 서비스 카테고리 선택 -->
                            <div class="mb-4">
                                <label for="service_category" class="form-label">
                                    <span class="badge bg-primary me-2">1</span>서비스 카테고리 선택 *
                                </label>
                                <select id="service_category"
                                        class="form-select"
                                        required>
                                    <option value="">카테고리를 선택하세요</option>
                                </select>
                            </div>

                            <!-- 2단계: 서비스 선택 -->
                            <div class="mb-4">
                                <label for="service_id" class="form-label">
                                    <span class="badge bg-primary me-2">2</span>서비스 선택 *
                                </label>
                                <select id="service_id"
                                        name="service_id"
                                        class="form-select"
                                        required
                                        disabled>
                                    <option value="">먼저 카테고리를 선택하세요</option>
                                </select>
                            </div>

                            <!-- 3단계: 플랜 선택 -->
                            <div class="mb-4">
                                <label for="service_plan" class="form-label">
                                    <span class="badge bg-primary me-2">3</span>플랜 선택 *
                                </label>
                                <select id="service_plan"
                                        class="form-select"
                                        required
                                        disabled>
                                    <option value="">먼저 서비스를 선택하세요</option>
                                </select>
                                <!-- 플랜 설명 -->
                                <div id="plan_description" class="d-none mt-2">
                                    <div class="alert alert-info">
                                        <p class="mb-0"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- 4단계: 청구 주기 선택 -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <span class="badge bg-primary me-2">4</span>청구 주기 선택 *
                                </label>
                                <div id="billing_options">
                                    <p class="text-muted">먼저 플랜을 선택하세요</p>
                                </div>
                                <input type="hidden" id="billing_cycle" name="billing_cycle" required>
                            </div>

                            <!-- 가격 계산 결과 -->
                            <div id="price_summary" class="d-none">
                                <div class="alert alert-success">
                                    <h6 class="alert-heading">선택한 구독 정보</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>카테고리:</strong> <span id="selected_category_name">-</span></p>
                                            <p class="mb-1"><strong>서비스:</strong> <span id="selected_service_name">-</span></p>
                                            <p class="mb-1"><strong>플랜:</strong> <span id="selected_plan_name">-</span></p>
                                            <p class="mb-0"><strong>청구 주기:</strong> <span id="selected_cycle_name">-</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>가격:</strong> <span id="calculated_price" class="h5 text-success">₩0</span></p>
                                            <p id="setup_fee_display" class="d-none mb-1"><strong>설치비:</strong> <span id="setup_fee_amount">₩0</span></p>
                                            <p id="total_display" class="d-none mb-1"><strong>총 금액:</strong> <span id="total_amount" class="h5 text-success">₩0</span></p>
                                            <p id="discount_display" class="d-none mb-0 text-primary"><strong>할인:</strong> <span id="discount_info">-</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 히든 필드들 (자동 계산으로 설정됨) -->
                            <input type="hidden" id="plan_name" name="plan_name">
                            <input type="hidden" id="plan_price" name="plan_price">
                            <input type="hidden" id="monthly_price" name="monthly_price">
                            <input type="hidden" id="service_title" name="service_title">

                            <!-- 기타 구독 정보 -->
                            <div class="row g-3 mt-3">
                                <div class="col-md-4">
                                    <label for="status" class="form-label">구독 상태 *</label>
                                    <select id="status"
                                            name="status"
                                            class="form-select"
                                            required>
                                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>대기중</option>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>활성</option>
                                        <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>일시정지</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>취소</option>
                                        <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>만료</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="payment_status" class="form-label">결제 상태 *</label>
                                    <select id="payment_status"
                                            name="payment_status"
                                            class="form-select"
                                            required>
                                        <option value="pending" {{ old('payment_status', 'pending') == 'pending' ? 'selected' : '' }}>대기중</option>
                                        <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>결제완료</option>
                                        <option value="failed" {{ old('payment_status') == 'failed' ? 'selected' : '' }}>결제실패</option>
                                        <option value="refunded" {{ old('payment_status') == 'refunded' ? 'selected' : '' }}>환불</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="payment_method" class="form-label">결제 방법</label>
                                    <input type="text"
                                           id="payment_method"
                                           name="payment_method"
                                           value="{{ old('payment_method') }}"
                                           placeholder="예: 신용카드, 계좌이체"
                                           class="form-control">
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
                                               {{ old('auto_renewal') ? 'checked' : '' }}
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
                                               {{ old('auto_upgrade') ? 'checked' : '' }}
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
                                          placeholder="관리자 메모를 입력하세요..."
                                          class="form-control">{{ old('admin_notes') }}</textarea>
                            </div>
                        </div>

                        <!-- 버튼 -->
                        <div class="d-flex justify-content-end gap-2 pt-3">
                            <a href="{{ route('admin.service.users.index') }}"
                               class="btn btn-secondary">
                                <i class="fe fe-x me-2"></i>취소
                            </a>
                            <button type="submit"
                                    class="btn btn-primary">
                                <i class="fe fe-plus me-2"></i>서비스 구독자 추가
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF 토큰 가져오기
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                 document.querySelector('input[name="_token"]')?.value;

    // ===========================================
    // 사용자 검색 기능
    // ===========================================
    const searchEmailInput = document.getElementById('search_email');
    const searchBtn = document.getElementById('search_user_btn');
    const searchResult = document.getElementById('search_result');
    const btnText = searchBtn.querySelector('.btn-text');
    const btnLoading = searchBtn.querySelector('.btn-loading');

    // 검색 버튼 클릭 이벤트
    searchBtn.addEventListener('click', function() {
        const email = searchEmailInput.value.trim();
        if (!email) {
            showError('이메일 주소를 입력해주세요.');
            return;
        }
        searchUser(email);
    });

    // 검색 입력창에서 엔터 키 이벤트
    searchEmailInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBtn.click();
        }
    });

    // 사용자 검색 함수
    function searchUser(email) {
        setLoading(true);
        hideResult();

        fetch('{{ route("admin.service.users.search.email") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            setLoading(false);
            if (data.success) {
                showUserResult(data.user);
                fillUserForm(data.user);
            } else {
                showError(data.message || '사용자를 찾을 수 없습니다.');
            }
        })
        .catch(error => {
            setLoading(false);
            console.error('Search error:', error);
            showError('검색 중 오류가 발생했습니다. 다시 시도해주세요.');
        });
    }

    function setLoading(loading) {
        if (loading) {
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            searchBtn.disabled = true;
        } else {
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
            searchBtn.disabled = false;
        }
    }

    function showUserResult(user) {
        const html = `
            <div class="alert alert-success" role="alert">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="fe fe-check-circle"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="alert-heading">사용자를 찾았습니다!</h6>
                        <div class="small">
                            <p class="mb-1"><strong>이름:</strong> ${user.user_name}</p>
                            <p class="mb-1"><strong>이메일:</strong> ${user.user_email}</p>
                            <p class="mb-1"><strong>샤드:</strong> ${user.user_shard}</p>
                            <p class="mb-2"><strong>UUID:</strong> ${user.user_uuid || '없음'}</p>
                        </div>
                        <div class="mt-2">
                            <button type="button" onclick="fillUserForm(${JSON.stringify(user).replace(/"/g, '&quot;')})"
                                    class="btn btn-success btn-sm">
                                폼에 자동 입력
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        searchResult.innerHTML = html;
        searchResult.classList.remove('d-none');
    }

    function showError(message) {
        const html = `
            <div class="alert alert-danger" role="alert">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="fe fe-alert-circle"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="alert-heading">검색 실패</h6>
                        <p class="mb-0">${message}</p>
                    </div>
                </div>
            </div>
        `;
        searchResult.innerHTML = html;
        searchResult.classList.remove('d-none');
    }

    function hideResult() {
        searchResult.classList.add('d-none');
    }

    // 사용자 정보를 폼에 자동 입력
    window.fillUserForm = function(user) {
        document.getElementById('user_email').value = user.user_email || '';
        document.getElementById('user_name').value = user.user_name || '';
        document.getElementById('user_uuid').value = user.user_uuid || '';
        document.getElementById('user_shard').value = user.user_shard || '';
        document.getElementById('user_id').value = user.user_id || '';

        const fields = ['user_email', 'user_name', 'user_uuid', 'user_shard', 'user_id'];
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && field.value) {
                field.classList.add('is-valid');
                setTimeout(() => {
                    field.classList.remove('is-valid');
                }, 2000);
            }
        });

        showSuccessMessage('사용자 정보가 자동으로 입력되었습니다!');
        searchEmailInput.value = '';

        setTimeout(() => {
            const serviceSection = document.querySelector('[data-section="service-info"]');
            if (serviceSection) {
                serviceSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 1000);
    };

    function showSuccessMessage(message) {
        const existingMsg = document.querySelector('.auto-success-message');
        if (existingMsg) {
            existingMsg.remove();
        }

        const msgDiv = document.createElement('div');
        msgDiv.className = 'auto-success-message fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity';
        msgDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fe fe-check me-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(msgDiv);

        setTimeout(() => {
            msgDiv.style.opacity = '0';
            setTimeout(() => {
                if (msgDiv.parentNode) {
                    msgDiv.parentNode.removeChild(msgDiv);
                }
            }, 300);
        }, 3000);
    }

    // 이메일 동기화
    const userEmailInput = document.getElementById('user_email');
    userEmailInput.addEventListener('input', function() {
        if (this.value && this.value !== searchEmailInput.value) {
            searchEmailInput.value = this.value;
        }
    });

    searchEmailInput.addEventListener('input', function() {
        if (this.value && this.value !== userEmailInput.value) {
            userEmailInput.value = this.value;
        }
    });

    // ===========================================
    // 서비스 계층구조 선택 기능
    // ===========================================
    const categorySelect = document.getElementById('service_category');
    const serviceSelect = document.getElementById('service_id');
    const planSelect = document.getElementById('service_plan');
    const billingOptionsDiv = document.getElementById('billing_options');
    const planDescriptionDiv = document.getElementById('plan_description');
    const priceSummaryDiv = document.getElementById('price_summary');

    // 현재 선택 상태
    let currentSelection = {
        category: null,
        service: null,
        plan: null,
        billingCycle: null,
        priceData: null
    };

    // 페이지 로드 시 카테고리 목록 불러오기
    loadCategories();

    function loadCategories() {
        fetch('{{ route("admin.service.users.hierarchy.categories") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateCategories(data.categories);
            } else {
                console.error('Failed to load categories:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
    }

    function populateCategories(categories) {
        categorySelect.innerHTML = '<option value="">카테고리를 선택하세요</option>';
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            option.dataset.description = category.description || '';
            categorySelect.appendChild(option);
        });
    }

    // 카테고리 선택 이벤트
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        if (categoryId) {
            currentSelection.category = {
                id: categoryId,
                name: this.options[this.selectedIndex].textContent
            };
            loadServices(categoryId);
        } else {
            resetSelection('category');
        }
    });

    function loadServices(categoryId) {
        fetch(`{{ route("admin.service.users.hierarchy.services") }}?category_id=${categoryId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateServices(data.services);
            } else {
                console.error('Failed to load services:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading services:', error);
        });
    }

    function populateServices(services) {
        serviceSelect.innerHTML = '<option value="">서비스를 선택하세요</option>';
        serviceSelect.disabled = false;

        services.forEach(service => {
            const option = document.createElement('option');
            option.value = service.id;
            option.textContent = service.title;
            option.dataset.description = service.description || '';
            option.dataset.price = service.price || 0;
            serviceSelect.appendChild(option);
        });

        resetSelection('service');
    }

    // 서비스 선택 이벤트
    serviceSelect.addEventListener('change', function() {
        const serviceId = this.value;
        if (serviceId) {
            currentSelection.service = {
                id: serviceId,
                title: this.options[this.selectedIndex].textContent
            };
            loadPlans(serviceId);
        } else {
            resetSelection('service');
        }
    });

    function loadPlans(serviceId) {
        fetch(`{{ route("admin.service.users.hierarchy.plans") }}?service_id=${serviceId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populatePlans(data.plans);
            } else {
                console.error('Failed to load plans:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading plans:', error);
        });
    }

    function populatePlans(plans) {
        planSelect.innerHTML = '<option value="">플랜을 선택하세요</option>';
        planSelect.disabled = false;

        plans.forEach(plan => {
            const option = document.createElement('option');
            option.value = plan.id;
            option.textContent = plan.plan_name;
            option.dataset.description = plan.description || '';
            option.dataset.features = JSON.stringify(plan.features || []);
            option.dataset.popular = plan.is_popular;
            option.dataset.featured = plan.is_featured;
            planSelect.appendChild(option);
        });

        resetSelection('plan');
    }

    // 플랜 선택 이벤트
    planSelect.addEventListener('change', function() {
        const planId = this.value;
        if (planId) {
            currentSelection.plan = {
                id: planId,
                name: this.options[this.selectedIndex].textContent,
                description: this.options[this.selectedIndex].dataset.description
            };

            showPlanDescription(currentSelection.plan.description);
            loadPrices(planId);
        } else {
            resetSelection('plan');
        }
    });

    function showPlanDescription(description) {
        if (description) {
            planDescriptionDiv.querySelector('p').textContent = description;
            planDescriptionDiv.classList.remove('d-none');
        } else {
            planDescriptionDiv.classList.add('d-none');
        }
    }

    function loadPrices(planId) {
        fetch(`{{ route("admin.service.users.hierarchy.prices") }}?plan_id=${planId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showBillingOptions(data.prices, data.plan);
            } else {
                console.error('Failed to load prices:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading prices:', error);
        });
    }

    function showBillingOptions(prices, planData) {
        billingOptionsDiv.innerHTML = '';

        if (prices.length === 0) {
            billingOptionsDiv.innerHTML = '<p class="text-sm text-red-500">이용 가능한 가격 옵션이 없습니다.</p>';
            return;
        }

        prices.forEach(priceOption => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'card mb-2 billing-option';
            optionDiv.style.cursor = 'pointer';
            optionDiv.dataset.cycle = priceOption.cycle;
            optionDiv.dataset.price = priceOption.price;
            optionDiv.dataset.planData = JSON.stringify(planData);
            optionDiv.dataset.priceData = JSON.stringify(priceOption);

            let discountHtml = '';
            if (priceOption.discount_percentage > 0) {
                discountHtml = `<small class="text-primary">(-${priceOption.discount_percentage}% 할인)</small>`;
            }

            optionDiv.innerHTML = `
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${priceOption.cycle_display}</h6>
                            <small class="text-muted">월 평균: ₩${number_format(priceOption.monthly_cost)}</small>
                        </div>
                        <div class="text-end">
                            <h5 class="mb-0">₩${number_format(priceOption.price)}</h5>
                            ${discountHtml}
                        </div>
                    </div>
                </div>
            `;

            optionDiv.addEventListener('click', function() {
                selectBillingOption(this);
            });

            billingOptionsDiv.appendChild(optionDiv);
        });

        resetSelection('billing');
    }

    function selectBillingOption(optionElement) {
        // 기존 선택 해제
        document.querySelectorAll('.billing-option').forEach(el => {
            el.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        });

        // 새 선택 표시
        optionElement.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');

        // 선택 상태 업데이트
        const cycle = optionElement.dataset.cycle;
        const planData = JSON.parse(optionElement.dataset.planData);
        const priceData = JSON.parse(optionElement.dataset.priceData);

        currentSelection.billingCycle = cycle;
        currentSelection.priceData = priceData;

        // 히든 필드 업데이트
        document.getElementById('billing_cycle').value = cycle;
        document.getElementById('plan_name').value = planData.name;
        document.getElementById('plan_price').value = priceData.price;
        document.getElementById('monthly_price').value = priceData.monthly_cost;
        document.getElementById('service_title').value = currentSelection.service.title;

        // 최종 가격 계산 및 표시
        calculateAndShowFinalPrice(planData, priceData);
    }

    function calculateAndShowFinalPrice(planData, priceData) {
        // 가격 계산
        const price = parseFloat(priceData.price);
        const setupFee = parseFloat(planData.setup_fee || 0);
        const totalPrice = price + setupFee;

        // 결과 표시
        updatePriceSummary({
            category: currentSelection.category.name,
            service: currentSelection.service.title,
            plan: planData.name,
            cycle: priceData.cycle_display,
            price: price,
            setupFee: setupFee,
            totalPrice: totalPrice,
            discount: priceData.discount_percentage,
            savings: priceData.savings || 0
        });
    }

    function updatePriceSummary(data) {
        document.getElementById('selected_category_name').textContent = data.category;
        document.getElementById('selected_service_name').textContent = data.service;
        document.getElementById('selected_plan_name').textContent = data.plan;
        document.getElementById('selected_cycle_name').textContent = data.cycle;
        document.getElementById('calculated_price').textContent = `₩${number_format(data.price)}`;

        // 설치비 표시
        if (data.setupFee > 0) {
            document.getElementById('setup_fee_amount').textContent = `₩${number_format(data.setupFee)}`;
            document.getElementById('setup_fee_display').classList.remove('d-none');
            document.getElementById('total_amount').textContent = `₩${number_format(data.totalPrice)}`;
            document.getElementById('total_display').classList.remove('d-none');
        } else {
            document.getElementById('setup_fee_display').classList.add('d-none');
            document.getElementById('total_display').classList.add('d-none');
        }

        // 할인 정보 표시
        if (data.discount > 0) {
            document.getElementById('discount_info').textContent = `${data.discount}% 할인 (₩${number_format(data.savings)} 절약)`;
            document.getElementById('discount_display').classList.remove('d-none');
        } else {
            document.getElementById('discount_display').classList.add('d-none');
        }

        priceSummaryDiv.classList.remove('d-none');
    }

    function resetSelection(from) {
        switch(from) {
            case 'category':
                serviceSelect.innerHTML = '<option value="">먼저 카테고리를 선택하세요</option>';
                serviceSelect.disabled = true;
                // fall through
            case 'service':
                planSelect.innerHTML = '<option value="">먼저 서비스를 선택하세요</option>';
                planSelect.disabled = true;
                planDescriptionDiv.classList.add('d-none');
                // fall through
            case 'plan':
                billingOptionsDiv.innerHTML = '<p class="text-muted">먼저 플랜을 선택하세요</p>';
                // fall through
            case 'billing':
                priceSummaryDiv.classList.add('d-none');
                // 히든 필드 초기화
                document.getElementById('billing_cycle').value = '';
                document.getElementById('plan_name').value = '';
                document.getElementById('plan_price').value = '';
                document.getElementById('monthly_price').value = '';
                document.getElementById('service_title').value = '';
                break;
        }
    }

    // 유틸리티 함수
    function number_format(number) {
        return new Intl.NumberFormat('ko-KR').format(number);
    }
});
</script>

<style>
/* 커스텀 스타일 */
.billing-option {
    transition: all 0.2s ease;
}

.billing-option:hover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
}

/* 반응형 메시지 */
@media (max-width: 576px) {
    .auto-success-message {
        top: 1rem !important;
        right: 1rem !important;
        left: 1rem !important;
    }
}

/* 스피너 애니메이션 */
.spinner-border-sm {
    animation: spinner-border 0.75s linear infinite;
}
</style>
@endsection
