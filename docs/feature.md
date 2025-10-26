# 구독형 서비스 관리 시스템 (Subscription Service Management)

## 패키지 의존성 (Package Dependencies)

본 모듈은 Jiny 생태계의 다음 패키지들과 통합되어 작동합니다:

### 1. jiny/admin 패키지 의존성
- **관리자 인증**: `users` 테이블 기반 세션 인증
- **접근 제어**: `admin` 미들웨어를 통한 관리자 권한 검증
- **관리 인터페이스**: 기존 Jiny Admin 패널과 완전 통합
- **사용자 관리**: 관리자는 단일 `users` 테이블에서 중앙집중식 관리

```php
// 관리자 라우트 예시
Route::middleware(['admin'])->prefix('admin/service')->group(function () {
    // 서비스 관리 라우트들
});
```

### 2. jiny/auth 패키지 의존성
- **고객/파트너 인증**: JWT 기반 토큰 인증 시스템
- **사용자 샤딩**: `users_0xx` 테이블을 통한 분산 사용자 관리
- **확장성**: 대용량 사용자 처리를 위한 수평적 확장 지원
- **보안**: JWT 토큰 기반 무상태(Stateless) 인증

```php
// 고객/파트너 라우트 예시
Route::middleware(['jwt.auth'])->prefix('home/service')->group(function () {
    // 고객 서비스 라우트들
});

Route::middleware(['jwt.auth', 'partner.verify'])->prefix('partner')->group(function () {
    // 파트너 서비스 라우트들
});
```

### 3. 사용자 분류 및 테이블 구조
```
관리자 (Admin):
├── 테이블: users (중앙집중)
├── 인증: 세션 기반
├── 미들웨어: admin
└── 접근: /admin/service/*

고객 (Customer):
├── 테이블: users_001, users_002, ... users_099 (샤딩)
├── 인증: JWT 토큰
├── 미들웨어: jwt.auth
└── 접근: /home/service/*

파트너 (Partner/Engineer):
├── 테이블: users_001, users_002, ... users_099 (샤딩)
├── 인증: JWT 토큰
├── 미들웨어: jwt.auth + partner.verify
├── 추가 테이블: partners (파트너 정보)
└── 접근: /partner/*
```

## 개요 (Overview)

본 모듈은 SaaS(Software as a Service) 기반의 구독형 서비스를 효율적으로 운영하고 관리하기 위한 종합적인 플랫폼입니다. 현대적인 구독 경제 모델에 최적화된 기능들을 제공하여, 서비스 제공자가 고객 생애주기(Customer Lifecycle) 전반에 걸쳐 효과적인 서비스 운영이 가능하도록 설계되었습니다.

Jiny 생태계의 기존 패키지들(`jiny/admin`, `jiny/auth`)과 완전히 통합되어 일관된 사용자 경험과 관리 체계를 제공합니다.

### 핵심 설계 철학

1. **고객 중심 설계**: 고객의 구독 여정(Customer Journey)을 중심으로 한 직관적이고 편리한 사용자 경험 제공
2. **확장 가능한 아키텍처**: 마이크로서비스 기반의 모듈형 구조로 비즈니스 성장에 따른 유연한 확장 지원
3. **데이터 기반 의사결정**: 실시간 분석과 인사이트를 통한 비즈니스 최적화 지원
4. **보안 우선**: 결제 정보와 개인데이터 보호를 위한 엔터프라이즈급 보안 구현

### 시스템 아키텍처 개념

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend UI   │────│  API Gateway    │────│  Microservices  │
│  (Customer)     │    │                 │    │   - Service     │
└─────────────────┘    │                 │    │   - Billing     │
                       │                 │    │   - Support     │
┌─────────────────┐    │                 │    │   - Analytics   │
│   Admin Panel   │────│                 │    └─────────────────┘
│  (Management)   │    │                 │
└─────────────────┘    └─────────────────┘    ┌─────────────────┐
                                              │   External      │
                                              │   Integrations  │
                                              │  - Payment      │
                                              │  - CRM          │
                                              │  - Support      │
                                              └─────────────────┘
```

### 주요 비즈니스 가치

- **매출 증대**: 예측 가능한 경상 매출(MRR/ARR) 모델 구축
- **고객 유지**: 데이터 기반 고객 이탈 방지 및 업셀/크로스셀 기회 창출
- **운영 효율성**: 자동화된 결제, 프로비저닝, 고객 지원 프로세스
- **확장성**: 글로벌 시장 진출을 위한 다국가, 다통화, 다결제수단 지원

## 1. 서비스 카탈로그 관리 (Service Catalog Management)

### 1.1 설계 목적과 필요성

서비스 카탈로그는 고객이 구독할 수 있는 모든 서비스 상품을 체계적으로 관리하고 제공하는 핵심 모듈입니다. 이는 단순한 상품 목록을 넘어서 고객의 구매 의사결정을 돕는 마케팅 도구이자, 내부 운영팀이 서비스를 효율적으로 관리할 수 있는 관리 도구로 기능합니다.

현대의 SaaS 비즈니스에서는 다양한 고객 세그먼트에 맞춤형 서비스를 제공해야 하므로, 유연하고 확장 가능한 서비스 카탈로그 시스템이 필수적입니다.

### 1.2 서비스 관리 (Service Management)

#### 1.2.1 서비스 생명주기 관리

**설계 방향**: 서비스는 기획 단계부터 출시, 운영, 종료까지의 전체 생명주기를 체계적으로 관리해야 합니다.

```
기획 → 개발 → 베타 → 출시 → 성숙 → 업데이트 → 종료
  ↓      ↓      ↓      ↓      ↓        ↓       ↓
Draft → Dev → Beta → Active → Mature → Updated → Deprecated
```

**구현 상세**:
- **Draft (초안)**: 서비스 기획 단계에서 내부 관리자만 접근 가능
- **Development (개발)**: 개발팀과 QA팀이 테스트할 수 있는 상태
- **Beta (베타)**: 선별된 고객들에게 제한적 공개
- **Active (활성)**: 일반 고객들에게 완전 공개된 상태
- **Mature (성숙)**: 안정화된 서비스로 신규 기능 추가 최소화
- **Updated (업데이트)**: 주요 기능 개선이나 가격 변경이 있는 상태
- **Deprecated (종료 예정)**: 신규 구독 중단, 기존 고객 이관 진행

#### 1.2.2 서비스 메타데이터 관리

**설계 방향**: SEO 최적화와 마케팅 효과를 위한 풍부한 메타데이터 관리

```php
// 서비스 메타데이터 구조 예시
[
    'basic_info' => [
        'name' => '프리미엄 분석 서비스',
        'slug' => 'premium-analytics',
        'short_description' => '고급 비즈니스 인텔리전스 도구',
        'full_description' => '상세 서비스 설명...',
        'category_id' => 1,
        'tags' => ['analytics', 'business-intelligence', 'reporting']
    ],
    'marketing' => [
        'hero_image' => '/images/services/analytics-hero.jpg',
        'gallery' => ['/images/analytics-1.jpg', '/images/analytics-2.jpg'],
        'video_url' => 'https://youtube.com/watch?v=...',
        'features_highlight' => ['실시간 대시보드', 'AI 예측 분석', '커스텀 리포트']
    ],
    'seo' => [
        'meta_title' => 'AI 기반 비즈니스 분석 도구 | 프리미엄 분석',
        'meta_description' => '실시간 데이터 분석과 AI 예측으로...',
        'keywords' => ['비즈니스 분석', 'AI 예측', '실시간 대시보드'],
        'og_image' => '/images/og/analytics-og.jpg'
    ]
]
```

#### 1.2.3 동적 페이지 빌더

**설계 방향**: 마케팅팀이 개발자 없이도 매력적인 서비스 페이지를 생성할 수 있는 블록 기반 시스템

**블록 타입 정의**:
1. **Hero 블록**: 주요 메시지와 CTA (Call-to-Action)
2. **Feature 블록**: 주요 기능 소개 (아이콘, 제목, 설명)
3. **Pricing 블록**: 가격 정보와 플랜 비교
4. **Testimonial 블록**: 고객 후기와 사례
5. **FAQ 블록**: 자주 묻는 질문
6. **CTA 블록**: 행동 유도 버튼
7. **Media 블록**: 이미지, 비디오, 갤러리

**사용자 인터페이스 설계**:
- 드래그 앤 드롭 방식의 블록 배치
- 실시간 미리보기 기능
- 반응형 레이아웃 자동 적용
- 블록별 스타일 커스터마이징

#### 1.2.4 A/B 테스트 시스템

**설계 방향**: 서비스 페이지의 전환율 최적화를 위한 과학적 테스트 환경 제공

**테스트 시나리오**:
- 헤드라인 문구 테스트
- CTA 버튼 색상/위치 테스트
- 가격 표시 방식 테스트
- 이미지/비디오 효과 테스트

**통계적 유의성 보장**:
- 최소 샘플 사이즈 계산
- 신뢰도 95% 기준 결과 판정
- 테스트 기간 자동 종료
- 승리 버전 자동 적용 옵션

### 1.3 서비스 카테고리 시스템

#### 1.3.1 계층형 카테고리 구조

**설계 방향**: 복잡한 서비스 포트폴리오를 직관적으로 분류하고 탐색할 수 있는 구조

```
비즈니스 도구
├── 분석 도구
│   ├── 웹 분석
│   ├── 비즈니스 인텔리전스
│   └── 예측 분석
├── 마케팅 도구
│   ├── 이메일 마케팅
│   ├── 소셜 미디어 관리
│   └── SEO 도구
└── 협업 도구
    ├── 프로젝트 관리
    ├── 문서 관리
    └── 커뮤니케이션
```

**데이터베이스 설계**: Modified Preorder Tree Traversal (MPTT) 방식으로 효율적인 계층 구조 구현

#### 1.3.2 스마트 태깅 시스템

**설계 방향**: AI 기반 자동 태깅과 사용자 행동 기반 추천 시스템

**태그 유형**:
- **기능 태그**: 'real-time', 'automation', 'integration'
- **산업 태그**: 'ecommerce', 'healthcare', 'education'
- **규모 태그**: 'small-business', 'enterprise', 'startup'
- **기술 태그**: 'api', 'mobile-app', 'web-based'

**자동 추천 알고리즘**:
- 사용자 구매 이력 기반 협업 필터링
- 서비스 간 유사도 계산 (Content-based Filtering)
- 인기도와 평점을 고려한 하이브리드 추천

### 1.4 서비스 티어 관리

#### 1.4.1 티어 설계 전략

**설계 방향**: 고객의 성장 단계에 맞춘 진화적 가격 모델

**Good-Better-Best 원칙**:
- **Basic**: 개인/소규모팀 대상, 핵심 기능만 제공
- **Professional**: 중소기업 대상, 고급 기능과 우선 지원
- **Enterprise**: 대기업 대상, 무제한 기능과 전담 지원
- **Custom**: 특수 요구사항 대상, 맞춤형 솔루션

#### 1.4.2 기능 매트릭스 설계

**설계 방향**: 명확하고 비교하기 쉬운 기능 차별화

```
| 기능                | Basic | Pro | Enterprise |
|--------------------|-------|-----|------------|
| 월간 리포트 수      | 10개  | 100개| 무제한     |
| 데이터 저장 기간    | 3개월 | 1년  | 무제한     |
| API 호출 수        | 1K/월 | 10K/월| 무제한    |
| 팀 멤버 수         | 3명   | 25명 | 무제한     |
| 우선순위 지원      | ❌    | ✅   | ✅         |
| 전담 매니저        | ❌    | ❌   | ✅         |
```

### 1.5 데이터베이스 스키마 설계

#### 1.5.1 서비스 테이블 구조

```sql
-- 서비스 기본 정보
CREATE TABLE services (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    short_description TEXT,
    full_description LONGTEXT,
    category_id BIGINT,
    status ENUM('draft', 'dev', 'beta', 'active', 'mature', 'updated', 'deprecated'),
    featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_featured (featured),
    FOREIGN KEY (category_id) REFERENCES service_categories(id)
);

-- 서비스 미디어
CREATE TABLE service_media (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT NOT NULL,
    type ENUM('hero_image', 'gallery', 'video', 'icon'),
    url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- 서비스 SEO 정보
CREATE TABLE service_seo (
    service_id BIGINT PRIMARY KEY,
    meta_title VARCHAR(255),
    meta_description TEXT,
    keywords TEXT,
    og_title VARCHAR(255),
    og_description TEXT,
    og_image VARCHAR(500),
    canonical_url VARCHAR(500),
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);
```

## 2. 가격 및 요금제 관리 (Pricing & Plan Management)

### 2.1 설계 목적과 전략

가격 모델은 SaaS 비즈니스의 핵심 경쟁력이자 수익성을 결정하는 중요한 요소입니다. 효과적인 가격 전략은 고객의 가치 인식과 지불 의향을 최대화하면서, 동시에 예측 가능한 매출 성장을 보장해야 합니다.

**핵심 가격 전략**:
1. **Value-Based Pricing**: 고객이 얻는 가치에 비례한 가격 책정
2. **Freemium Model**: 무료 체험을 통한 고객 획득 후 유료 전환
3. **Usage-Based Pricing**: 실제 사용량에 기반한 공정한 과금
4. **Seat-Based Pricing**: 사용자 수 기반의 확장 가능한 모델

### 2.2 구독 주기 및 할인 정책

#### 2.2.1 구독 주기 설계

**설계 방향**: 고객의 다양한 예산 주기와 비즈니스 니즈에 맞춘 유연한 옵션 제공

```php
// 구독 주기 설정 예시
$billing_cycles = [
    'monthly' => [
        'period' => 1,
        'period_unit' => 'month',
        'discount_rate' => 0,
        'description' => '매월 자동 결제'
    ],
    'quarterly' => [
        'period' => 3,
        'period_unit' => 'month',
        'discount_rate' => 5, // 5% 할인
        'description' => '3개월마다 자동 결제'
    ],
    'yearly' => [
        'period' => 1,
        'period_unit' => 'year',
        'discount_rate' => 20, // 연간 20% 할인
        'description' => '연간 자동 결제 (20% 할인)'
    ],
    'biennial' => [
        'period' => 2,
        'period_unit' => 'year',
        'discount_rate' => 30, // 2년 30% 할인
        'description' => '2년 약정 (30% 할인)'
    ]
];
```

#### 2.2.2 동적 할인 시스템

**설계 방향**: 마케팅 캠페인과 고객 세그먼트에 따른 자동화된 할인 적용

**할인 유형**:
- **시간 기반 할인**: 얼리버드, 시즌 할인, 마감 임박 할인
- **볼륨 할인**: 사용자 수, 구독 기간에 따른 단계별 할인
- **고객 세그먼트 할인**: 스타트업, 학생, 비영리단체 할인
- **로열티 할인**: 장기 고객 대상 특별 할인

### 2.3 사용량 기반 과금 시스템

#### 2.3.1 미터링 아키텍처

**설계 방향**: 실시간 사용량 추적과 정확한 과금을 위한 확장 가능한 미터링 시스템

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Service API   │───▶│  Usage Tracker  │───▶│  Billing Engine │
│                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                        │                        │
         ▼                        ▼                        ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Usage Logs    │    │  Aggregation    │    │   Invoice Gen   │
│                 │    │    Service      │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

**미터링 대상**:
- **API 호출**: REST API, GraphQL 쿼리 횟수
- **데이터 처리량**: 업로드/다운로드 용량 (GB)
- **저장공간**: 사용 중인 스토리지 용량 (GB)
- **컴퓨팅 시간**: 서버 처리 시간 (시간)
- **트랜잭션**: 처리된 트랜잭션 수

#### 2.3.2 과금 정책 설계

**티어 기반 과금 모델**:
```
API 호출 과금 예시:
- 첫 1,000회: 무료
- 1,001 ~ 10,000회: 회당 ₩1
- 10,001 ~ 100,000회: 회당 ₩0.8
- 100,001회 이상: 회당 ₩0.5
```

### 2.4 고급 가격 모델

#### 2.4.1 무료 체험 시스템 (Free Trial System)

**설계 방향**: 체계적이고 유연한 무료 체험 시스템으로 고객 전환율 극대화

**무료 체험 유형 및 설정**:

```php
class FreeTrialConfigManager {

    public function getTrialTypes() {
        return [
            'time_based' => [
                'name' => '기간 기반 체험',
                'description' => '정해진 기간 동안 전체 기능 사용',
                'configurations' => [
                    'trial_period_days' => [7, 14, 30, 60], // 선택 가능한 체험 기간
                    'full_feature_access' => true,
                    'data_retention_after_trial' => 90, // 체험 종료 후 데이터 보관 기간
                    'auto_downgrade_to' => 'free_tier' // 체험 종료 후 전환
                ]
            ],

            'usage_based' => [
                'name' => '사용량 기반 체험',
                'description' => '특정 사용량까지 무료 이용',
                'configurations' => [
                    'trial_limits' => [
                        'api_calls' => 1000,
                        'storage_gb' => 5,
                        'users' => 3,
                        'projects' => 2
                    ],
                    'reset_cycle' => 'monthly', // 사용량 리셋 주기
                    'overage_handling' => 'block' // 한도 초과 시 차단
                ]
            ],

            'feature_based' => [
                'name' => '기능 제한 체험',
                'description' => '핵심 기능만 무제한 이용',
                'configurations' => [
                    'allowed_features' => [
                        'basic_dashboard',
                        'basic_reports',
                        'standard_support'
                    ],
                    'restricted_features' => [
                        'advanced_analytics',
                        'custom_integrations',
                        'priority_support',
                        'white_labeling'
                    ]
                ]
            ],

            'hybrid' => [
                'name' => '복합 체험',
                'description' => '기간 + 사용량 + 기능 제한 조합',
                'configurations' => [
                    'trial_period_days' => 14,
                    'trial_limits' => [
                        'api_calls' => 2000,
                        'storage_gb' => 10
                    ],
                    'premium_features_days' => 7, // 프리미엄 기능은 7일만
                    'grace_period_days' => 3 // 결제 유예 기간
                ]
            ]
        ];
    }
}
```

**고급 체험 정책 관리**:

```php
class AdvancedTrialPolicyEngine {

    /**
     * 개인화된 체험 조건 생성
     */
    public function generatePersonalizedTrial($customerProfile) {
        $baseTrialDays = 14;
        $adjustments = [];

        // 고객 프로필에 따른 체험 기간 조정
        if ($customerProfile['company_size'] === 'enterprise') {
            $adjustments['extended_trial'] = 30; // 대기업 30일
            $adjustments['dedicated_support'] = true;
        }

        if ($customerProfile['referral_source'] === 'partner') {
            $adjustments['bonus_features'] = ['advanced_analytics'];
            $adjustments['extended_trial'] = 21;
        }

        if ($customerProfile['industry'] === 'education') {
            $adjustments['extended_trial'] = 60; // 교육기관 60일
            $adjustments['discount_after_trial'] = 50; // 체험 후 50% 할인
        }

        return $this->buildTrialConfig($baseTrialDays, $adjustments);
    }

    /**
     * 체험 연장 정책
     */
    public function getTrialExtensionPolicies() {
        return [
            'first_time_extension' => [
                'max_days' => 7,
                'required_action' => 'complete_onboarding',
                'automatic' => false
            ],
            'engagement_based' => [
                'criteria' => [
                    'daily_logins' => 5,
                    'features_used' => 3,
                    'invite_sent' => 1
                ],
                'reward' => 'additional_7_days'
            ],
            'feedback_extension' => [
                'trigger' => 'completed_survey',
                'reward' => 'additional_3_days',
                'max_per_user' => 1
            ]
        ];
    }
}
```

**체험 사용자 온보딩 시스템**:

```php
class TrialOnboardingManager {

    public function createOnboardingJourney($trialUser) {
        $journey = [
            'day_0' => [
                'welcome_email' => true,
                'account_setup_guide' => true,
                'quick_start_tutorial' => true,
                'success_criteria' => 'complete_profile'
            ],
            'day_1' => [
                'feature_introduction' => 'core_features',
                'sample_data_import' => true,
                'success_criteria' => 'first_project_created'
            ],
            'day_3' => [
                'advanced_features_showcase' => true,
                'integration_guide' => true,
                'success_criteria' => 'integration_connected'
            ],
            'day_7' => [
                'progress_check_email' => true,
                'personal_consultation_offer' => true,
                'success_criteria' => 'active_usage_pattern'
            ],
            'day_10' => [
                'conversion_focused_email' => true,
                'pricing_information' => true,
                'limited_time_offer' => '20% off first year'
            ],
            'day_13' => [
                'final_reminder' => true,
                'data_export_guide' => true,
                'retention_offer' => 'extended_trial'
            ]
        ];

        return $this->scheduleOnboardingTasks($trialUser, $journey);
    }
}
```

**체험 전환 최적화 시스템**:

```php
class TrialConversionOptimizer {

    /**
     * 실시간 전환 가능성 예측
     */
    public function predictConversionProbability($trialUser) {
        $signals = [
            'login_frequency' => $this->getLoginFrequency($trialUser),
            'feature_adoption' => $this->getFeatureAdoptionScore($trialUser),
            'data_investment' => $this->getDataInvestmentScore($trialUser),
            'team_collaboration' => $this->getCollaborationScore($trialUser),
            'support_engagement' => $this->getSupportEngagementScore($trialUser)
        ];

        $probability = $this->calculateConversionScore($signals);

        return [
            'probability' => $probability,
            'risk_level' => $this->getRiskLevel($probability),
            'recommended_actions' => $this->getRecommendedActions($probability, $signals)
        ];
    }

    /**
     * 개인화된 전환 제안
     */
    public function generateConversionOffer($trialUser, $conversionData) {
        $offers = [];

        if ($conversionData['probability'] > 0.8) {
            $offers[] = [
                'type' => 'standard_conversion',
                'message' => '지금 구독하고 첫 달 20% 할인 받으세요',
                'discount' => 20,
                'urgency' => 'low'
            ];
        } elseif ($conversionData['probability'] > 0.5) {
            $offers[] = [
                'type' => 'incentive_conversion',
                'message' => '특별 할인 30% + 추가 기능 무료',
                'discount' => 30,
                'bonus_features' => ['advanced_analytics'],
                'urgency' => 'medium'
            ];
        } else {
            $offers[] = [
                'type' => 'retention_offer',
                'message' => '체험 기간 연장 + 개인 컨설팅',
                'trial_extension' => 14,
                'personal_demo' => true,
                'urgency' => 'high'
            ];
        }

        return $offers;
    }
}
```

**체험 데이터 분석 및 최적화**:

```php
class TrialAnalyticsEngine {

    public function generateTrialInsights() {
        return [
            'conversion_metrics' => [
                'overall_conversion_rate' => $this->getOverallConversionRate(),
                'conversion_by_trial_type' => $this->getConversionByTrialType(),
                'average_time_to_convert' => $this->getAverageTimeToConvert(),
                'high_value_user_conversion' => $this->getHighValueUserConversion()
            ],

            'engagement_patterns' => [
                'feature_adoption_sequence' => $this->getFeatureAdoptionSequence(),
                'critical_engagement_moments' => $this->getCriticalMoments(),
                'drop_off_points' => $this->getDropOffAnalysis()
            ],

            'optimization_opportunities' => [
                'trial_length_optimization' => $this->getOptimalTrialLength(),
                'feature_limit_optimization' => $this->getOptimalFeatureLimits(),
                'onboarding_improvements' => $this->getOnboardingOptimizations()
            ]
        ];
    }
}
```

#### 2.4.2 Enterprise 커스텀 가격

**설계 방향**: 대기업 고객의 특수 요구사항에 맞춘 맞춤형 계약 모델

**커스텀 요소**:
- **볼륨 할인**: 대량 사용에 따른 특별 가격
- **전용 인프라**: 클라우드 또는 온프레미스 전용 환경
- **SLA 보장**: 99.99% 가동시간, 4시간 내 대응
- **커스텀 개발**: 특수 기능 개발 및 통합

### 2.5 가격 최적화 도구

#### 2.5.1 A/B 테스트 프레임워크

**설계 방향**: 과학적 방법론을 통한 가격 최적화

**테스트 시나리오**:
- 가격 포인트 테스트 (₩10,000 vs ₩12,000)
- 무료 체험 기간 테스트 (14일 vs 30일)
- 할인율 테스트 (20% vs 25% 연간 할인)

#### 2.5.2 가격 탄력성 분석

**설계 방향**: 가격 변화가 수요에 미치는 영향 분석

```sql
-- 가격 민감도 분석 쿼리 예시
SELECT
    price_point,
    COUNT(*) as conversions,
    AVG(ltv) as avg_lifetime_value,
    (COUNT(*) * AVG(ltv)) as total_revenue
FROM subscription_conversions
WHERE test_period BETWEEN '2024-01-01' AND '2024-03-31'
GROUP BY price_point
ORDER BY total_revenue DESC;
```

### 2.6 데이터베이스 스키마 - 가격 관리

```sql
-- 가격 플랜
CREATE TABLE pricing_plans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    billing_cycle ENUM('monthly', 'quarterly', 'yearly', 'biennial'),
    base_price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'KRW',
    trial_config_id BIGINT NULL, -- 무료 체험 설정 참조
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (trial_config_id) REFERENCES trial_configurations(id),
    UNIQUE KEY unique_plan (service_id, slug)
);

-- 무료 체험 설정
CREATE TABLE trial_configurations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    trial_type ENUM('time_based', 'usage_based', 'feature_based', 'hybrid') NOT NULL,
    trial_period_days INT DEFAULT 0,

    -- 사용량 제한 (JSON)
    usage_limits JSON, -- {"api_calls": 1000, "storage_gb": 5, "users": 3}

    -- 기능 제한 (JSON)
    feature_restrictions JSON, -- {"allowed": ["basic_dashboard"], "restricted": ["advanced_analytics"]}

    -- 체험 정책
    auto_extend_conditions JSON, -- 자동 연장 조건
    conversion_incentives JSON, -- 전환 인센티브
    data_retention_days INT DEFAULT 90,
    grace_period_days INT DEFAULT 0,

    -- 개인화 정책
    personalization_rules JSON, -- 고객 프로필별 조정 규칙

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 사용자 체험 이력
CREATE TABLE user_trials (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    service_id BIGINT NOT NULL,
    trial_config_id BIGINT NOT NULL,

    -- 체험 기간
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scheduled_end_at TIMESTAMP NOT NULL,
    actual_end_at TIMESTAMP NULL,

    -- 체험 상태
    status ENUM('active', 'extended', 'converted', 'expired', 'cancelled') DEFAULT 'active',

    -- 개인화된 설정
    personalized_config JSON, -- 이 사용자만의 특별 설정

    -- 사용량 추적
    current_usage JSON, -- 현재 사용량
    usage_history JSON, -- 사용량 이력

    -- 전환 추적
    conversion_probability DECIMAL(5,4) DEFAULT 0,
    conversion_offers JSON, -- 제공된 전환 제안들
    conversion_reason TEXT, -- 전환/비전환 사유

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (trial_config_id) REFERENCES trial_configurations(id),

    INDEX idx_user_service (user_id, service_id),
    INDEX idx_status_end (status, scheduled_end_at),
    INDEX idx_conversion_probability (conversion_probability DESC)
);

-- 체험 사용량 추적
CREATE TABLE trial_usage_tracking (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_trial_id BIGINT NOT NULL,
    usage_type VARCHAR(50) NOT NULL, -- 'api_calls', 'storage', 'users' 등
    usage_amount DECIMAL(15,4) NOT NULL,
    tracked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON, -- 추가 컨텍스트 정보

    FOREIGN KEY (user_trial_id) REFERENCES user_trials(id),
    INDEX idx_trial_usage_type (user_trial_id, usage_type),
    INDEX idx_tracked_at (tracked_at)
);

-- 체험 온보딩 진행상황
CREATE TABLE trial_onboarding_progress (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_trial_id BIGINT NOT NULL,
    onboarding_step VARCHAR(100) NOT NULL,
    completed_at TIMESTAMP NULL,
    success_criteria_met BOOLEAN DEFAULT FALSE,
    engagement_score DECIMAL(5,2) DEFAULT 0,
    notes TEXT,

    FOREIGN KEY (user_trial_id) REFERENCES user_trials(id),
    UNIQUE KEY unique_step_progress (user_trial_id, onboarding_step),
    INDEX idx_completion_status (completed_at, success_criteria_met)
);

-- 체험 전환 이벤트
CREATE TABLE trial_conversion_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_trial_id BIGINT NOT NULL,
    event_type ENUM('offer_shown', 'offer_clicked', 'offer_converted', 'trial_extended', 'trial_cancelled') NOT NULL,
    event_data JSON, -- 이벤트 상세 정보
    occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_trial_id) REFERENCES user_trials(id),
    INDEX idx_trial_events (user_trial_id, event_type),
    INDEX idx_occurred_at (occurred_at)
);

-- 사용량 미터
CREATE TABLE usage_meters (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    plan_id BIGINT NOT NULL,
    meter_name VARCHAR(255) NOT NULL,
    meter_type ENUM('api_calls', 'storage', 'bandwidth', 'users', 'transactions'),
    included_units BIGINT DEFAULT 0,
    overage_price DECIMAL(8,4),
    billing_model ENUM('tiered', 'volume', 'stairstep'),
    FOREIGN KEY (plan_id) REFERENCES pricing_plans(id)
);

-- 가격 티어 (사용량 기반 과금)
CREATE TABLE pricing_tiers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    meter_id BIGINT NOT NULL,
    tier_level INT NOT NULL,
    unit_from BIGINT NOT NULL,
    unit_to BIGINT,
    unit_price DECIMAL(8,4) NOT NULL,
    FOREIGN KEY (meter_id) REFERENCES usage_meters(id)
);
```

## 3. 구독 생명주기 관리 (Subscription Lifecycle Management)

### 3.1 설계 목적과 고객 여정

구독 생명주기 관리는 고객이 서비스를 발견하고 구독하는 순간부터 해지하거나 장기 고객이 되기까지의 전체 여정을 최적화하는 핵심 모듈입니다. 효과적인 생명주기 관리는 고객 획득 비용(CAC)을 줄이고 고객 생애 가치(LTV)를 극대화합니다.

**고객 여정 단계**:
```
Discovery → Trial → Onboarding → Activation → Engagement → Renewal → Expansion → Advocacy
    ↓        ↓        ↓           ↓           ↓          ↓         ↓         ↓
   마케팅   무료체험   온보딩      첫 가치     지속사용   갱신결제   업그레이드  추천
```

### 3.2 구독 신청 및 온보딩 프로세스

#### 3.2.1 지능형 가입 프로세스

**설계 방향**: 가입 과정에서의 이탈을 최소화하고 고품질 리드를 확보하기 위한 최적화된 경험

**단계별 설계**:

1. **초기 정보 수집** (30초 완료)
   ```php
   // 최소 필수 정보만 수집
   $required_fields = [
       'email' => 'required|email|unique:users',
       'company_name' => 'required|string|max:255',
       'company_size' => 'required|in:1-10,11-50,51-200,201-1000,1000+'
   ];
   ```

2. **지능형 플랜 추천**
   - 회사 규모와 업종 기반 자동 추천
   - 유사 고객의 선택 패턴 분석
   - 개인화된 가격 제안

3. **점진적 정보 수집**
   - 결제 시점에서만 상세 정보 요청
   - 서비스 사용 중 필요에 따라 추가 정보 수집

#### 3.2.2 멀티채널 인증 시스템

**설계 방향**: 보안성과 편의성을 동시에 만족하는 유연한 인증 체계

**인증 방법별 구현**:
- **이메일 인증**: Magic link 기반 비밀번호 없는 로그인
- **소셜 로그인**: OAuth 2.0 기반 주요 플랫폼 연동
- **SSO (Enterprise)**: SAML 2.0, OpenID Connect 지원
- **도메인 인증**: 기업 이메일 도메인 자동 검증

#### 3.2.3 온보딩 개인화 시스템

**설계 방향**: 고객의 목표와 사용 패턴에 맞춘 맞춤형 온보딩 경험

```php
// 온보딩 체크리스트 동적 생성
class OnboardingBuilder {
    public function buildChecklist($user) {
        $checklist = [];

        if ($user->company_size === '1-10') {
            $checklist[] = ['task' => 'setup_basic_dashboard', 'priority' => 1];
            $checklist[] = ['task' => 'invite_team_members', 'priority' => 3];
        } else {
            $checklist[] = ['task' => 'configure_sso', 'priority' => 1];
            $checklist[] = ['task' => 'setup_advanced_analytics', 'priority' => 2];
        }

        return $checklist;
    }
}
```

### 3.3 구독 상태 관리 시스템

#### 3.3.1 상태 기반 아키텍처

**설계 방향**: 명확한 상태 정의와 전환 규칙으로 일관성 있는 구독 관리

```
┌─────────────┐    결제성공    ┌─────────────┐    만료    ┌─────────────┐
│    Trial    │──────────────▶│   Active    │─────────▶│   Expired   │
└─────────────┘                └─────────────┘           └─────────────┘
       │                              │                         │
       │ 체험만료                       │ 결제실패                 │ 재결제
       ▼                              ▼                         ▼
┌─────────────┐                ┌─────────────┐           ┌─────────────┐
│   Expired   │                │  Past Due   │           │   Active    │
└─────────────┘                └─────────────┘           └─────────────┘
                                      │
                                      │ 유예기간만료
                                      ▼
                               ┌─────────────┐
                               │ Cancelled   │
                               └─────────────┘
```

#### 3.3.2 자동화된 상태 전환

**설계 방향**: 비즈니스 규칙에 따른 자동화된 상태 관리와 고객 커뮤니케이션

**상태별 자동 액션**:
- **Trial → Expired**: 체험 종료 3일 전 알림, 전환 인센티브 제공
- **Active → Past Due**: 결제 실패 시 즉시 재시도, 48시간 후 서비스 제한
- **Past Due → Cancelled**: 7일 유예 기간 후 자동 해지, 데이터 백업 안내

### 3.4 구독 변경 관리

#### 3.4.1 업그레이드/다운그레이드 엔진

**설계 방향**: 고객의 비즈니스 성장에 맞춘 유연한 플랜 변경과 정확한 비례 계산

**비례 계산 로직**:
```php
class ProrationCalculator {
    public function calculateUpgrade($currentPlan, $newPlan, $billingCycle) {
        $remainingDays = $this->getRemainingDays($billingCycle);
        $totalDays = $this->getTotalDays($billingCycle);

        $currentPlanCredit = ($currentPlan->price / $totalDays) * $remainingDays;
        $newPlanCharge = ($newPlan->price / $totalDays) * $remainingDays;

        return max(0, $newPlanCharge - $currentPlanCredit);
    }
}
```

#### 3.4.2 지능형 플랜 추천

**설계 방향**: 사용 패턴 분석을 통한 자동 업그레이드/다운그레이드 제안

**추천 로직**:
- **사용량 기반**: 제한 임계치 80% 도달 시 상위 플랜 추천
- **기능 사용**: 제한된 기능 접근 시도 시 해당 플랜 추천
- **팀 규모**: 사용자 수 증가 패턴 분석을 통한 예측적 추천

### 3.5 해지 방지 및 윈백 시스템

#### 3.5.1 해지 의도 예측 모델

**설계 방향**: 머신러닝 기반 이탈 위험 고객 조기 감지

**위험 지표**:
- 로그인 빈도 급감 (30일 내 50% 감소)
- 핵심 기능 미사용 (14일간 주요 기능 접근 없음)
- 지원 티켓 증가 (불만 관련 문의 패턴)
- 사용량 감소 (API 호출, 스토리지 사용량 하락)

#### 3.5.2 해지 플로우 최적화

**설계 방향**: 해지 과정에서 고객을 유지할 수 있는 마지막 기회 활용

```php
// 해지 플로우 단계별 처리
class CancellationFlow {
    public function processStep($step, $user) {
        switch($step) {
            case 'reason_collection':
                return $this->showReasonOptions();
            case 'retention_offer':
                return $this->generateRetentionOffer($user);
            case 'final_confirmation':
                return $this->showFinalOptions($user);
        }
    }

    private function generateRetentionOffer($user) {
        if ($user->cancellation_reason === 'price') {
            return ['type' => 'discount', 'amount' => 25, 'duration' => 3];
        } elseif ($user->cancellation_reason === 'features') {
            return ['type' => 'trial_upgrade', 'plan' => 'professional', 'duration' => 30];
        }
    }
}
```

### 3.6 팀 및 조직 관리

#### 3.6.1 계층적 조직 구조

**설계 방향**: 복잡한 기업 조직 구조를 반영한 유연한 권한 관리

```
Organization (조직)
├── Department (부서)
│   ├── Team (팀)
│   │   └── Users (사용자)
│   └── Team
└── Department
    └── Team
        └── Users
```

#### 3.6.2 세밀한 권한 제어

**설계 방향**: RBAC (Role-Based Access Control) 기반의 세분화된 권한 시스템

**권한 매트릭스**:
```
| 역할            | 결제관리 | 사용자관리 | 데이터접근 | 설정변경 |
|----------------|----------|-----------|----------|----------|
| Owner          | ✅       | ✅        | ✅       | ✅       |
| Admin          | ✅       | ✅        | ✅       | ❌       |
| Manager        | ❌       | ✅        | ✅       | ❌       |
| User           | ❌       | ❌        | ✅       | ❌       |
| Viewer         | ❌       | ❌        | 👁️       | ❌       |
```

### 3.7 데이터베이스 스키마 - 구독 관리

```sql
-- 구독 정보
CREATE TABLE subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    user_id BIGINT NOT NULL,
    plan_id BIGINT NOT NULL,
    status ENUM('trial', 'active', 'past_due', 'cancelled', 'expired') NOT NULL,
    trial_starts_at TIMESTAMP NULL,
    trial_ends_at TIMESTAMP NULL,
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_status (user_id, status),
    INDEX idx_plan_status (plan_id, status),
    INDEX idx_trial_ends (trial_ends_at),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (plan_id) REFERENCES pricing_plans(id)
);

-- 구독 변경 이력
CREATE TABLE subscription_changes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    subscription_id BIGINT NOT NULL,
    from_plan_id BIGINT,
    to_plan_id BIGINT NOT NULL,
    change_type ENUM('upgrade', 'downgrade', 'addon', 'removal'),
    proration_amount DECIMAL(10,2),
    effective_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id),
    FOREIGN KEY (from_plan_id) REFERENCES pricing_plans(id),
    FOREIGN KEY (to_plan_id) REFERENCES pricing_plans(id)
);

-- 조직 구조
CREATE TABLE organizations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) UNIQUE,
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 조직 멤버십
CREATE TABLE organization_memberships (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role ENUM('owner', 'admin', 'manager', 'user', 'viewer') NOT NULL,
    invited_by BIGINT,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (invited_by) REFERENCES users(id),
    UNIQUE KEY unique_membership (organization_id, user_id)
);
```

## 4. 구현 가이드라인 및 베스트 프랙티스

### 4.1 개발 방법론

#### 4.1.1 아키텍처 원칙

**마이크로서비스 아키텍처**: 각 도메인별로 독립적인 서비스로 분리하여 확장성과 유지보수성 확보

```
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│   Service        │  │   Billing        │  │   Support        │
│   Catalog        │  │   Service        │  │   Service        │
│                  │  │                  │  │                  │
├──────────────────┤  ├──────────────────┤  ├──────────────────┤
│ - 서비스 관리     │  │ - 결제 처리       │  │ - 티켓 관리       │
│ - 카테고리 관리   │  │ - 구독 관리       │  │ - 지식베이스      │
│ - 가격 관리       │  │ - 인보이스 생성   │  │ - 실시간 채팅     │
└──────────────────┘  └──────────────────┘  └──────────────────┘
```

#### 4.1.2 API 설계 원칙

**RESTful API with GraphQL**: REST는 CRUD 작업에, GraphQL은 복잡한 쿼리에 활용

```php
// REST API 예시
POST /api/v1/subscriptions
GET /api/v1/subscriptions/{id}
PATCH /api/v1/subscriptions/{id}
DELETE /api/v1/subscriptions/{id}

// GraphQL 쿼리 예시
query GetSubscriptionDetails($id: ID!) {
  subscription(id: $id) {
    id
    status
    plan {
      name
      features {
        name
        limit
      }
    }
    usage {
      apiCalls
      storage
    }
  }
}
```

### 4.2 사용자 경험 (UX) 설계 가이드라인

#### 4.2.1 구독 온보딩 UX

**Progressive Disclosure**: 복잡한 정보를 단계적으로 제공하여 인지 부하 최소화

**온보딩 단계별 UX**:
1. **Welcome & Goal Setting** (30초)
   - 환영 메시지와 서비스 가치 제안
   - 사용자 목표 및 사용 사례 수집

2. **Quick Wins** (2분)
   - 즉시 가치를 느낄 수 있는 핵심 기능 체험
   - 샘플 데이터를 활용한 결과 미리보기

3. **Personalization** (5분)
   - 수집된 정보 기반 개인화 설정
   - 팀 초대 및 권한 설정

#### 4.2.2 구독 관리 대시보드 UX

**정보 계층구조**: 가장 중요한 정보를 상단에, 세부 정보는 드릴다운 방식으로 제공

```
구독 대시보드 레이아웃:
┌─────────────────────────────────────────────────────┐
│ 📊 현재 플랜: Professional | 다음 결제일: 2024-02-15  │
├─────────────────────────────────────────────────────┤
│ 📈 이번 달 사용량                                    │
│ ┌───────────┐ ┌───────────┐ ┌───────────┐          │
│ │ API 호출   │ │ 저장공간   │ │ 팀 멤버    │          │
│ │ 75% 사용  │ │ 45% 사용  │ │ 8/25명    │          │
│ └───────────┘ └───────────┘ └───────────┘          │
├─────────────────────────────────────────────────────┤
│ 🛠️ 빠른 작업                                        │
│ [플랜 업그레이드] [팀원 초대] [결제 정보 수정]       │
└─────────────────────────────────────────────────────┘
```

#### 4.2.3 결제 및 청구 UX

**신뢰성 확보**: 보안 인증서, 결제 보안 설명, 명확한 취소 정책 표시

**결제 페이지 체크리스트**:
- [ ] SSL 인증서 아이콘 표시
- [ ] 지원 결제 수단 로고 표시
- [ ] 환불 정책 링크 제공
- [ ] 결제 정보 암호화 설명
- [ ] 고객 지원 연락처 표시

### 4.3 보안 및 컴플라이언스

#### 4.3.1 데이터 보호

**개인정보 최소 수집 원칙**: 서비스 제공에 필요한 최소한의 정보만 수집

```php
// 데이터 암호화 예시
class UserDataEncryption {
    public function encryptSensitiveData($data) {
        return encrypt($data, config('app.encryption_key'));
    }

    public function hashPassword($password) {
        return Hash::make($password);
    }

    public function tokenizeCard($cardNumber) {
        // PCI DSS 준수를 위한 카드 토큰화
        return PaymentGateway::tokenize($cardNumber);
    }
}
```

#### 4.3.2 감사 로그

**모든 중요 액션 로깅**: 결제, 구독 변경, 권한 변경 등 추적 가능한 로그 기록

```php
// 감사 로그 예시
class AuditLogger {
    public function logSubscriptionChange($subscription, $action, $details) {
        AuditLog::create([
            'user_id' => auth()->id(),
            'resource_type' => 'subscription',
            'resource_id' => $subscription->id,
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()
        ]);
    }
}
```

### 4.4 성능 최적화

#### 4.4.1 캐싱 전략

**다층 캐싱**: 애플리케이션, 데이터베이스, CDN 레벨에서 적절한 캐싱 구현

```php
// 캐싱 전략 예시
class ServiceCatalogCache {
    public function getPopularServices() {
        return Cache::remember('popular_services', 3600, function () {
            return Service::where('featured', true)
                         ->where('status', 'active')
                         ->orderBy('popularity_score', 'desc')
                         ->take(10)
                         ->get();
        });
    }
}
```

#### 4.4.2 데이터베이스 최적화

**인덱스 설계**: 자주 조회되는 필드 조합에 대한 복합 인덱스 생성

```sql
-- 성능 최적화를 위한 인덱스
CREATE INDEX idx_subscriptions_user_status_active
ON subscriptions(user_id, status)
WHERE status IN ('active', 'trial');

CREATE INDEX idx_usage_tracking_date_service
ON usage_tracking(created_date, service_id, user_id);
```

### 4.5 모니터링 및 알림

#### 4.5.1 비즈니스 메트릭 모니터링

**핵심 지표 실시간 추적**: MRR, 이탈률, 전환율 등 중요 비즈니스 메트릭 모니터링

```php
// 메트릭 수집 예시
class BusinessMetrics {
    public function calculateMRR() {
        return Subscription::where('status', 'active')
                          ->whereMonth('created_at', now())
                          ->sum('monthly_value');
    }

    public function calculateChurnRate($period = 30) {
        $totalActive = Subscription::where('status', 'active')
                                  ->where('created_at', '<=', now()->subDays($period))
                                  ->count();

        $churned = Subscription::where('status', 'cancelled')
                              ->whereBetween('cancelled_at', [
                                  now()->subDays($period),
                                  now()
                              ])->count();

        return $totalActive > 0 ? ($churned / $totalActive) * 100 : 0;
    }
}
```

### 4.6 국제화 및 다국가 지원

#### 4.6.1 다국가 결제 지원

**지역별 결제 수단**: 각 국가의 선호 결제 수단과 규제 요구사항 준수

```php
// 국가별 결제 설정
$paymentMethods = [
    'KR' => ['credit_card', 'bank_transfer', 'kakao_pay', 'naver_pay'],
    'US' => ['credit_card', 'paypal', 'ach'],
    'JP' => ['credit_card', 'konbini', 'bank_transfer'],
    'DE' => ['credit_card', 'sepa', 'sofort']
];
```

## 5. 위치 기반 서비스 관리 (Location-Based Service Management)

### 5.1 설계 목적과 필요성

물리적 서비스(청소, 수리, 배송 등)는 디지털 서비스와 달리 지리적 제약과 이동 비용이 발생합니다. 효율적인 위치 기반 서비스 관리는 운영 비용을 최소화하고 고객 만족도를 극대화하는 핵심 요소입니다.

### 5.2 서비스 지역 관리

#### 5.2.1 서비스 권역 설정

**계층적 지역 구조**:
```
국가 (Country)
├── 시/도 (Province/State)
│   ├── 시/군/구 (City/District)
│   │   ├── 동/읍/면 (Town/Township)
│   │   │   └── 세부 지역 (Detailed Area)
```

**지역별 서비스 설정**:
```php
class ServiceAreaManager {
    public function defineServiceArea($serviceId, $area) {
        return [
            'service_id' => $serviceId,
            'country' => $area['country'],
            'province' => $area['province'],
            'city' => $area['city'],
            'districts' => $area['districts'], // 서비스 가능 구역 배열
            'excluded_areas' => $area['excluded'] ?? [], // 서비스 제외 지역
            'travel_time_minutes' => $area['travel_time'] ?? 30,
            'additional_cost' => $area['additional_cost'] ?? 0
        ];
    }
}
```

#### 5.2.2 지역별 가격 차등화

**거리 기반 요금 체계**:
```php
class LocationPricing {
    public function calculateLocationSurcharge($serviceArea, $customerLocation) {
        $baseArea = $serviceArea['base_location'];
        $distance = $this->calculateDistance($baseArea, $customerLocation);

        $surchargeRules = [
            ['max_km' => 10, 'rate' => 0], // 기본 지역
            ['max_km' => 20, 'rate' => 0.1], // 10% 할증
            ['max_km' => 30, 'rate' => 0.2], // 20% 할증
            ['max_km' => 50, 'rate' => 0.3], // 30% 할증
        ];

        foreach ($surchargeRules as $rule) {
            if ($distance <= $rule['max_km']) {
                return $rule['rate'];
            }
        }

        return 0.5; // 50km 초과 시 50% 할증
    }
}
```

### 5.3 지역별 서비스 운영

#### 5.3.1 서비스 가능 시간 관리

**지역별 운영 시간**:
```php
class RegionalOperationHours {
    public function getServiceHours($location, $serviceType) {
        $baseHours = [
            'weekday' => ['start' => '09:00', 'end' => '18:00'],
            'saturday' => ['start' => '09:00', 'end' => '15:00'],
            'sunday' => ['closed' => true]
        ];

        // 지역 특성 반영
        if ($location['type'] === 'residential') {
            $baseHours['weekday']['start'] = '10:00'; // 주거지역 늦은 시작
        } elseif ($location['type'] === 'commercial') {
            $baseHours['weekday']['end'] = '20:00'; // 상업지역 연장 운영
        }

        return $baseHours;
    }
}
```

## 6. 스케줄링 및 예약 관리 (Scheduling & Appointment Management)

### 6.1 설계 목적과 필요성

물리적 서비스는 정확한 시간 관리와 효율적인 스케줄링이 성공의 핵심입니다. 고객의 편의성과 서비스 제공자의 효율성을 동시에 만족하는 지능형 스케줄링 시스템이 필요합니다.

### 6.2 정기 스케줄링 시스템

#### 6.2.1 구독 기반 자동 스케줄링

**정기 서비스 패턴 관리**:
```php
class RecurringScheduler {
    public function generateSchedule($subscription) {
        $pattern = $subscription->plan->visit_frequency; // 'monthly', 'bimonthly', 'weekly'
        $startDate = $subscription->starts_at;
        $endDate = $subscription->ends_at ?? $startDate->addYear();

        $schedule = [];
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $appointment = [
                'subscription_id' => $subscription->id,
                'scheduled_date' => $currentDate,
                'duration_minutes' => $subscription->service->duration,
                'status' => 'scheduled',
                'auto_generated' => true
            ];

            $schedule[] = $appointment;
            $currentDate = $this->getNextDate($currentDate, $pattern);
        }

        return $schedule;
    }
}
```

#### 6.2.2 지능형 시간 배정

**최적 시간대 추천**:
```php
class IntelligentTimeSlotManager {
    public function suggestOptimalTimeSlots($customer, $service, $date) {
        // 고객 선호도 분석
        $customerPreferences = $this->getCustomerPreferences($customer);

        // 서비스 제공자 가용성
        $providerAvailability = $this->getProviderAvailability($service, $date);

        // 교통 상황 고려
        $trafficAnalysis = $this->analyzeTrafficPatterns($customer->location, $date);

        return $this->calculateOptimalSlots([
            'customer_preferences' => $customerPreferences,
            'provider_availability' => $providerAvailability,
            'traffic_data' => $trafficAnalysis,
            'service_duration' => $service->duration
        ]);
    }
}
```

### 6.3 예약 변경 및 취소 관리

#### 6.3.1 유연한 일정 변경

**고객 친화적 변경 정책**:
```php
class AppointmentChangeManager {
    public function processRescheduleRequest($appointmentId, $newDateTime, $reason) {
        $appointment = Appointment::find($appointmentId);
        $hoursUntilAppointment = $appointment->scheduled_date->diffInHours(now());

        // 변경 정책 적용
        $changePolicy = $this->getChangePolicy($appointment->subscription->plan);

        if ($hoursUntilAppointment < $changePolicy['minimum_notice_hours']) {
            return [
                'status' => 'rejected',
                'reason' => 'insufficient_notice',
                'fee' => $changePolicy['late_change_fee']
            ];
        }

        // 새 시간대 가용성 확인
        $availability = $this->checkTimeSlotAvailability($newDateTime, $appointment->service);

        if (!$availability['available']) {
            return [
                'status' => 'rejected',
                'reason' => 'time_unavailable',
                'alternatives' => $availability['suggested_times']
            ];
        }

        return $this->executeReschedule($appointment, $newDateTime, $reason);
    }
}
```

## 7. 서비스 제공자 관리 (Service Provider Management)

### 7.1 설계 목적과 필요성

물리적 서비스의 품질은 서비스 제공자(기술자, 청소원, 배송원 등)의 역량에 직접적으로 의존합니다. 체계적인 인력 관리와 성과 평가 시스템은 일관된 서비스 품질 보장의 핵심입니다.

### 7.2 서비스 제공자 등록 및 관리

#### 7.2.1 제공자 프로필 관리

**종합적 프로필 시스템**:
```php
class ServiceProviderProfile {
    public function createProfile($providerData) {
        return [
            'basic_info' => [
                'name' => $providerData['name'],
                'phone' => $providerData['phone'],
                'email' => $providerData['email'],
                'photo' => $providerData['photo'],
                'id_verification' => $providerData['id_document']
            ],
            'service_capabilities' => [
                'service_types' => $providerData['services'], // ['aircon_cleaning', 'repair']
                'certifications' => $providerData['certifications'],
                'experience_years' => $providerData['experience'],
                'specializations' => $providerData['specializations']
            ],
            'operational_info' => [
                'service_areas' => $providerData['coverage_areas'],
                'available_hours' => $providerData['working_hours'],
                'vehicle_info' => $providerData['vehicle'],
                'equipment_owned' => $providerData['equipment']
            ],
            'performance_metrics' => [
                'average_rating' => 0,
                'completed_jobs' => 0,
                'customer_satisfaction' => 0,
                'punctuality_rate' => 0,
                'cancellation_rate' => 0
            ]
        ];
    }
}
```

#### 7.2.2 스킬 및 인증 관리

**기술 역량 인증 시스템**:
```php
class ProviderSkillManager {
    public function manageCertifications($providerId, $certification) {
        $certificationTypes = [
            'hvac_basic' => [
                'name' => '에어컨 기본 정비',
                'validity_months' => 24,
                'renewal_required' => true
            ],
            'cleaning_specialist' => [
                'name' => '전문 청소 자격',
                'validity_months' => 12,
                'renewal_required' => true
            ],
            'safety_training' => [
                'name' => '안전 교육 이수',
                'validity_months' => 6,
                'renewal_required' => true
            ]
        ];

        return $this->validateAndStoreCertification($providerId, $certification, $certificationTypes);
    }
}
```

### 7.3 서비스 제공자 배정 시스템

#### 7.3.1 지능형 매칭 알고리즘

**다중 조건 기반 최적 배정**:
```php
class ProviderMatchingEngine {
    public function findBestProvider($appointment) {
        $criteria = [
            'location_proximity' => 0.3, // 30% 가중치
            'skill_match' => 0.25,       // 25% 가중치
            'availability' => 0.2,       // 20% 가중치
            'customer_rating' => 0.15,   // 15% 가중치
            'workload_balance' => 0.1    // 10% 가중치
        ];

        $candidates = $this->getCandidateProviders($appointment);
        $scoredCandidates = [];

        foreach ($candidates as $provider) {
            $score = 0;

            // 위치 근접성 점수
            $distance = $this->calculateDistance($provider->location, $appointment->location);
            $score += $this->normalizeDistanceScore($distance) * $criteria['location_proximity'];

            // 기술 매칭 점수
            $skillMatch = $this->calculateSkillMatch($provider->skills, $appointment->required_skills);
            $score += $skillMatch * $criteria['skill_match'];

            // 가용성 점수
            $availability = $this->checkAvailability($provider, $appointment->scheduled_time);
            $score += $availability * $criteria['availability'];

            // 고객 평가 점수
            $rating = $provider->average_rating / 5; // 정규화
            $score += $rating * $criteria['customer_rating'];

            // 업무량 균형 점수
            $workloadScore = $this->calculateWorkloadBalance($provider);
            $score += $workloadScore * $criteria['workload_balance'];

            $scoredCandidates[] = [
                'provider' => $provider,
                'score' => $score,
                'breakdown' => $this->getScoreBreakdown($provider, $appointment)
            ];
        }

        return collect($scoredCandidates)->sortByDesc('score')->first();
    }
}
```

## 8. 서비스 품질 관리 및 체크리스트 (Quality Management & Checklists)

### 8.1 설계 목적과 필요성

물리적 서비스에서 품질의 일관성은 고객 만족도와 브랜드 신뢰도에 직접적으로 영향을 미칩니다. 표준화된 서비스 프로세스와 체크리스트 시스템은 서비스 품질을 보장하고 지속적인 개선을 가능하게 합니다.

### 8.2 서비스 표준화 시스템

#### 8.2.1 서비스별 체크리스트 관리

**동적 체크리스트 생성**:
```php
class ServiceChecklistManager {
    public function generateChecklist($serviceType, $customerRequirements = []) {
        $baseChecklist = $this->getBaseChecklist($serviceType);
        $customizations = $this->getCustomizations($customerRequirements);

        return [
            'service_id' => $serviceType,
            'version' => $this->getCurrentVersion($serviceType),
            'sections' => [
                'preparation' => $this->buildPreparationSection($baseChecklist, $customizations),
                'execution' => $this->buildExecutionSection($baseChecklist, $customizations),
                'completion' => $this->buildCompletionSection($baseChecklist, $customizations),
                'documentation' => $this->buildDocumentationSection($baseChecklist, $customizations)
            ],
            'required_evidence' => $this->getRequiredEvidence($serviceType),
            'quality_standards' => $this->getQualityStandards($serviceType)
        ];
    }

    private function getBaseChecklist($serviceType) {
        $checklists = [
            'aircon_cleaning' => [
                'preparation' => [
                    '고객 인사 및 신분증 확인',
                    '작업 범위 설명 및 동의',
                    '주변 보호 작업 (비닐 설치)',
                    '필요 도구 및 장비 준비 확인',
                    '안전장비 착용 확인'
                ],
                'execution' => [
                    '전원 차단 및 안전 확인',
                    '필터 분리 및 상태 점검',
                    '필터 세척 (물 + 중성세제)',
                    '내부 팬 및 열교환기 청소',
                    '드레인 라인 청소 및 점검',
                    '항균 코팅 적용 (옵션)',
                    '부품 조립 및 동작 테스트'
                ],
                'completion' => [
                    '청소 전후 사진 촬영',
                    '고객 확인 및 서명',
                    '다음 방문 일정 안내',
                    '정리 정돈 및 폐기물 처리',
                    '서비스 완료 보고서 작성'
                ]
            ]
        ];

        return $checklists[$serviceType] ?? [];
    }
}
```

#### 8.2.2 품질 기준 및 검증

**품질 측정 지표**:
```php
class QualityAssuranceManager {
    public function defineQualityMetrics($serviceType) {
        return [
            'time_standards' => [
                'preparation_time' => ['min' => 5, 'max' => 15], // 분
                'service_time' => ['min' => 30, 'max' => 60],
                'cleanup_time' => ['min' => 5, 'max' => 10]
            ],
            'quality_checkpoints' => [
                'customer_satisfaction' => ['threshold' => 4.0, 'scale' => 5],
                'completion_rate' => ['threshold' => 95], // %
                'rework_rate' => ['threshold' => 5], // %
                'punctuality' => ['threshold' => 90] // %
            ],
            'documentation_requirements' => [
                'before_photos' => ['required' => true, 'min_count' => 2],
                'after_photos' => ['required' => true, 'min_count' => 2],
                'customer_signature' => ['required' => true],
                'service_notes' => ['required' => true, 'min_length' => 50]
            ]
        ];
    }
}
```

### 8.3 실시간 품질 모니터링

#### 8.3.1 서비스 진행 상황 추적

**실시간 체크리스트 진행 관리**:
```php
class ServiceProgressTracker {
    public function trackChecklistProgress($appointmentId, $checklistItemId, $status, $evidence = null) {
        $progress = ServiceProgress::updateOrCreate([
            'appointment_id' => $appointmentId,
            'checklist_item_id' => $checklistItemId
        ], [
            'status' => $status, // 'pending', 'in_progress', 'completed', 'skipped'
            'completed_at' => $status === 'completed' ? now() : null,
            'evidence' => $evidence, // 사진, 서명, 메모 등
            'provider_notes' => request('notes'),
            'quality_score' => $this->calculateItemQualityScore($checklistItemId, $evidence)
        ]);

        // 전체 진행률 계산
        $overallProgress = $this->calculateOverallProgress($appointmentId);

        // 고객에게 실시간 업데이트 알림
        $this->notifyCustomerProgress($appointmentId, $overallProgress);

        // 품질 기준 미달 시 알림
        if ($progress->quality_score < 3.0) {
            $this->triggerQualityAlert($appointmentId, $checklistItemId);
        }

        return $progress;
    }
}
```

### 8.4 서비스 완료 및 검증

#### 8.4.1 고객 검수 프로세스

**디지털 검수 시스템**:
```php
class ServiceInspectionManager {
    public function initiateCustomerInspection($appointmentId) {
        $appointment = Appointment::find($appointmentId);
        $checklist = $appointment->service_checklist;

        // 검수 항목 생성
        $inspectionItems = [
            'service_completion' => [
                'title' => '서비스 완료 확인',
                'items' => $this->getCompletionVerificationItems($checklist),
                'required' => true
            ],
            'quality_assessment' => [
                'title' => '품질 평가',
                'items' => $this->getQualityAssessmentItems($appointment->service_type),
                'required' => true
            ],
            'additional_feedback' => [
                'title' => '추가 의견',
                'items' => $this->getFeedbackItems(),
                'required' => false
            ]
        ];

        return [
            'inspection_id' => Str::uuid(),
            'appointment_id' => $appointmentId,
            'customer_id' => $appointment->customer_id,
            'provider_id' => $appointment->provider_id,
            'inspection_items' => $inspectionItems,
            'deadline' => now()->addHours(24), // 24시간 내 검수 완료
            'digital_signature_required' => true
        ];
    }

    public function processCustomerApproval($inspectionId, $approvalData) {
        $inspection = ServiceInspection::find($inspectionId);

        $result = [
            'inspection_id' => $inspectionId,
            'approval_status' => $approvalData['status'], // 'approved', 'rejected', 'conditional'
            'overall_rating' => $approvalData['rating'],
            'feedback' => $approvalData['feedback'],
            'signature' => $approvalData['signature'],
            'photo_evidence' => $approvalData['photos'] ?? [],
            'approved_at' => now()
        ];

        if ($approvalData['status'] === 'rejected') {
            $this->initiateReworkProcess($inspection, $approvalData['rejection_reasons']);
        } else {
            $this->finalizeServiceCompletion($inspection, $result);
        }

        return $result;
    }
}
```

## 9. 물리적 서비스 추적 및 로지스틱스 (Physical Service Tracking & Logistics)

### 9.1 설계 목적과 필요성

물리적 서비스는 서비스 제공자의 이동과 현장 작업이 포함되므로, 실시간 위치 추적과 효율적인 로지스틱스 관리가 필수입니다. 이는 고객 만족도 향상과 운영 효율성 증대에 직접적으로 기여합니다.

### 9.2 실시간 위치 추적 시스템

#### 9.2.1 서비스 제공자 위치 관리

**GPS 기반 실시간 추적**:
```php
class LocationTrackingService {
    public function startServiceTracking($appointmentId, $providerId) {
        $tracking = ServiceTracking::create([
            'appointment_id' => $appointmentId,
            'provider_id' => $providerId,
            'status' => 'dispatched',
            'started_at' => now(),
            'route_optimization' => true
        ]);

        // 실시간 위치 업데이트 시작
        $this->enableRealTimeLocationUpdates($providerId, $tracking->id);

        // 고객에게 출발 알림
        $this->notifyCustomerDispatch($appointmentId, $tracking);

        return $tracking;
    }

    public function updateProviderLocation($trackingId, $locationData) {
        $location = LocationUpdate::create([
            'tracking_id' => $trackingId,
            'latitude' => $locationData['lat'],
            'longitude' => $locationData['lng'],
            'accuracy' => $locationData['accuracy'],
            'speed' => $locationData['speed'] ?? null,
            'heading' => $locationData['heading'] ?? null,
            'timestamp' => $locationData['timestamp'],
            'address' => $this->reverseGeocode($locationData['lat'], $locationData['lng'])
        ]);

        // ETA 계산 및 업데이트
        $eta = $this->calculateETA($trackingId, $locationData);
        $this->updateEstimatedArrival($trackingId, $eta);

        // 도착 임박 알림 (5분 이내)
        if ($eta <= 5) {
            $this->notifyCustomerArrivalImminent($trackingId);
        }

        return $location;
    }
}
```

#### 9.2.2 경로 최적화

**지능형 경로 계획**:
```php
class RouteOptimizationEngine {
    public function optimizeProviderRoute($providerId, $appointments) {
        // 당일 예정된 모든 예약 수집
        $scheduleData = collect($appointments)->map(function($appointment) {
            return [
                'appointment_id' => $appointment->id,
                'customer_location' => $appointment->customer->location,
                'service_duration' => $appointment->service->duration,
                'preferred_time' => $appointment->preferred_time,
                'priority' => $appointment->priority,
                'travel_time_from_previous' => null // 계산됨
            ];
        });

        // 경로 최적화 알고리즘 적용
        $optimizedRoute = $this->calculateOptimalRoute($scheduleData);

        return [
            'route_id' => Str::uuid(),
            'provider_id' => $providerId,
            'total_distance' => $optimizedRoute['total_distance'],
            'total_travel_time' => $optimizedRoute['total_travel_time'],
            'fuel_cost_estimate' => $optimizedRoute['fuel_cost'],
            'carbon_footprint' => $optimizedRoute['co2_emissions'],
            'ordered_appointments' => $optimizedRoute['sequence'],
            'alternative_routes' => $optimizedRoute['alternatives']
        ];
    }

    private function calculateOptimalRoute($appointments) {
        // TSP (Traveling Salesman Problem) 해결 알고리즘
        // Google Maps API 또는 자체 알고리즘 사용
        return $this->solveTSP($appointments);
    }
}
```

### 9.3 서비스 상태 관리

#### 9.3.1 서비스 생명주기 추적

**상태 기반 워크플로우**:
```php
class ServiceStatusManager {
    public function updateServiceStatus($appointmentId, $newStatus, $metadata = []) {
        $appointment = Appointment::find($appointmentId);
        $validTransitions = $this->getValidStatusTransitions($appointment->status);

        if (!in_array($newStatus, $validTransitions)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition from {$appointment->status} to {$newStatus}"
            );
        }

        $previousStatus = $appointment->status;
        $appointment->update(['status' => $newStatus]);

        // 상태 변경 기록
        ServiceStatusLog::create([
            'appointment_id' => $appointmentId,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'changed_by' => auth()->id(),
            'metadata' => $metadata,
            'timestamp' => now()
        ]);

        // 상태별 자동 액션 실행
        $this->executeStatusActions($appointmentId, $newStatus, $metadata);

        // 실시간 알림 발송
        $this->broadcastStatusUpdate($appointmentId, $newStatus);

        return $appointment;
    }

    private function getValidStatusTransitions($currentStatus) {
        return [
            'scheduled' => ['confirmed', 'cancelled', 'rescheduled'],
            'confirmed' => ['dispatched', 'cancelled'],
            'dispatched' => ['arrived', 'cancelled'],
            'arrived' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'paused', 'cancelled'],
            'paused' => ['in_progress', 'cancelled'],
            'completed' => ['verified', 'disputed'],
            'verified' => ['billed'],
            'disputed' => ['resolved', 'escalated']
        ];
    }
}
```

### 9.4 고객 커뮤니케이션 시스템

#### 9.4.1 자동화된 알림 시스템

**다채널 실시간 알림**:
```php
class ServiceNotificationManager {
    public function sendStatusNotification($appointmentId, $status, $customMessage = null) {
        $appointment = Appointment::with(['customer', 'provider', 'service'])->find($appointmentId);

        $notificationData = [
            'appointment_id' => $appointmentId,
            'customer_name' => $appointment->customer->name,
            'service_name' => $appointment->service->name,
            'provider_name' => $appointment->provider->name,
            'status' => $status,
            'estimated_arrival' => $appointment->estimated_arrival,
            'custom_message' => $customMessage
        ];

        $channels = $this->getNotificationChannels($appointment->customer, $status);

        foreach ($channels as $channel) {
            switch ($channel) {
                case 'sms':
                    $this->sendSMSNotification($appointment->customer->phone, $status, $notificationData);
                    break;
                case 'email':
                    $this->sendEmailNotification($appointment->customer->email, $status, $notificationData);
                    break;
                case 'push':
                    $this->sendPushNotification($appointment->customer, $status, $notificationData);
                    break;
                case 'in_app':
                    $this->sendInAppNotification($appointment->customer, $status, $notificationData);
                    break;
            }
        }

        return true;
    }

    private function getNotificationChannels($customer, $status) {
        $preferences = $customer->notification_preferences;
        $urgentStatuses = ['cancelled', 'emergency', 'delayed'];

        $channels = [];

        // 긴급 상황 시 모든 채널 사용
        if (in_array($status, $urgentStatuses)) {
            $channels = ['sms', 'push', 'in_app'];
            if ($preferences['email_urgent']) {
                $channels[] = 'email';
            }
        } else {
            // 일반 상황 시 고객 선호도 따름
            if ($preferences['sms_enabled']) $channels[] = 'sms';
            if ($preferences['email_enabled']) $channels[] = 'email';
            if ($preferences['push_enabled']) $channels[] = 'push';
            $channels[] = 'in_app'; // 항상 포함
        }

        return $channels;
    }
}
```

### 9.5 서비스 데이터 분석

#### 9.5.1 성과 분석 및 최적화

**운영 효율성 분석**:
```php
class ServiceAnalyticsEngine {
    public function generateOperationalReport($dateRange, $serviceType = null) {
        return [
            'efficiency_metrics' => $this->calculateEfficiencyMetrics($dateRange, $serviceType),
            'quality_metrics' => $this->calculateQualityMetrics($dateRange, $serviceType),
            'customer_satisfaction' => $this->calculateSatisfactionMetrics($dateRange, $serviceType),
            'provider_performance' => $this->calculateProviderMetrics($dateRange, $serviceType),
            'route_optimization' => $this->calculateRouteEfficiency($dateRange, $serviceType)
        ];
    }

    private function calculateEfficiencyMetrics($dateRange, $serviceType) {
        return [
            'average_service_time' => $this->getAverageServiceTime($dateRange, $serviceType),
            'travel_time_ratio' => $this->getTravelTimeRatio($dateRange, $serviceType),
            'utilization_rate' => $this->getProviderUtilizationRate($dateRange, $serviceType),
            'no_show_rate' => $this->getNoShowRate($dateRange, $serviceType),
            'cancellation_rate' => $this->getCancellationRate($dateRange, $serviceType),
            'rework_rate' => $this->getReworkRate($dateRange, $serviceType)
        ];
    }
}
```

## 구독 결제관리

### 결제 시스템 연동
- **결제 게이트웨이 지원**
  - 신용카드 (VISA, MasterCard, AMEX)
  - 계좌이체 (무통장입금)
  - 간편결제 (카카오페이, 네이버페이, 토스)
  - 해외결제 (PayPal, Stripe)
  - 가상계좌 발급
  - 휴대폰 소액결제

### 구독 결제 프로세스
- **신규 구독 결제**
  - 결제 방법 등록 및 검증
  - 즉시 결제 vs 나중에 결제
  - 결제 실패 시 재시도 로직
  - 무료 체험 후 자동 결제 전환

- **정기 결제 관리**
  - 자동 결제 스케줄링
  - 결제일 전 알림 발송
  - 결제 실패 시 재시도 (3회)
  - 연속 실패 시 서비스 일시정지

### 인보이스 및 영수증 관리
- **세금계산서 발행**
  - 개인/법인 구분
  - 사업자등록번호 관리
  - 전자세금계산서 발송
  - 세금계산서 발행 내역 관리

- **영수증 관리**
  - 결제 즉시 영수증 발행
  - PDF 형태 다운로드
  - 이메일 자동 발송
  - 영수증 재발행 기능

### 환불 및 크레딧 관리
- **환불 정책**
  - 부분 환불 vs 전체 환불
  - 환불 수수료 정책
  - 환불 처리 기간 안내
  - 환불 승인 워크플로우

- **크레딧 시스템**
  - 서비스 크레딧 지급
  - 크레딧 사용 내역 추적
  - 크레딧 만료 관리
  - 프로모션 크레딧

### 결제 분석 및 리포팅
- **매출 분석**
  - 일/월/년 매출 통계
  - 서비스별 매출 현황
  - 구독자 증감 추이
  - 이탈률 (Churn Rate) 분석

- **결제 실패 분석**
  - 실패 원인별 통계
  - 결제 성공률 모니터링
  - 결제 수단별 성공률
  - 재시도 성공률

## 구독 기술지원

### 고객 지원 시스템
- **티켓 시스템**
  - 다중 채널 접수 (이메일, 웹폼, 채팅)
  - 자동 티켓 분류 및 우선순위 설정
  - SLA(Service Level Agreement) 관리
  - 에스컬레이션 프로세스

- **실시간 지원**
  - 라이브 채팅 지원
  - 화면 공유 및 원격 지원
  - 영상 통화 지원
  - 24/7 지원 (Enterprise)

### 셀프 서비스 포털
- **지식 베이스**
  - FAQ 자동 추천 시스템
  - 사용 가이드 및 튜토리얼
  - API 문서 및 개발자 가이드
  - 동영상 교육 자료

- **커뮤니티 포럼**
  - 사용자 간 Q&A
  - 베스트 프랙티스 공유
  - 제품 피드백 수집
  - 전문가 답변 시스템

### 기술 지원 레벨
- **Basic 지원**
  - 이메일 지원 (48시간 내 응답)
  - 기본 사용법 안내
  - 커뮤니티 포럼 이용

- **Professional 지원**
  - 이메일 지원 (24시간 내 응답)
  - 라이브 채팅 지원 (업무시간)
  - 전화 지원
  - 우선순위 처리

- **Enterprise 지원**
  - 전담 계정 매니저
  - 24/7 우선 지원
  - 온사이트 지원
  - 커스텀 교육 프로그램

### 지원 품질 관리
- **성과 지표 (KPI)**
  - 응답 시간 (First Response Time)
  - 해결 시간 (Resolution Time)
  - 고객 만족도 (CSAT)
  - 티켓 해결률

- **지원팀 관리**
  - 담당자별 업무량 분배
  - 전문 분야별 라우팅
  - 지원 기록 및 히스토리 관리
  - 내부 교육 및 스킬업

## 시스템 관리 및 모니터링

### 서비스 상태 관리
- **시스템 모니터링**
  - 서버 상태 모니터링
  - 성능 지표 추적
  - 장애 조기 감지
  - 자동 알림 시스템

- **상태 페이지**
  - 실시간 서비스 상태 공개
  - 계획된 점검 공지
  - 장애 발생 시 투명한 커뮤니케이션
  - 히스토리 및 통계 제공

### 보안 및 컴플라이언스
- **데이터 보안**
  - 개인정보 암호화 저장
  - 접근 권한 관리
  - 감사 로그 기록
  - 정기 보안 점검

- **규정 준수**
  - GDPR 컴플라이언스
  - 개인정보보호법 준수
  - PCI DSS 인증 (결제 시)
  - ISO 27001 보안 관리

### 알림 및 커뮤니케이션
- **자동 알림 시스템**
  - 결제 알림 (성공/실패)
  - 서비스 만료 예정 알림
  - 새로운 기능 업데이트 공지
  - 보안 관련 중요 알림

- **커뮤니케이션 채널**
  - 이메일 뉴스레터
  - SMS 긴급 알림
  - 푸시 알림 (모바일 앱)
  - 인앱 메시지

### 분석 및 리포팅
- **사용량 분석**
  - 서비스별 사용 패턴 분석
  - 피크 시간대 분석
  - 기능별 사용률 통계
  - 사용자 행동 분석

- **비즈니스 인텔리전스**
  - 대시보드 및 리포트
  - 예측 분석 (이탈 가능성 등)
  - A/B 테스트 결과 분석
  - ROI 및 LTV 계산

### API 및 통합
- **REST API 제공**
  - 구독 관리 API
  - 결제 관리 API
  - 사용량 조회 API
  - 웹훅 지원

- **써드파티 통합**
  - CRM 시스템 연동
  - 마케팅 자동화 도구
  - 분석 도구 (Google Analytics)
  - 고객 지원 도구 연동

## 10. 물리적 서비스를 위한 데이터베이스 스키마

### 10.1 위치 및 지역 관리 테이블

```sql
-- 서비스 지역 정의
CREATE TABLE service_areas (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT NOT NULL,
    country VARCHAR(2) NOT NULL,
    province VARCHAR(255),
    city VARCHAR(255),
    district VARCHAR(255),
    postal_code VARCHAR(20),
    base_location POINT,
    service_radius_km DECIMAL(5,2),
    additional_cost DECIMAL(8,2) DEFAULT 0,
    travel_time_minutes INT DEFAULT 30,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (service_id) REFERENCES services(id),
    SPATIAL INDEX idx_location (base_location)
);

-- 고객 주소 정보
CREATE TABLE customer_addresses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL,
    address_type ENUM('primary', 'billing', 'service') DEFAULT 'primary',
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(255) NOT NULL,
    district VARCHAR(255),
    postal_code VARCHAR(20),
    coordinates POINT,
    access_instructions TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    SPATIAL INDEX idx_coordinates (coordinates)
);
```

### 10.2 예약 및 스케줄링 테이블

```sql
-- 예약 정보
CREATE TABLE appointments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    subscription_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    provider_id BIGINT,
    service_id BIGINT NOT NULL,
    service_address_id BIGINT NOT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time TIME NOT NULL,
    duration_minutes INT DEFAULT 60,
    status ENUM('scheduled', 'confirmed', 'dispatched', 'arrived', 'in_progress', 'paused', 'completed', 'verified', 'cancelled') DEFAULT 'scheduled',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    special_instructions TEXT,
    estimated_arrival TIMESTAMP NULL,
    actual_start_time TIMESTAMP NULL,
    actual_end_time TIMESTAMP NULL,
    auto_generated BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_date (customer_id, scheduled_date),
    INDEX idx_provider_date (provider_id, scheduled_date),
    INDEX idx_status (status),
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (provider_id) REFERENCES service_providers(id),
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (service_address_id) REFERENCES customer_addresses(id)
);

-- 예약 변경 이력
CREATE TABLE appointment_changes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    appointment_id BIGINT NOT NULL,
    change_type ENUM('reschedule', 'cancel', 'provider_change', 'service_change'),
    previous_date DATE,
    new_date DATE,
    previous_time TIME,
    new_time TIME,
    previous_provider_id BIGINT,
    new_provider_id BIGINT,
    reason VARCHAR(255),
    changed_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);
```

### 10.3 서비스 제공자 관리 테이블

```sql
-- 서비스 제공자 프로필
CREATE TABLE service_providers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    provider_code VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    specializations JSON,
    service_areas JSON,
    available_hours JSON,
    vehicle_info JSON,
    equipment_owned JSON,
    emergency_contact JSON,
    id_verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    background_check_status ENUM('pending', 'passed', 'failed') DEFAULT 'pending',
    insurance_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_provider_code (provider_code)
);

-- 제공자 인증 및 자격증
CREATE TABLE provider_certifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    provider_id BIGINT NOT NULL,
    certification_type VARCHAR(255) NOT NULL,
    certification_name VARCHAR(255) NOT NULL,
    issuing_authority VARCHAR(255),
    issue_date DATE,
    expiry_date DATE,
    certificate_number VARCHAR(255),
    verification_status ENUM('pending', 'verified', 'expired', 'invalid') DEFAULT 'pending',
    document_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES service_providers(id),
    INDEX idx_provider_cert (provider_id, certification_type),
    INDEX idx_expiry (expiry_date)
);

-- 제공자 성과 지표
CREATE TABLE provider_performance (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    provider_id BIGINT NOT NULL,
    metric_date DATE NOT NULL,
    total_appointments INT DEFAULT 0,
    completed_appointments INT DEFAULT 0,
    cancelled_appointments INT DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0,
    punctuality_rate DECIMAL(5,2) DEFAULT 0,
    customer_satisfaction DECIMAL(5,2) DEFAULT 0,
    rework_rate DECIMAL(5,2) DEFAULT 0,
    total_earnings DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES service_providers(id),
    UNIQUE KEY unique_provider_date (provider_id, metric_date)
);
```

### 10.4 서비스 품질 관리 테이블

```sql
-- 서비스 체크리스트 템플릿
CREATE TABLE service_checklists (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(20) NOT NULL,
    checklist_data JSON NOT NULL,
    quality_standards JSON,
    required_evidence JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- 서비스 진행 상황 추적
CREATE TABLE service_progress (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    appointment_id BIGINT NOT NULL,
    checklist_id BIGINT NOT NULL,
    checklist_item_id VARCHAR(255) NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'skipped', 'failed') DEFAULT 'pending',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    quality_score DECIMAL(3,2),
    provider_notes TEXT,
    evidence_type ENUM('photo', 'signature', 'note', 'measurement'),
    evidence_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (checklist_id) REFERENCES service_checklists(id),
    INDEX idx_appointment_progress (appointment_id, status)
);

-- 서비스 검수 및 승인
CREATE TABLE service_inspections (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    appointment_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    provider_id BIGINT NOT NULL,
    inspection_status ENUM('pending', 'approved', 'rejected', 'conditional') DEFAULT 'pending',
    overall_rating DECIMAL(3,2),
    quality_ratings JSON,
    feedback TEXT,
    rejection_reasons JSON,
    customer_signature LONGTEXT,
    photo_evidence JSON,
    inspector_notes TEXT,
    deadline TIMESTAMP,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (provider_id) REFERENCES service_providers(id)
);
```

### 10.5 위치 추적 및 로지스틱스 테이블

```sql
-- 서비스 추적
CREATE TABLE service_tracking (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    appointment_id BIGINT NOT NULL,
    provider_id BIGINT NOT NULL,
    tracking_status ENUM('dispatched', 'en_route', 'arrived', 'in_progress', 'completed') DEFAULT 'dispatched',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estimated_arrival TIMESTAMP,
    actual_arrival TIMESTAMP NULL,
    route_optimization_id VARCHAR(255),
    total_distance_km DECIMAL(8,2),
    total_travel_time_minutes INT,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (provider_id) REFERENCES service_providers(id)
);

-- 실시간 위치 업데이트
CREATE TABLE location_updates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tracking_id BIGINT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    accuracy DECIMAL(6,2),
    speed DECIMAL(6,2),
    heading DECIMAL(6,2),
    address TEXT,
    timestamp TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tracking_id) REFERENCES service_tracking(id),
    INDEX idx_tracking_time (tracking_id, timestamp)
);

-- 경로 최적화 기록
CREATE TABLE route_optimizations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    provider_id BIGINT NOT NULL,
    optimization_date DATE NOT NULL,
    total_appointments INT,
    total_distance_km DECIMAL(8,2),
    total_travel_time_minutes INT,
    fuel_cost_estimate DECIMAL(8,2),
    carbon_footprint_kg DECIMAL(8,2),
    route_data JSON,
    optimization_algorithm VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES service_providers(id)
);
```

### 10.6 알림 및 커뮤니케이션 테이블

```sql
-- 알림 로그
CREATE TABLE service_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    appointment_id BIGINT NOT NULL,
    recipient_id BIGINT NOT NULL,
    notification_type ENUM('appointment_reminder', 'status_update', 'arrival_notification', 'completion_notice'),
    channel ENUM('sms', 'email', 'push', 'in_app'),
    status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    message_content TEXT,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (recipient_id) REFERENCES users(id),
    INDEX idx_appointment_notifications (appointment_id),
    INDEX idx_status_channel (status, channel)
);

-- 서비스 상태 변경 로그
CREATE TABLE service_status_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    appointment_id BIGINT NOT NULL,
    previous_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by BIGINT,
    change_reason VARCHAR(255),
    metadata JSON,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (changed_by) REFERENCES users(id),
    INDEX idx_appointment_status (appointment_id, new_status)
);
```

## 11. 3-Tier 사용자 시스템 및 운영 모델 (Three-Tier User System & Operations Model)

### 11.1 사용자 그룹 및 역할 정의

#### 11.1.1 서비스 운영 참여자

**1. 서비스 관리자 (Service Administrator)**
- **데이터베이스**: `user` 테이블로 관리
- **인증 방식**: 세션 기반 인증
- **접근 라우트**: `/admin/service/*`
- **권한**: 플랫폼 전체 운영 관리, 파트너 관리 및 배정, 수익 분배 및 정산 관리, 서비스 품질 관리 및 정책 수립

**2. 고객 (Customer)**
- **데이터베이스**: `users_0xx` 샤딩 테이블로 관리
- **인증 방식**: JWT 토큰 기반 인증
- **접근 라우트**: `/home/service/*`
- **권한**: 서비스 구독 및 이용, 서비스 요청 및 스케줄링, 서비스 평가 및 피드백, 결제 및 구독 관리

**3. 파트너 (Partner)**
- **데이터베이스**: `users_0xx` 샤딩 테이블로 기본 회원 정보 관리 + 별도 파트너 등록 테이블
- **인증 방식**: JWT 토큰 기반 인증
- **접근 라우트**: `/partner/*`
- **파트너 유형**:

  **3-1. 서비스 파트너 (Service Engineer)**
  - **역할**: 실제 서비스 제공 (에어콘 청소, 수리 등)
  - **접근 라우트**: `/partner/service/*`
  - **권한**: 작업 스케줄 관리, 서비스 결과 보고, 수익 정산 확인, 고객 서비스 실행
  - **수익 구조**: 서비스 제공 수수료 (Bronze 60% → Platinum 75%)

  **3-2. 영업 파트너 (Sales Partner)**
  - **역할**: 고객 유치 및 영업 활동 (총판/리셀러/에이전시)
  - **접근 라우트**: `/partner/sales/*`
  - **권한**: 고객 관리, 영업 성과 추적, 커미션 정산 확인, 마케팅 자료 관리
  - **수익 구조**: 영업 커미션 + 지속 수익 분배 (5-15%)

### 11.2 라우트 구조 설계

#### 11.2.1 프론트엔드 사이트 구조
```
/ (메인 사이트)
├── /services                    # 서비스 소개 페이지
│   ├── /aircon-cleaning        # 에어콘 청소 서비스
│   ├── /appliance-repair       # 가전 수리 서비스
│   └── /maintenance            # 정기 점검 서비스
├── /pricing                    # 가격 정책
├── /about                      # 회사 소개
├── /contact                    # 문의하기
├── /login                      # 로그인
├── /register                   # 회원가입
└── /partner-apply              # 파트너 지원
```

#### 11.2.2 고객 포털 구조
```
/home (고객 대시보드)
├── /home/dashboard             # 대시보드
├── /home/services              # 서비스 관리
│   ├── /subscriptions         # 구독 관리
│   ├── /appointments          # 예약 관리
│   ├── /history              # 서비스 이력
│   └── /reviews              # 평가 관리
├── /home/billing              # 결제 관리
│   ├── /payments             # 결제 내역
│   ├── /invoices             # 청구서
│   └── /methods              # 결제 수단
├── /home/profile              # 프로필 관리
│   ├── /settings             # 계정 설정
│   ├── /addresses            # 주소 관리
│   └── /notifications        # 알림 설정
└── /home/support              # 고객 지원
    ├── /tickets              # 지원 티켓
    ├── /faq                  # 자주 묻는 질문
    └── /chat                 # 실시간 채팅
```

#### 11.2.3 파트너 포털 구조

**공통 파트너 대시보드**
```
/partner (파트너 메인 대시보드)
├── /partner/dashboard              # 통합 대시보드
├── /partner/profile                # 공통 프로필 관리
│   ├── /info                      # 기본 정보
│   ├── /business                  # 사업자 정보
│   └── /bank-account              # 정산 계좌
├── /partner/earnings               # 공통 수익 관리
│   ├── /summary                   # 수익 요약
│   ├── /history                   # 수익 내역
│   ├── /withdrawals               # 출금 신청
│   └── /tax-reports               # 세무 보고서
└── /partner/support                # 공통 지원
    ├── /help                      # 도움말
    ├── /contact                   # 문의하기
    └── /announcements             # 공지사항
```

**서비스 파트너 전용 (엔지니어)**
```
/partner/service (서비스 파트너)
├── /partner/service/dashboard      # 서비스 파트너 대시보드
├── /partner/service/tasks          # 작업 관리
│   ├── /assigned                  # 배정된 작업
│   ├── /in-progress               # 진행 중 작업
│   ├── /completed                 # 완료된 작업
│   └── /reviews                   # 고객 평가
├── /partner/service/schedule       # 스케줄 관리
│   ├── /calendar                  # 달력 보기
│   ├── /availability              # 가용 시간 설정
│   └── /routes                    # 경로 최적화
├── /partner/service/skills         # 기술 관리
│   ├── /specialties               # 전문 분야
│   ├── /certifications           # 자격증
│   ├── /training                  # 교육 이수
│   └── /equipment                 # 장비 관리
└── /partner/service/performance    # 성과 관리
    ├── /ratings                   # 평점 현황
    ├── /tier-progress             # 등급 진행도
    └── /quality-scores            # 품질 점수
```

**영업 파트너 전용 (총판/리셀러/에이전시)**
```
/partner/sales (영업 파트너)
├── /partner/sales/dashboard        # 영업 파트너 대시보드
├── /partner/sales/customers        # 고객 관리
│   ├── /leads                     # 리드 관리
│   ├── /prospects                 # 잠재 고객
│   ├── /active                    # 활성 고객
│   └── /referrals                 # 추천 고객
├── /partner/sales/campaigns        # 마케팅 캠페인
│   ├── /materials                 # 마케팅 자료
│   ├── /promotions                # 프로모션
│   ├── /tracking                  # 성과 추적
│   └── /analytics                 # 분석 리포트
├── /partner/sales/commissions      # 커미션 관리
│   ├── /structure                 # 수수료 구조
│   ├── /calculator                # 수익 계산기
│   ├── /forecasts                 # 수익 예측
│   └── /bonuses                   # 보너스 현황
└── /partner/sales/network          # 네트워크 관리
    ├── /sub-partners              # 하위 파트너
    ├── /territories               # 담당 지역
    └── /agreements                # 계약 관리
```

#### 11.2.4 관리자 패널 구조
```
/admin (관리자 대시보드)
├── /admin/dashboard           # 운영 대시보드
├── /admin/services            # 서비스 관리
│   ├── /catalog              # 서비스 카탈로그
│   ├── /pricing              # 가격 관리
│   └── /areas                # 서비스 지역
├── /admin/customers           # 고객 관리
│   ├── /list                 # 고객 목록
│   ├── /subscriptions        # 구독 관리
│   └── /analytics            # 고객 분석
├── /admin/engineers           # 엔지니어 관리
│   ├── /list                 # 엔지니어 목록
│   ├── /applications         # 지원자 관리
│   ├── /performance          # 성과 관리
│   ├── /tiers                # 등급 관리
│   └── /assignments          # 작업 배정
├── /admin/operations          # 운영 관리
│   ├── /appointments         # 예약 관리
│   ├── /scheduling           # 스케줄링
│   ├── /quality              # 품질 관리
│   └── /tracking             # 서비스 추적
├── /admin/finance             # 재무 관리
│   ├── /revenue              # 수익 현황
│   ├── /commissions          # 수수료 관리
│   ├── /payouts              # 정산 관리
│   └── /reports              # 재무 보고서
└── /admin/settings            # 시스템 설정
    ├── /policies             # 운영 정책
    ├── /commission-rates     # 수수료율 설정
    └── /notifications        # 알림 설정
```

### 11.3 인증 및 데이터베이스 아키텍처

#### 11.3.1 사용자 인증 시스템

**관리자 인증 (Session-based)**
```php
// 관리자 세션 인증 미들웨어
class AdminAuthMiddleware {
    public function handle($request, Closure $next) {
        if (!session('admin_user_id')) {
            return redirect('/admin/login');
        }

        $admin = User::find(session('admin_user_id'));
        if (!$admin || !$admin->isAdmin()) {
            session()->flush();
            return redirect('/admin/login');
        }

        // 관리자 활동 로깅
        $this->logAdminActivity($admin, $request);

        return $next($request);
    }
}

// 관리자 로그인 처리
class AdminAuthController extends Controller {
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->isAdmin()) {
                session(['admin_user_id' => $user->id]);
                return redirect('/admin/dashboard');
            }
        }

        return back()->withErrors(['Invalid admin credentials']);
    }
}
```

**고객/엔지니어 인증 (JWT-based)**
```php
// JWT 인증 미들웨어
class JWTAuthMiddleware {
    public function handle($request, Closure $next) {
        $token = $request->bearerToken() ?: $request->cookie('auth_token');

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $payload = JWT::decode($token, config('jwt.secret'), ['HS256']);
            $user = $this->findUserInShards($payload->user_id);

            if (!$user || $user->is_blocked) {
                return response()->json(['error' => 'User blocked'], 403);
            }

            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);

        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }

    private function findUserInShards($userId) {
        $shardIndex = $userId % 100; // 100개 샤드 가정
        $tableName = "users_{$shardIndex:03d}";

        return DB::table($tableName)->where('id', $userId)->first();
    }
}
```

#### 11.3.2 데이터베이스 샤딩 구조

**관리자 테이블**
```sql
-- 관리자 사용자 (단일 테이블)
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'manager') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**고객/엔지니어 샤딩 테이블**
```sql
-- 샤딩된 사용자 테이블 (users_000 ~ users_099)
CREATE TABLE users_000 (
    id BIGINT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('customer', 'engineer') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_blocked BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    last_activity_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user_type (user_type),
    INDEX idx_email (email),
    INDEX idx_active (is_active, is_blocked)
);

-- 추가 샤드 테이블들: users_001, users_002, ..., users_099
```

**파트너 등록 및 관리 테이블**

```sql
-- 통합 파트너 기본 정보
CREATE TABLE partners (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL, -- users_xxx 테이블의 사용자 ID
    user_shard_index TINYINT NOT NULL, -- 어느 샤드에 속하는지
    partner_code VARCHAR(20) UNIQUE NOT NULL,
    partner_type ENUM('service', 'sales') NOT NULL, -- 파트너 유형
    business_type ENUM('individual', 'corporate') DEFAULT 'individual',
    business_registration VARCHAR(20), -- 사업자등록번호
    company_name VARCHAR(100), -- 법인명/상호명
    contact_person VARCHAR(50), -- 담당자명
    phone VARCHAR(20),
    address JSON, -- 주소 정보
    bank_account JSON, -- 정산 계좌 정보
    status ENUM('pending', 'active', 'suspended', 'inactive') DEFAULT 'pending',
    total_earnings DECIMAL(12,2) DEFAULT 0.00,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user_shard (user_id, user_shard_index),
    INDEX idx_partner_type_status (partner_type, status),
    INDEX idx_partner_code (partner_code),
    INDEX idx_business_registration (business_registration)
);

-- 서비스 파트너 (엔지니어) 상세 정보
CREATE TABLE service_partners (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    partner_id BIGINT NOT NULL REFERENCES partners(id),
    tier_level ENUM('bronze', 'silver', 'gold', 'platinum') DEFAULT 'bronze',
    commission_rate DECIMAL(5,2) DEFAULT 60.00, -- 서비스 수수료율
    specialties JSON, -- 전문 분야 배열
    service_areas JSON, -- 서비스 가능 지역
    certifications JSON, -- 자격증 정보
    equipment_list JSON, -- 보유 장비 목록
    work_schedule JSON, -- 근무 가능 시간
    max_daily_jobs TINYINT DEFAULT 5, -- 일일 최대 작업 수
    travel_radius_km DECIMAL(5,2) DEFAULT 50.0, -- 이동 가능 반경(km)
    total_jobs BIGINT DEFAULT 0,
    completed_jobs BIGINT DEFAULT 0,
    avg_rating DECIMAL(3,2) DEFAULT 0.00,
    quality_score DECIMAL(5,2) DEFAULT 0.00,
    last_job_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_partner_tier (partner_id, tier_level),
    INDEX idx_rating_quality (avg_rating, quality_score),
    INDEX idx_service_areas ((CAST(service_areas AS CHAR(255) ARRAY))),
    INDEX idx_specialties ((CAST(specialties AS CHAR(255) ARRAY)))
);

-- 영업 파트너 (총판/리셀러/에이전시) 상세 정보
CREATE TABLE sales_partners (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    partner_id BIGINT NOT NULL REFERENCES partners(id),
    sales_tier ENUM('agent', 'reseller', 'distributor', 'master') DEFAULT 'agent',
    commission_structure JSON, -- 복잡한 커미션 구조
    base_commission_rate DECIMAL(5,2) DEFAULT 5.00, -- 기본 커미션율
    recurring_commission_rate DECIMAL(5,2) DEFAULT 2.00, -- 지속 수익 커미션율
    sales_territories JSON, -- 담당 지역
    marketing_budget DECIMAL(10,2) DEFAULT 0.00, -- 마케팅 예산
    customer_acquisition_target BIGINT DEFAULT 10, -- 월 고객 유치 목표
    performance_bonuses JSON, -- 성과 보너스 구조
    total_customers BIGINT DEFAULT 0,
    active_customers BIGINT DEFAULT 0,
    monthly_revenue DECIMAL(12,2) DEFAULT 0.00,
    conversion_rate DECIMAL(5,2) DEFAULT 0.00,
    last_sale_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_partner_tier (partner_id, sales_tier),
    INDEX idx_territories ((CAST(sales_territories AS CHAR(255) ARRAY))),
    INDEX idx_performance (total_customers, conversion_rate)
);
```

#### 11.3.3 샤딩 관리 시스템

```php
class UserShardManager {
    private const SHARD_COUNT = 100;

    public function determineShardIndex($userId) {
        return str_pad($userId % self::SHARD_COUNT, 3, '0', STR_PAD_LEFT);
    }

    public function getShardedTableName($userId) {
        $shardIndex = $this->determineShardIndex($userId);
        return "users_{$shardIndex}";
    }

    public function createUser($userData) {
        // 새 사용자 ID 생성 (분산 ID 생성기 사용)
        $userId = $this->generateDistributedUserId();
        $tableName = $this->getShardedTableName($userId);

        $userData['id'] = $userId;

        return DB::table($tableName)->insert($userData);
    }

    public function findUser($userId) {
        $tableName = $this->getShardedTableName($userId);
        return DB::table($tableName)->where('id', $userId)->first();
    }

    public function registerPartner($userId, $partnerType, $partnerData) {
        $user = $this->findUser($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }

        // 기본 파트너 정보 등록
        $basePartnerData = [
            'user_id' => $userId,
            'user_shard_index' => $this->determineShardIndex($userId),
            'partner_code' => $this->generatePartnerCode($partnerType),
            'partner_type' => $partnerType,
        ];

        $basePartnerData = array_merge($basePartnerData, $partnerData);
        $partnerId = DB::table('partners')->insertGetId($basePartnerData);

        // 파트너 유형별 상세 정보 등록
        if ($partnerType === 'service') {
            return $this->registerServicePartner($partnerId, $partnerData);
        } elseif ($partnerType === 'sales') {
            return $this->registerSalesPartner($partnerId, $partnerData);
        }

        return $partnerId;
    }

    private function registerServicePartner($partnerId, $data) {
        $serviceData = [
            'partner_id' => $partnerId,
            'tier_level' => $data['tier_level'] ?? 'bronze',
            'commission_rate' => $data['commission_rate'] ?? 60.00,
            'specialties' => json_encode($data['specialties'] ?? []),
            'service_areas' => json_encode($data['service_areas'] ?? []),
            'certifications' => json_encode($data['certifications'] ?? []),
            'equipment_list' => json_encode($data['equipment_list'] ?? []),
            'work_schedule' => json_encode($data['work_schedule'] ?? []),
        ];

        return DB::table('service_partners')->insert($serviceData);
    }

    private function registerSalesPartner($partnerId, $data) {
        $salesData = [
            'partner_id' => $partnerId,
            'sales_tier' => $data['sales_tier'] ?? 'agent',
            'commission_structure' => json_encode($data['commission_structure'] ?? []),
            'base_commission_rate' => $data['base_commission_rate'] ?? 5.00,
            'recurring_commission_rate' => $data['recurring_commission_rate'] ?? 2.00,
            'sales_territories' => json_encode($data['sales_territories'] ?? []),
            'marketing_budget' => $data['marketing_budget'] ?? 0.00,
            'customer_acquisition_target' => $data['customer_acquisition_target'] ?? 10,
        ];

        return DB::table('sales_partners')->insert($salesData);
    }

    private function generateDistributedUserId() {
        // Snowflake 알고리즘 또는 유사한 분산 ID 생성
        $timestamp = (int)(microtime(true) * 1000);
        $nodeId = config('app.node_id', 1);
        $sequence = Cache::increment('user_id_sequence', 1) % 4096;

        return ($timestamp << 22) | ($nodeId << 12) | $sequence;
    }

    private function generatePartnerCode($partnerType) {
        $prefix = $partnerType === 'service' ? 'SVC' : 'SAL';
        return $prefix . date('Y') . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}
```


### 11.4 파트너 유형별 등급 및 수익 분배 시스템

#### 11.4.1 서비스 파트너 등급 체계 (엔지니어)

**등급 분류 및 기준**:
```php
class PartnerTierSystem {
    public function getTierStructure() {
        return [
            'bronze' => [
                'name' => '브론즈',
                'requirements' => [
                    'experience_months' => 0,
                    'completed_jobs' => 0,
                    'average_rating' => 0,
                    'certification_level' => 'basic'
                ],
                'benefits' => [
                    'commission_rate' => 60,  // 60%
                    'priority_level' => 1,
                    'bonus_eligibility' => false,
                    'training_access' => 'basic'
                ]
            ],
            'silver' => [
                'name' => '실버',
                'requirements' => [
                    'experience_months' => 6,
                    'completed_jobs' => 50,
                    'average_rating' => 4.0,
                    'certification_level' => 'intermediate'
                ],
                'benefits' => [
                    'commission_rate' => 65,  // 65%
                    'priority_level' => 2,
                    'bonus_eligibility' => true,
                    'training_access' => 'advanced'
                ]
            ],
            'gold' => [
                'name' => '골드',
                'requirements' => [
                    'experience_months' => 12,
                    'completed_jobs' => 150,
                    'average_rating' => 4.3,
                    'certification_level' => 'advanced'
                ],
                'benefits' => [
                    'commission_rate' => 70,  // 70%
                    'priority_level' => 3,
                    'bonus_eligibility' => true,
                    'training_access' => 'premium'
                ]
            ],
            'platinum' => [
                'name' => '플래티넘',
                'requirements' => [
                    'experience_months' => 24,
                    'completed_jobs' => 300,
                    'average_rating' => 4.5,
                    'certification_level' => 'expert'
                ],
                'benefits' => [
                    'commission_rate' => 75,  // 75%
                    'priority_level' => 4,
                    'bonus_eligibility' => true,
                    'training_access' => 'all',
                    'leadership_opportunities' => true
                ]
            ]
        ];
    }
}
```

#### 11.3.2 수익 분배 모델

**서비스별 수익 분배 구조**:
```php
class RevenueDistributionModel {
    public function calculateDistribution($serviceRevenue, $engineerTier, $serviceType) {
        $tierInfo = $this->getTierInfo($engineerTier);

        // 기본 수수료율
        $engineerRate = $tierInfo['commission_rate'] / 100;
        $platformRate = (100 - $tierInfo['commission_rate']) / 100;

        // 서비스 타입별 조정
        $adjustments = $this->getServiceTypeAdjustments($serviceType);

        $engineerShare = $serviceRevenue * $engineerRate * $adjustments['engineer_multiplier'];
        $platformShare = $serviceRevenue - $engineerShare;

        // 세부 분배
        return [
            'total_revenue' => $serviceRevenue,
            'engineer_share' => $engineerShare,
            'platform_share' => $platformShare,
            'breakdown' => [
                'base_commission' => $serviceRevenue * $engineerRate,
                'tier_bonus' => $this->calculateTierBonus($serviceRevenue, $engineerTier),
                'performance_bonus' => $this->calculatePerformanceBonus($serviceRevenue, $engineerTier),
                'platform_fee' => $platformShare * 0.7,  // 70% 플랫폼 운영비
                'marketing_fund' => $platformShare * 0.2, // 20% 마케팅
                'reserve_fund' => $platformShare * 0.1    // 10% 적립금
            ]
        ];
    }
}
```

### 11.4 서비스 워크플로우 및 작업 배정

#### 11.4.1 자동 배정 시스템

**지능형 엔지니어 매칭**:
```php
class IntelligentEngineerAssignment {
    public function assignEngineer($appointment) {
        $criteria = [
            'location_proximity' => 0.25,    // 25% - 위치 근접성
            'skill_compatibility' => 0.25,   // 25% - 기술 적합성
            'availability' => 0.20,          // 20% - 가용성
            'tier_level' => 0.15,           // 15% - 엔지니어 등급
            'workload_balance' => 0.10,      // 10% - 업무량 균형
            'customer_preference' => 0.05    // 5% - 고객 선호도
        ];

        $candidates = $this->getCandidateEngineers($appointment);
        $scoredCandidates = [];

        foreach ($candidates as $engineer) {
            $score = 0;

            // 위치 점수 (거리 기반)
            $distance = $this->calculateDistance($engineer->location, $appointment->location);
            $locationScore = max(0, (50 - $distance) / 50); // 50km 기준 정규화
            $score += $locationScore * $criteria['location_proximity'];

            // 기술 적합성 점수
            $skillMatch = $this->calculateSkillMatch($engineer->skills, $appointment->required_skills);
            $score += $skillMatch * $criteria['skill_compatibility'];

            // 가용성 점수
            $availability = $this->checkTimeAvailability($engineer, $appointment->scheduled_time);
            $score += $availability * $criteria['availability'];

            // 엔지니어 등급 점수
            $tierScore = $this->getTierScore($engineer->tier);
            $score += $tierScore * $criteria['tier_level'];

            // 업무량 균형 점수
            $workloadScore = $this->calculateWorkloadBalance($engineer);
            $score += $workloadScore * $criteria['workload_balance'];

            // 고객 선호도 점수 (이전 서비스 이력 기반)
            $preferenceScore = $this->getCustomerPreferenceScore($appointment->customer_id, $engineer->id);
            $score += $preferenceScore * $criteria['customer_preference'];

            $scoredCandidates[] = [
                'engineer' => $engineer,
                'score' => $score,
                'distance_km' => $distance,
                'estimated_travel_time' => $this->estimateTravelTime($engineer->location, $appointment->location)
            ];
        }

        // 최고 점수 엔지니어 선택
        $bestMatch = collect($scoredCandidates)->sortByDesc('score')->first();

        return $this->createAssignment($appointment, $bestMatch);
    }
}
```

#### 11.4.2 작업 진행 상황 관리

**실시간 작업 추적 시스템**:
```php
class TaskProgressManager {
    public function trackTaskProgress($taskId, $status, $progress = null) {
        $task = ServiceTask::find($taskId);

        // 상태 업데이트
        $task->update([
            'status' => $status,
            'progress_percentage' => $progress,
            'last_update' => now()
        ]);

        // 관련 당사자들에게 알림
        $this->notifyStakeholders($task, $status);

        // 상태별 자동 액션
        switch($status) {
            case 'started':
                $this->onTaskStarted($task);
                break;
            case 'in_progress':
                $this->onTaskInProgress($task, $progress);
                break;
            case 'completed':
                $this->onTaskCompleted($task);
                break;
            case 'delayed':
                $this->onTaskDelayed($task);
                break;
        }
    }

    private function notifyStakeholders($task, $status) {
        // 고객에게 알림
        $this->notifyCustomer($task->appointment->customer, $task, $status);

        // 관리자에게 알림 (지연이나 문제 발생 시)
        if (in_array($status, ['delayed', 'cancelled', 'problem'])) {
            $this->notifyAdministrators($task, $status);
        }

        // 엔지니어에게 확인 알림
        $this->notifyEngineer($task->engineer, $task, $status);
    }
}
```

#### 11.4.3 영업 파트너 등급 체계 (총판/리셀러/에이전시)

**영업 파트너 등급 분류**:
```php
class SalesPartnerTierSystem {
    public function getSalesTierStructure() {
        return [
            'agent' => [
                'name' => '에이전트',
                'requirements' => [
                    'min_monthly_customers' => 5,
                    'min_monthly_revenue' => 1000000, // 100만원
                    'min_conversion_rate' => 10.0,
                ],
                'benefits' => [
                    'base_commission_rate' => 5.0,
                    'recurring_commission_rate' => 2.0,
                    'performance_bonus' => 0,
                    'marketing_support' => 'basic',
                    'territory_exclusive' => false
                ]
            ],
            'reseller' => [
                'name' => '리셀러',
                'requirements' => [
                    'min_monthly_customers' => 15,
                    'min_monthly_revenue' => 3000000, // 300만원
                    'min_conversion_rate' => 15.0,
                    'min_experience_months' => 6,
                ],
                'benefits' => [
                    'base_commission_rate' => 7.0,
                    'recurring_commission_rate' => 3.0,
                    'performance_bonus' => 100000,
                    'marketing_support' => 'standard',
                    'territory_exclusive' => true
                ]
            ],
            'distributor' => [
                'name' => '총판',
                'requirements' => [
                    'min_monthly_customers' => 50,
                    'min_monthly_revenue' => 10000000, // 1000만원
                    'min_conversion_rate' => 20.0,
                    'min_experience_months' => 12,
                    'sub_partners_count' => 5,
                ],
                'benefits' => [
                    'base_commission_rate' => 10.0,
                    'recurring_commission_rate' => 5.0,
                    'performance_bonus' => 500000,
                    'marketing_support' => 'premium',
                    'territory_exclusive' => true,
                    'sub_partner_override' => 2.0
                ]
            ],
            'master' => [
                'name' => '마스터 파트너',
                'requirements' => [
                    'min_monthly_customers' => 100,
                    'min_monthly_revenue' => 25000000, // 2500만원
                    'min_conversion_rate' => 25.0,
                    'min_experience_months' => 24,
                    'sub_partners_count' => 15,
                ],
                'benefits' => [
                    'base_commission_rate' => 12.0,
                    'recurring_commission_rate' => 7.0,
                    'performance_bonus' => 1000000,
                    'marketing_support' => 'vip',
                    'territory_exclusive' => true,
                    'sub_partner_override' => 3.0,
                    'annual_incentive' => 5000000
                ]
            ]
        ];
    }
}
```

**영업 파트너 커미션 구조**:
```php
class SalesCommissionCalculator {
    public function calculateCommission($salesPartner, $subscription, $month = null) {
        $month = $month ?: now()->month;
        $tierData = $this->getTierData($salesPartner->sales_tier);

        $commission = [
            'base_commission' => 0,
            'recurring_commission' => 0,
            'performance_bonus' => 0,
            'sub_partner_override' => 0,
            'total' => 0
        ];

        // 1. 기본 커미션 (신규 고객 유치)
        if ($subscription->is_new_customer) {
            $commission['base_commission'] =
                $subscription->monthly_amount *
                ($tierData['base_commission_rate'] / 100);
        }

        // 2. 지속 수익 커미션 (기존 고객 월 결제)
        $commission['recurring_commission'] =
            $subscription->monthly_amount *
            ($tierData['recurring_commission_rate'] / 100);

        // 3. 성과 보너스 (월 목표 달성 시)
        if ($this->hasMetMonthlyTarget($salesPartner, $month)) {
            $commission['performance_bonus'] = $tierData['performance_bonus'];
        }

        // 4. 하위 파트너 오버라이드 커미션
        if (isset($tierData['sub_partner_override'])) {
            $commission['sub_partner_override'] =
                $this->calculateSubPartnerOverride(
                    $salesPartner,
                    $tierData['sub_partner_override'],
                    $month
                );
        }

        $commission['total'] = array_sum($commission);

        return $commission;
    }

    private function calculateSubPartnerOverride($salesPartner, $overrideRate, $month) {
        $subPartners = $salesPartner->subPartners()->get();
        $overrideCommission = 0;

        foreach ($subPartners as $subPartner) {
            $subPartnerRevenue = $this->getMonthlyRevenue($subPartner, $month);
            $overrideCommission += $subPartnerRevenue * ($overrideRate / 100);
        }

        return $overrideCommission;
    }
}
```

#### 11.4.4 영업 파트너 트리 구조 관리 시스템

**계층형 셀러 모집 및 관리**:

```php
class SalesPartnerTreeManager {

    /**
     * 새로운 하위 셀러 추가
     */
    public function recruitDownlineSeller($parent_partner_id, $new_seller_data) {
        $parent = SalesPartner::find($parent_partner_id);

        // 권한 검증
        $this->validateRecruitmentRights($parent);

        // 트리 깊이 제한 검증 (최대 7레벨)
        if ($this->getTreeDepth($parent) >= 7) {
            throw new MaxDepthExceededException('최대 7단계까지만 하위 조직 구성 가능');
        }

        // 신규 셀러 생성
        $new_seller = $this->createNewSeller($new_seller_data, $parent);

        // 트리 구조 업데이트
        $this->updateTreeStructure($parent, $new_seller);

        // 상위 라인 커미션 구조 설정
        $this->setupCommissionLineage($new_seller);

        return $new_seller;
    }

    /**
     * 트리 구조 데이터 생성
     */
    private function updateTreeStructure($parent, $new_seller) {
        // Nested Set Model을 사용한 트리 구조 관리
        $parent_left = $parent->tree_left;
        $parent_right = $parent->tree_right;

        // 기존 노드들의 left/right 값 조정
        SalesPartner::where('tree_left', '>', $parent_right)
            ->increment('tree_left', 2);
        SalesPartner::where('tree_right', '>=', $parent_right)
            ->increment('tree_right', 2);

        // 새 셀러의 트리 위치 설정
        $new_seller->update([
            'parent_id' => $parent->id,
            'tree_left' => $parent_right,
            'tree_right' => $parent_right + 1,
            'tree_depth' => $parent->tree_depth + 1,
            'lineage_path' => $parent->lineage_path . '/' . $parent->id
        ]);
    }

    /**
     * 다단계 커미션 구조 설정
     */
    private function setupCommissionLineage($seller) {
        $ancestors = $this->getAncestors($seller);
        $commission_levels = $this->getCommissionLevels();

        foreach ($ancestors as $level => $ancestor) {
            if (isset($commission_levels[$level])) {
                SalesCommissionLineage::create([
                    'downstream_partner_id' => $seller->id,
                    'upstream_partner_id' => $ancestor->id,
                    'level_depth' => $level + 1,
                    'commission_rate' => $commission_levels[$level],
                    'is_active' => true
                ]);
            }
        }
    }

    private function getCommissionLevels() {
        return [
            0 => 3.0, // 직속 상위 (1단계) - 3%
            1 => 2.0, // 2단계 상위 - 2%
            2 => 1.5, // 3단계 상위 - 1.5%
            3 => 1.0, // 4단계 상위 - 1%
            4 => 0.5, // 5단계 상위 - 0.5%
            5 => 0.3, // 6단계 상위 - 0.3%
            6 => 0.2  // 7단계 상위 - 0.2%
        ];
    }
}
```

**셀러 트리 관리 권한 시스템**:

```php
class SellerManagementRights {

    public function getManagementCapabilities($seller_tier, $tree_depth) {
        $capabilities = [
            'master' => [
                'max_direct_recruits' => 50,
                'max_tree_depth' => 7,
                'can_manage_levels' => 7, // 하위 7단계까지 관리
                'can_transfer_downlines' => true,
                'can_terminate_downlines' => true,
                'can_adjust_commissions' => true,
                'monthly_recruitment_bonus' => 500000
            ],
            'distributor' => [
                'max_direct_recruits' => 20,
                'max_tree_depth' => 5,
                'can_manage_levels' => 5,
                'can_transfer_downlines' => true,
                'can_terminate_downlines' => true,
                'can_adjust_commissions' => false,
                'monthly_recruitment_bonus' => 200000
            ],
            'reseller' => [
                'max_direct_recruits' => 10,
                'max_tree_depth' => 3,
                'can_manage_levels' => 3,
                'can_transfer_downlines' => false,
                'can_terminate_downlines' => true,
                'can_adjust_commissions' => false,
                'monthly_recruitment_bonus' => 100000
            ],
            'agent' => [
                'max_direct_recruits' => 5,
                'max_tree_depth' => 2,
                'can_manage_levels' => 2,
                'can_transfer_downlines' => false,
                'can_terminate_downlines' => false,
                'can_adjust_commissions' => false,
                'monthly_recruitment_bonus' => 50000
            ]
        ];

        return $capabilities[$seller_tier] ?? $capabilities['agent'];
    }

    /**
     * 하위 셀러 상태 변경 (활성/비활성/탈퇴)
     */
    public function changeDownlineStatus($manager_id, $target_seller_id, $new_status, $reason = null) {
        $manager = SalesPartner::find($manager_id);
        $target = SalesPartner::find($target_seller_id);

        // 관리 권한 검증
        if (!$this->canManageDownline($manager, $target)) {
            throw new UnauthorizedException('해당 셀러 관리 권한이 없습니다.');
        }

        $old_status = $target->status;

        // 상태 변경 실행
        $target->update([
            'status' => $new_status,
            'status_changed_at' => now(),
            'status_changed_by' => $manager_id,
            'status_change_reason' => $reason
        ]);

        // 상태 변경 이력 기록
        SellerStatusHistory::create([
            'seller_id' => $target_seller_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'changed_by' => $manager_id,
            'reason' => $reason,
            'changed_at' => now()
        ]);

        // 하위 조직에 미치는 영향 처리
        $this->handleDownlineStatusImpact($target, $new_status);

        return $target;
    }

    private function canManageDownline($manager, $target) {
        // 직속 하위인지 확인
        if ($target->parent_id === $manager->id) {
            return true;
        }

        // 관리 가능한 레벨 내인지 확인
        $capabilities = $this->getManagementCapabilities($manager->sales_tier, $manager->tree_depth);
        $depth_difference = $target->tree_depth - $manager->tree_depth;

        return $depth_difference <= $capabilities['can_manage_levels'] &&
               $this->isInDownlineTree($manager, $target);
    }
}
```

**트리 구조 데이터베이스 스키마 확장**:

```sql
-- 영업 파트너 트리 구조 정보 추가
ALTER TABLE sales_partners ADD COLUMN (
    parent_id BIGINT NULL REFERENCES partners(id),
    tree_left INT NOT NULL DEFAULT 1,
    tree_right INT NOT NULL DEFAULT 2,
    tree_depth TINYINT NOT NULL DEFAULT 0,
    lineage_path TEXT, -- 예: "/1/5/12/25"
    direct_recruits_count SMALLINT DEFAULT 0,
    total_downlines_count INT DEFAULT 0,
    recruitment_date DATE NULL,
    recruited_by BIGINT NULL REFERENCES partners(id),

    INDEX idx_tree_structure (tree_left, tree_right),
    INDEX idx_parent_child (parent_id, tree_depth),
    INDEX idx_lineage_path (lineage_path(100))
);

-- 다단계 커미션 계보 테이블
CREATE TABLE sales_commission_lineage (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    downstream_partner_id BIGINT NOT NULL REFERENCES partners(id),
    upstream_partner_id BIGINT NOT NULL REFERENCES partners(id),
    level_depth TINYINT NOT NULL, -- 1=직속상위, 2=2단계상위, etc.
    commission_rate DECIMAL(5,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    effective_from DATE DEFAULT CURRENT_DATE,
    effective_until DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_lineage (downstream_partner_id, upstream_partner_id, level_depth),
    INDEX idx_downstream (downstream_partner_id, is_active),
    INDEX idx_upstream (upstream_partner_id, level_depth, is_active)
);

-- 셀러 상태 변경 이력
CREATE TABLE seller_status_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    seller_id BIGINT NOT NULL REFERENCES partners(id),
    old_status ENUM('pending', 'active', 'suspended', 'inactive', 'terminated'),
    new_status ENUM('pending', 'active', 'suspended', 'inactive', 'terminated'),
    changed_by BIGINT NOT NULL REFERENCES partners(id),
    reason TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_seller_history (seller_id, changed_at),
    INDEX idx_status_changes (new_status, changed_at)
);

-- 셀러 모집 성과 추적
CREATE TABLE recruitment_performance (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    recruiter_id BIGINT NOT NULL REFERENCES partners(id),
    recruited_id BIGINT NOT NULL REFERENCES partners(id),
    recruitment_month DATE NOT NULL,
    recruitment_bonus DECIMAL(10,2) DEFAULT 0,
    recruiter_tier_at_time VARCHAR(20),
    status ENUM('active', 'churned', 'transferred') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_recruiter_performance (recruiter_id, recruitment_month),
    INDEX idx_monthly_stats (recruitment_month, status)
);
```

**실시간 트리 현황 조회 시스템**:

```php
class SalesTreeAnalytics {

    /**
     * 셀러의 전체 조직 현황 조회
     */
    public function getOrganizationOverview($seller_id) {
        $seller = SalesPartner::find($seller_id);

        return [
            'seller_info' => [
                'id' => $seller->id,
                'name' => $seller->name,
                'tier' => $seller->sales_tier,
                'tree_depth' => $seller->tree_depth,
                'joined_date' => $seller->joined_at
            ],

            'organization_stats' => [
                'direct_recruits' => $this->getDirectRecruitsCount($seller_id),
                'total_downlines' => $this->getTotalDownlinesCount($seller_id),
                'active_downlines' => $this->getActiveDownlinesCount($seller_id),
                'max_depth' => $this->getMaxDepthInTree($seller_id),
                'monthly_new_recruits' => $this->getMonthlyRecruits($seller_id),
            ],

            'performance_metrics' => [
                'personal_sales' => $this->getPersonalSales($seller_id),
                'organization_sales' => $this->getOrganizationSales($seller_id),
                'commission_earned' => $this->getTotalCommissions($seller_id),
                'recruitment_bonuses' => $this->getRecruitmentBonuses($seller_id)
            ],

            'tree_structure' => $this->getTreeHierarchy($seller_id),

            'management_rights' => $this->getManagementCapabilities($seller->sales_tier, $seller->tree_depth)
        ];
    }

    /**
     * 트리 구조 시각화를 위한 계층 데이터
     */
    public function getTreeHierarchy($seller_id, $max_levels = 3) {
        $seller = SalesPartner::find($seller_id);

        // Nested Set Model을 이용한 효율적인 트리 조회
        $downlines = SalesPartner::where('tree_left', '>', $seller->tree_left)
            ->where('tree_right', '<', $seller->tree_right)
            ->where('tree_depth', '<=', $seller->tree_depth + $max_levels)
            ->orderBy('tree_left')
            ->get();

        return $this->buildHierarchicalArray($downlines, $seller->tree_depth);
    }

    /**
     * 월별 조직 성장 추이
     */
    public function getOrganizationGrowthTrend($seller_id, $months = 12) {
        $start_date = now()->subMonths($months)->startOfMonth();

        return RecruitmentPerformance::where('recruiter_id', $seller_id)
            ->where('recruitment_month', '>=', $start_date)
            ->selectRaw('
                DATE_FORMAT(recruitment_month, "%Y-%m") as month,
                COUNT(*) as new_recruits,
                SUM(recruitment_bonus) as total_bonus,
                COUNT(CASE WHEN status = "active" THEN 1 END) as active_recruits
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
```

### 11.5 성과 평가 및 관리

#### 11.5.1 엔지니어 성과 관리

**종합 성과 평가 시스템**:
```php
class EngineerPerformanceManager {
    public function calculateMonthlyPerformance($engineerId, $month) {
        $engineer = Engineer::find($engineerId);
        $tasks = $this->getMonthlyTasks($engineerId, $month);

        return [
            'basic_metrics' => [
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'completed')->count(),
                'completion_rate' => $this->calculateCompletionRate($tasks),
                'average_rating' => $this->calculateAverageRating($tasks),
                'punctuality_rate' => $this->calculatePunctualityRate($tasks),
                'rework_rate' => $this->calculateReworkRate($tasks)
            ],
            'quality_metrics' => [
                'customer_satisfaction' => $this->getCustomerSatisfaction($tasks),
                'quality_score' => $this->calculateQualityScore($tasks),
                'complaint_count' => $this->getComplaintCount($tasks),
                'compliment_count' => $this->getComplimentCount($tasks)
            ],
            'financial_metrics' => [
                'total_earnings' => $this->calculateTotalEarnings($tasks),
                'average_task_value' => $this->calculateAverageTaskValue($tasks),
                'efficiency_bonus' => $this->calculateEfficiencyBonus($engineer, $tasks)
            ],
            'tier_evaluation' => [
                'current_tier' => $engineer->tier,
                'eligible_for_promotion' => $this->checkPromotionEligibility($engineer),
                'next_tier_requirements' => $this->getNextTierRequirements($engineer->tier)
            ]
        ];
    }
}
```

### 11.6 운영 정책 관리

#### 11.6.1 동적 정책 설정

**운영 정책 관리 시스템**:
```php
class OperationPolicyManager {
    public function getPolicySettings() {
        return [
            'assignment_policies' => [
                'auto_assignment_enabled' => true,
                'manual_override_allowed' => true,
                'max_distance_km' => 50,
                'preferred_tier_for_premium_customers' => 'gold',
                'emergency_assignment_timeout_minutes' => 30
            ],
            'commission_policies' => [
                'tier_adjustment_period' => 'monthly',
                'performance_bonus_threshold' => 4.5,
                'penalty_for_low_rating' => 0.1,
                'late_completion_penalty' => 0.05
            ],
            'quality_policies' => [
                'minimum_rating_threshold' => 3.0,
                'automatic_rework_trigger' => 2.5,
                'customer_complaint_investigation' => true,
                'mandatory_photo_evidence' => true
            ],
            'scheduling_policies' => [
                'advance_booking_days' => 7,
                'cancellation_fee_hours' => 24,
                'rescheduling_limit' => 2,
                'emergency_service_surcharge' => 0.5
            ]
        ];
    }

    public function updatePolicy($category, $key, $value) {
        $policy = OperationPolicy::where('category', $category)
                                ->where('key', $key)
                                ->first();

        if ($policy) {
            $policy->update(['value' => $value]);
        } else {
            OperationPolicy::create([
                'category' => $category,
                'key' => $key,
                'value' => $value,
                'updated_by' => auth()->id()
            ]);
        }

        // 정책 변경 로그
        $this->logPolicyChange($category, $key, $value);

        // 영향받는 시스템들에 알림
        $this->notifySystemComponents($category, $key, $value);
    }
}
```

## 12. 3-Tier 시스템을 위한 추가 데이터베이스 스키마

### 12.1 엔지니어 관리 테이블

```sql
-- 엔지니어 등급 시스템
CREATE TABLE engineer_tiers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tier_code VARCHAR(20) UNIQUE NOT NULL,
    tier_name VARCHAR(100) NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL, -- 60.00, 65.00, 70.00, 75.00
    priority_level INT NOT NULL,
    requirements JSON NOT NULL,
    benefits JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 엔지니어 프로필 확장
CREATE TABLE engineers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    engineer_code VARCHAR(20) UNIQUE NOT NULL,
    current_tier VARCHAR(20) NOT NULL DEFAULT 'bronze',
    status ENUM('pending', 'active', 'inactive', 'suspended') DEFAULT 'pending',
    hire_date DATE,
    total_earnings DECIMAL(12,2) DEFAULT 0,
    current_month_earnings DECIMAL(10,2) DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0,
    total_completed_jobs INT DEFAULT 0,
    punctuality_rate DECIMAL(5,2) DEFAULT 0,
    customer_satisfaction DECIMAL(5,2) DEFAULT 0,
    last_tier_evaluation TIMESTAMP NULL,
    next_tier_eligible_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (current_tier) REFERENCES engineer_tiers(tier_code),
    UNIQUE KEY unique_engineer_code (engineer_code)
);

-- 엔지니어 지원 및 온보딩
CREATE TABLE engineer_applications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    application_status ENUM('submitted', 'reviewing', 'interview', 'approved', 'rejected') DEFAULT 'submitted',
    personal_info JSON NOT NULL,
    experience_info JSON NOT NULL,
    skills_info JSON NOT NULL,
    documents JSON NOT NULL, -- 이력서, 자격증, 신분증 등
    interview_date TIMESTAMP NULL,
    interview_notes TEXT,
    approval_date TIMESTAMP NULL,
    approved_by BIGINT,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

### 12.2 작업 배정 및 관리 테이블

```sql
-- 작업 배정
CREATE TABLE task_assignments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    appointment_id BIGINT NOT NULL,
    engineer_id BIGINT NOT NULL,
    assigned_by BIGINT, -- 관리자 ID (자동 배정 시 NULL)
    assignment_type ENUM('auto', 'manual', 'emergency') DEFAULT 'auto',
    assignment_score DECIMAL(5,2), -- 자동 배정 시 매칭 점수
    status ENUM('assigned', 'accepted', 'rejected', 'in_progress', 'completed', 'cancelled') DEFAULT 'assigned',
    estimated_travel_time INT, -- 분
    estimated_service_time INT, -- 분
    actual_travel_time INT NULL,
    actual_service_time INT NULL,
    acceptance_deadline TIMESTAMP,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (engineer_id) REFERENCES engineers(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    INDEX idx_engineer_status (engineer_id, status),
    INDEX idx_appointment_assignment (appointment_id)
);

-- 작업 진행 상황
CREATE TABLE task_progress (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    assignment_id BIGINT NOT NULL,
    status ENUM('pending', 'started', 'in_progress', 'paused', 'completed', 'delayed', 'cancelled') NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    current_step VARCHAR(255),
    notes TEXT,
    photo_evidence JSON,
    location_data JSON, -- GPS 위치 정보
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES task_assignments(id),
    INDEX idx_assignment_status (assignment_id, status)
);
```

### 12.3 수익 분배 및 정산 테이블

```sql
-- 수익 분배 기록
CREATE TABLE revenue_distributions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    assignment_id BIGINT NOT NULL,
    total_service_revenue DECIMAL(10,2) NOT NULL,
    engineer_share DECIMAL(10,2) NOT NULL,
    platform_share DECIMAL(10,2) NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL,
    tier_bonus DECIMAL(8,2) DEFAULT 0,
    performance_bonus DECIMAL(8,2) DEFAULT 0,
    penalties DECIMAL(8,2) DEFAULT 0,
    final_engineer_amount DECIMAL(10,2) NOT NULL,
    distribution_status ENUM('calculated', 'approved', 'paid') DEFAULT 'calculated',
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by BIGINT,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (assignment_id) REFERENCES task_assignments(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- 엔지니어 정산
CREATE TABLE engineer_payouts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    engineer_id BIGINT NOT NULL,
    payout_period_start DATE NOT NULL,
    payout_period_end DATE NOT NULL,
    total_assignments INT NOT NULL,
    total_earnings DECIMAL(12,2) NOT NULL,
    base_commissions DECIMAL(12,2) NOT NULL,
    bonuses DECIMAL(10,2) DEFAULT 0,
    penalties DECIMAL(10,2) DEFAULT 0,
    tax_withheld DECIMAL(10,2) DEFAULT 0,
    net_amount DECIMAL(12,2) NOT NULL,
    payout_status ENUM('pending', 'approved', 'processing', 'completed', 'failed') DEFAULT 'pending',
    payout_method ENUM('bank_transfer', 'digital_wallet', 'check') DEFAULT 'bank_transfer',
    bank_account_info JSON,
    processed_at TIMESTAMP NULL,
    transaction_reference VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (engineer_id) REFERENCES engineers(id),
    INDEX idx_engineer_period (engineer_id, payout_period_start)
);
```

### 12.4 성과 평가 및 등급 관리 테이블

```sql
-- 엔지니어 성과 평가
CREATE TABLE engineer_performance_reviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    engineer_id BIGINT NOT NULL,
    review_period_start DATE NOT NULL,
    review_period_end DATE NOT NULL,
    total_tasks INT NOT NULL,
    completed_tasks INT NOT NULL,
    completion_rate DECIMAL(5,2) NOT NULL,
    average_rating DECIMAL(3,2) NOT NULL,
    punctuality_rate DECIMAL(5,2) NOT NULL,
    customer_satisfaction DECIMAL(5,2) NOT NULL,
    quality_score DECIMAL(5,2) NOT NULL,
    rework_rate DECIMAL(5,2) NOT NULL,
    complaint_count INT DEFAULT 0,
    compliment_count INT DEFAULT 0,
    total_earnings DECIMAL(12,2) NOT NULL,
    current_tier VARCHAR(20) NOT NULL,
    recommended_tier VARCHAR(20),
    tier_change_effective_date DATE NULL,
    reviewed_by BIGINT NOT NULL,
    review_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (engineer_id) REFERENCES engineers(id),
    FOREIGN KEY (current_tier) REFERENCES engineer_tiers(tier_code),
    FOREIGN KEY (recommended_tier) REFERENCES engineer_tiers(tier_code),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- 등급 변경 이력
CREATE TABLE engineer_tier_changes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    engineer_id BIGINT NOT NULL,
    from_tier VARCHAR(20),
    to_tier VARCHAR(20) NOT NULL,
    change_reason ENUM('promotion', 'demotion', 'manual_adjustment', 'performance_review'),
    effective_date DATE NOT NULL,
    performance_data JSON,
    changed_by BIGINT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (engineer_id) REFERENCES engineers(id),
    FOREIGN KEY (from_tier) REFERENCES engineer_tiers(tier_code),
    FOREIGN KEY (to_tier) REFERENCES engineer_tiers(tier_code),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);
```

### 12.5 운영 정책 및 설정 테이블

```sql
-- 운영 정책 설정
CREATE TABLE operation_policies (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100) NOT NULL,
    policy_key VARCHAR(150) NOT NULL,
    policy_value JSON NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    updated_by BIGINT NOT NULL,
    effective_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id),
    UNIQUE KEY unique_policy (category, policy_key)
);

-- 수수료율 관리
CREATE TABLE commission_rates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_type VARCHAR(100) NOT NULL,
    engineer_tier VARCHAR(20) NOT NULL,
    base_rate DECIMAL(5,2) NOT NULL,
    bonus_rate DECIMAL(5,2) DEFAULT 0,
    effective_date DATE NOT NULL,
    end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (engineer_tier) REFERENCES engineer_tiers(tier_code),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_service_tier_date (service_type, engineer_tier, effective_date)
);
```

### 12.6 고객 평가 및 피드백 테이블

```sql
-- 서비스 평가 (고객이 엔지니어 평가)
CREATE TABLE service_reviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    assignment_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    engineer_id BIGINT NOT NULL,
    overall_rating DECIMAL(2,1) NOT NULL, -- 1.0 to 5.0
    quality_rating DECIMAL(2,1),
    punctuality_rating DECIMAL(2,1),
    professionalism_rating DECIMAL(2,1),
    communication_rating DECIMAL(2,1),
    cleanliness_rating DECIMAL(2,1),
    written_feedback TEXT,
    would_recommend BOOLEAN,
    anonymous BOOLEAN DEFAULT FALSE,
    photo_evidence JSON,
    response_from_engineer TEXT,
    response_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES task_assignments(id),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (engineer_id) REFERENCES engineers(id),
    INDEX idx_engineer_rating (engineer_id, overall_rating),
    INDEX idx_customer_reviews (customer_id)
);

-- 엔지니어가 고객 평가 (선택사항)
CREATE TABLE customer_reviews_by_engineers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    assignment_id BIGINT NOT NULL,
    engineer_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    cooperation_rating DECIMAL(2,1),
    communication_rating DECIMAL(2,1),
    preparation_rating DECIMAL(2,1),
    accessibility_rating DECIMAL(2,1),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES task_assignments(id),
    FOREIGN KEY (engineer_id) REFERENCES engineers(id),
    FOREIGN KEY (customer_id) REFERENCES users(id)
);
```

### 12.7 알림 및 커뮤니케이션 확장

```sql
-- 다중 사용자 알림 시스템
CREATE TABLE multi_user_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    notification_type ENUM('task_assignment', 'task_update', 'payment_notification', 'performance_alert', 'policy_update'),
    reference_type VARCHAR(100), -- 'assignment', 'payout', 'review' 등
    reference_id BIGINT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    sender_id BIGINT,
    target_user_type ENUM('customer', 'engineer', 'admin', 'all'),
    target_user_ids JSON, -- 특정 사용자들에게만 발송 시
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    INDEX idx_target_type (target_user_type, sent_at),
    INDEX idx_reference (reference_type, reference_id)
);

-- 알림 수신 상태
CREATE TABLE notification_recipients (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    notification_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    delivery_status ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending',
    delivery_channel ENUM('in_app', 'email', 'sms', 'push'),
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (notification_id) REFERENCES multi_user_notifications(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_status (user_id, delivery_status)
);
```

## 13. 시스템 통합 및 최종 검증

### 13.1 feature.md 완성도 검증

**✅ 3-Tier 사용자 시스템 완전 구현**
- 관리자(/admin), 고객(/home), 엔지니어(/engineer) 포털 분리
- 각 사용자 그룹별 전용 기능 및 워크플로우
- 통합 알림 및 커뮤니케이션 시스템

**✅ 엔지니어 등급 및 수익 분배 시스템**
- 브론즈(60%) → 실버(65%) → 골드(70%) → 플래티넘(75%) 등급 체계
- 성과 기반 자동 등급 조정 시스템
- 투명한 수익 분배 및 정산 프로세스

**✅ 지능형 작업 배정 및 관리**
- 다중 조건 기반 자동 엔지니어 매칭
- 실시간 작업 진행 추적 및 상태 관리
- 고객-엔지니어-관리자 간 삼자 피드백 시스템

**✅ 종합적 운영 관리**
- 동적 정책 설정 및 관리
- 성과 평가 및 등급 관리 자동화
- 재무 관리 및 정산 시스템

### 13.2 Sample01 에어콘 청소 서비스 대응

이제 feature.md는 sample01.md의 에어콘 청소 서비스 구현을 위한 **완전하고 충분한** 기능 명세를 제공합니다:

1. **프론트 사이트 구조** → 11.2.1 프론트엔드 사이트 구조
2. **고객 포털** → 11.2.2 고객 포털 구조
3. **관리자 패널** → 11.2.3 관리자 패널 구조
4. **엔지니어 포털** → 11.2.4 엔지니어 포털 구조
5. **작업 배정 시스템** → 11.4 서비스 워크플로우 및 작업 배정
6. **등급별 수익 분배** → 11.3 엔지니어 등급 및 수익 분배 시스템
7. **성과 평가 관리** → 11.5 성과 평가 및 관리
8. **운영 정책 관리** → 11.6 운영 정책 관리

**결론**: feature.md가 실제 운영 가능한 3-tier 에어콘 청소 서비스 플랫폼 구축을 위한 모든 필수 기능을 완벽하게 포함합니다! 🎉