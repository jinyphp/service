<!-- Sidebar -->
<style>
.navbar-heading {
    color: #8a94a6 !important;
    font-weight: 600 !important;
    font-size: 0.75rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    margin-bottom: 0.5rem !important;
    padding: 0.75rem 1.5rem 0.25rem 1.5rem !important;
}

.navbar-vertical .navbar-nav .navbar-heading:not(:first-child) {
    margin-top: 2rem !important;
}
</style>

<nav class="navbar-vertical navbar">
    <div class="vh-100" data-simplebar>
        <!-- Brand logo -->
        <a class="navbar-brand" href="/">
            <img src="{{ asset('assets/images/brand/logo/logo-inverse.svg') }}" alt="Jiny" />
        </a>

        <!-- Navbar nav -->
        <ul class="navbar-nav flex-column" id="sideNavbar">

            {{-- ============================================
                대시보드
            ============================================ --}}
            <li class="nav-item">
                <a class="nav-link" href="/admin/service">
                    <i class="nav-icon fe fe-home me-2"></i>
                    대시보드
                </a>
            </li>

            <li class="nav-item">
                <div class="nav-divider"></div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.plan.index') }}">
                    <i class="nav-icon fe fe-grid me-2"></i>
                    서비스 플렌
                </a>
            </li>

            {{-- ============================================
                서비스 관리
            ============================================ --}}
            <li class="nav-item">
                <div class="navbar-heading">서비스 관리</div>
            </li>

            {{-- 서비스 카테고리 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.categories.index') }}">
                    <i class="nav-icon fe fe-folder me-2"></i>
                    서비스 카테고리
                </a>
            </li>

            {{-- 서비스 목록 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.site.services.index') }}">
                    <i class="nav-icon fe fe-briefcase me-2"></i>
                    서비스 목록
                </a>
            </li>

            <li class="nav-item">
                <div class="navbar-heading">서비스 회원</div>
            </li>


            {{-- 구독 사용자 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.users.index') }}">
                    <i class="nav-icon fe fe-users me-2"></i>
                    구독 사용자
                </a>
            </li>

            {{-- 구독 로그 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.subscription-logs.index') }}">
                    <i class="nav-icon fe fe-activity me-2"></i>
                    구독 로그
                </a>
            </li>

            {{-- 결제 내역 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.payments.index') }}">
                    <i class="nav-icon fe fe-credit-card me-2"></i>
                    결제 내역
                </a>
            </li>
            {{-- 구독 프로세스 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navSubscriptionProcess" aria-expanded="false" aria-controls="navSubscriptionProcess">
                    <i class="nav-icon fe fe-refresh-cw me-2"></i>
                    구독 프로세스
                </a>
                <div id="navSubscriptionProcess" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.service.users.index') }}?status=active">
                                <i class="fe fe-play-circle me-2"></i>
                                활성 구독
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.service.users.index') }}?status=expiring_soon">
                                <i class="fe fe-alert-triangle me-2"></i>
                                만료 임박
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.service.users.index') }}?status=cancelled">
                                <i class="fe fe-x-circle me-2"></i>
                                취소된 구독
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- ============================================
                파트너 관리
            ============================================ --}}
            <li class="nav-item">
                <div class="navbar-heading">파트너 관리</div>
            </li>

            {{-- 파트너 등급 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.partner.tiers.index') }}">
                    <i class="nav-icon fe fe-award me-2"></i>
                    파트너 등급
                </a>
            </li>

            {{-- 파트너 엔지니어 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.partner.engineers.index') }}">
                    <i class="nav-icon fe fe-user-check me-2"></i>
                    파트너 엔지니어
                </a>
            </li>

            {{-- 파트너 지원서 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.partner.applications.index') }}">
                    <i class="nav-icon fe fe-file-text me-2"></i>
                    파트너 지원서
                </a>
            </li>

            {{-- ============================================
                운영 관리
            ============================================ --}}
            <li class="nav-item">
                <div class="navbar-heading">운영 관리</div>
            </li>

            {{-- 예약 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.operations.appointments.index') }}">
                    <i class="nav-icon fe fe-calendar me-2"></i>
                    예약 관리
                </a>
            </li>

            {{-- 고객 주소 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.customers.addresses.index') }}">
                    <i class="nav-icon fe fe-map-pin me-2"></i>
                    고객 주소 관리
                </a>
            </li>

            {{-- ============================================
                품질 관리
            ============================================ --}}
            <li class="nav-item">
                <div class="navbar-heading">품질 관리</div>
            </li>

            {{-- 서비스 체크리스트 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.quality.checklists.index') }}">
                    <i class="nav-icon fe fe-check-square me-2"></i>
                    서비스 체크리스트
                </a>
            </li>

            {{-- 서비스 진행상황 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.quality.progress.index') }}">
                    <i class="nav-icon fe fe-activity me-2"></i>
                    서비스 진행상황
                </a>
            </li>

            {{-- 서비스 검수 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.quality.inspections.index') }}">
                    <i class="nav-icon fe fe-eye me-2"></i>
                    서비스 검수
                </a>
            </li>

            {{-- ============================================
                작업 관리
            ============================================ --}}
            <li class="nav-item">
                <div class="navbar-heading">작업 관리</div>
            </li>

            {{-- 작업 배정 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.work.assignments.index') }}">
                    <i class="nav-icon fe fe-user-plus me-2"></i>
                    작업 배정
                </a>
            </li>

            {{-- 서비스 제공자 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.work.providers.index') }}">
                    <i class="nav-icon fe fe-users me-2"></i>
                    서비스 제공자
                </a>
            </li>

            {{-- ============================================
                운영 이력
            ============================================ --}}
            <li class="nav-item">
                <div class="navbar-heading">운영 이력</div>
            </li>

            {{-- 예약 변경 이력 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.service.history.appointment-changes.index') }}">
                    <i class="nav-icon fe fe-clock me-2"></i>
                    예약 변경 이력
                </a>
            </li>








        </ul>

        <!-- Help Card -->
        {{-- <div class="card bg-dark-primary shadow-none text-center mx-4 mt-5">
            <div class="card-body py-4">
                <h5 class="text-white-50">도움이 필요하신가요?</h5>
                <p class="text-white-50 fs-6 mb-3">CMS 관리 문서를 확인하세요</p>
                <a href="{{ route('admin.cms.dashboard') }}" class="btn btn-white btn-sm">
                    CMS 대시보드 바로가기
                </a>
            </div>
        </div> --}}
    </div>
</nav>
