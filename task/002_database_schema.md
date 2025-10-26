# 002. Database Schema Design - TDD Implementation

## 개요
Jiny 생태계 패키지 기반 구독형 서비스 관리 시스템의 데이터베이스 스키마 설계

### 기존 Jiny 패키지 테이블 활용
- **jiny/admin**: `users`, `admin_user_types`, `admin_user_logs` 테이블 활용
- **jiny/auth**: 샤딩 테이블 활용 (설정에 따라 동적으로 결정)
  - 설정: `config('jiny-auth.sharding.shard_count')` (기본값: 100)
  - 테이블 명: `config('jiny-auth.sharding.table_prefix')` + 샤드번호 (기본값: `users_001`~`users_099`)
- **새로 추가**: 서비스 관리 특화 테이블들 (아래 참조)

## 의존관계
- **선행 태스크**: [001. 프로젝트 개요](001_project_overview.md)
- **후속 태스크**: [003. 인증 시스템](003_authentication_system.md)

## TDD 접근법

### 데이터베이스 테스트 시나리오 (모두 HTTP 200 반환)

#### 1. 마이그레이션 실행 테스트
**테스트**: `DatabaseMigrationTest`

```php
public function test_all_migrations_run_successfully()
{
    // Given: 깨끗한 데이터베이스
    // When: 마이그레이션 실행
    $this->artisan('migrate:fresh');

    // Then: HTTP 200과 모든 테이블 생성 확인
    $this->assertDatabaseHas('migrations', ['migration' => '2024_01_01_000001_create_services_table']);
    $this->assertTrue(Schema::hasTable('services'));
    $this->assertTrue(Schema::hasTable('subscriptions'));
    $this->assertTrue(Schema::hasTable('partners'));
}
```

#### 2. 테이블 관계 검증 테스트
**테스트**: `DatabaseRelationshipTest`

```php
public function test_foreign_key_relationships_work()
{
    // Given: 기본 데이터
    $service = Service::factory()->create();
    $customer = User::factory()->create();

    // When: 관계 데이터 생성
    $subscription = Subscription::factory()->create([
        'service_id' => $service->id,
        'customer_id' => $customer->id
    ]);

    // Then: 관계가 올바르게 작동
    $this->assertEquals($service->id, $subscription->service->id);
    $this->assertEquals($customer->id, $subscription->customer->id);
}
```

#### 3. 동적 샤딩 구성 검증 테스트
**테스트**: `DynamicShardingConfigTest`

```php
public function test_sharding_configuration_dynamically_loaded()
{
    // Given: jiny/auth 설정이 변경된 상황
    config(['jiny-auth.sharding.shard_count' => 50]);
    config(['jiny-auth.sharding.table_prefix' => 'customers_']);

    // When: CustomerShardService 인스턴스 생성
    $shardService = new CustomerShardService();

    // Then: 동적 설정이 올바르게 로드됨
    $this->assertEquals(50, $shardService->getShardCount());
    $this->assertEquals('customers_', $shardService->getTablePrefix());

    // And: HTTP 200 응답으로 설정 확인 가능
    $response = $this->get('/admin/service/config/sharding');
    $response->assertStatus(200);
    $response->assertJson([
        'shard_count' => 50,
        'table_prefix' => 'customers_'
    ]);
}
```

## 데이터베이스 스키마 구조

### 1. Services Table (서비스 카탈로그)
```sql
CREATE TABLE services (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL COMMENT '서비스 이름',
    slug VARCHAR(255) UNIQUE NOT NULL COMMENT 'URL용 슬러그',
    description TEXT NOT NULL COMMENT '서비스 설명',
    category VARCHAR(100) NOT NULL COMMENT '카테고리 (maintenance, installation, repair)',
    pricing_model ENUM('fixed', 'hourly', 'subscription') NOT NULL COMMENT '가격 모델',
    base_price DECIMAL(12,2) NOT NULL COMMENT '기본 가격',
    features JSON COMMENT '제공 기능 목록',
    trial_config JSON COMMENT '무료 체험 설정',
    status ENUM('active', 'inactive', 'draft') DEFAULT 'draft' COMMENT '서비스 상태',
    image_url VARCHAR(500) COMMENT '서비스 이미지',
    sort_order INT DEFAULT 0 COMMENT '정렬 순서',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_pricing_model (pricing_model),
    INDEX idx_sort_order (sort_order),
    FULLTEXT idx_search (name, description)
) COMMENT='서비스 카탈로그';
```

### 2. Service Categories Table (서비스 카테고리)
```sql
CREATE TABLE service_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT '카테고리 이름',
    description TEXT COMMENT '카테고리 설명',
    icon VARCHAR(100) COMMENT '아이콘 클래스',
    sort_order INT DEFAULT 0 COMMENT '정렬 순서',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성 상태',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_sort_order (sort_order),
    INDEX idx_active (is_active)
) COMMENT='서비스 카테고리';
```

### 3. Subscriptions Table (구독 관리)
```sql
-- jiny/auth 패키지의 샤딩 테이블과 연동 (설정에 따라 동적)
CREATE TABLE subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL COMMENT '고객 ID (jiny/auth 샤딩 테이블)',
    customer_shard VARCHAR(3) NOT NULL COMMENT '고객 샤드 번호 (jiny/auth 설정 기반)',
    service_id BIGINT NOT NULL COMMENT '서비스 ID',
    status ENUM('trial', 'active', 'inactive', 'cancelled', 'expired', 'suspended') DEFAULT 'trial' COMMENT '구독 상태',
    start_date DATE NOT NULL COMMENT '시작일',
    end_date DATE COMMENT '종료일',
    trial_end_date DATE COMMENT '체험 종료일',
    billing_cycle ENUM('monthly', 'quarterly', 'yearly') NOT NULL COMMENT '청구 주기',
    amount DECIMAL(12,2) NOT NULL COMMENT '청구 금액',
    discount_amount DECIMAL(12,2) DEFAULT 0 COMMENT '할인 금액',
    next_billing_date DATE COMMENT '다음 청구일',
    cancellation_date DATE COMMENT '취소일',
    cancellation_reason TEXT COMMENT '취소 사유',
    auto_renewal BOOLEAN DEFAULT TRUE COMMENT '자동 갱신 여부',
    payment_method_id VARCHAR(100) COMMENT '결제 수단 ID',
    notes TEXT COMMENT '관리자 메모',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    INDEX idx_customer (customer_id),
    INDEX idx_service (service_id),
    INDEX idx_status (status),
    INDEX idx_billing_date (next_billing_date),
    INDEX idx_trial_end (trial_end_date),
    INDEX idx_dates (start_date, end_date)
) COMMENT='구독 관리';
```

### 4. Subscription Billings Table (청구 이력)
```sql
CREATE TABLE subscription_billings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    subscription_id BIGINT NOT NULL COMMENT '구독 ID',
    billing_date DATE NOT NULL COMMENT '청구일',
    due_date DATE NOT NULL COMMENT '납기일',
    amount DECIMAL(12,2) NOT NULL COMMENT '청구 금액',
    discount_amount DECIMAL(12,2) DEFAULT 0 COMMENT '할인 금액',
    tax_amount DECIMAL(12,2) DEFAULT 0 COMMENT '세금',
    total_amount DECIMAL(12,2) NOT NULL COMMENT '총 금액',
    status ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') DEFAULT 'pending' COMMENT '결제 상태',
    payment_method VARCHAR(50) COMMENT '결제 수단',
    transaction_id VARCHAR(255) COMMENT '거래 ID',
    paid_at TIMESTAMP NULL COMMENT '결제 완료 시간',
    invoice_number VARCHAR(100) UNIQUE COMMENT '인보이스 번호',
    notes TEXT COMMENT '결제 관련 메모',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    INDEX idx_subscription (subscription_id),
    INDEX idx_billing_date (billing_date),
    INDEX idx_status (status),
    INDEX idx_invoice (invoice_number)
) COMMENT='구독 청구 이력';
```

### 5. Partners Table (파트너 트리 - Nested Set Model)
```sql
-- jiny/auth 패키지의 샤딩 테이블과 연동 (설정에 따라 동적)
CREATE TABLE partners (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL COMMENT '사용자 ID (jiny/auth 샤딩 테이블)',
    user_shard VARCHAR(3) NOT NULL COMMENT '사용자 샤드 번호 (jiny/auth 설정 기반)',
    parent_id BIGINT COMMENT '상위 파트너 ID',
    level INTEGER NOT NULL DEFAULT 1 COMMENT '트리 레벨 (1-7)',
    left_node INTEGER NOT NULL COMMENT 'Nested Set 왼쪽 값',
    right_node INTEGER NOT NULL COMMENT 'Nested Set 오른쪽 값',
    partner_type ENUM('seller', 'engineer', 'agency', 'reseller') NOT NULL COMMENT '파트너 유형',
    commission_rate DECIMAL(5,2) NOT NULL COMMENT '커미션 비율',
    territory JSON COMMENT '담당 지역',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' COMMENT '파트너 상태',
    join_date DATE NOT NULL COMMENT '가입일',
    contract_end_date DATE COMMENT '계약 종료일',
    performance_grade ENUM('S', 'A', 'B', 'C', 'D') DEFAULT 'C' COMMENT '성과 등급',
    monthly_target DECIMAL(12,2) DEFAULT 0 COMMENT '월 목표 금액',
    notes TEXT COMMENT '관리자 메모',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (parent_id) REFERENCES partners(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_left_right (left_node, right_node),
    INDEX idx_level (level),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_type (partner_type),
    INDEX idx_performance (performance_grade),
    UNIQUE KEY unique_user_partner (user_id)
) COMMENT='파트너 트리 (Nested Set Model)';
```

### 6. Partner Invitations Table (파트너 초대)
```sql
CREATE TABLE partner_invitations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    inviter_id BIGINT NOT NULL COMMENT '초대한 파트너 ID',
    recruit_name VARCHAR(255) NOT NULL COMMENT '초대받은 사람 이름',
    recruit_email VARCHAR(255) NOT NULL COMMENT '초대받은 사람 이메일',
    recruit_phone VARCHAR(20) COMMENT '초대받은 사람 전화번호',
    invitation_code VARCHAR(50) UNIQUE NOT NULL COMMENT '초대 코드',
    partner_type ENUM('seller', 'engineer', 'agency', 'reseller') NOT NULL COMMENT '파트너 유형',
    territory JSON COMMENT '담당 지역',
    commission_rate DECIMAL(5,2) NOT NULL COMMENT '제안 커미션 비율',
    status ENUM('pending', 'accepted', 'expired', 'cancelled') DEFAULT 'pending' COMMENT '초대 상태',
    expires_at TIMESTAMP NOT NULL COMMENT '초대 만료 시간',
    accepted_at TIMESTAMP NULL COMMENT '수락 시간',
    notes TEXT COMMENT '초대 메시지',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (inviter_id) REFERENCES partners(id) ON DELETE CASCADE,
    INDEX idx_inviter (inviter_id),
    INDEX idx_email (recruit_email),
    INDEX idx_code (invitation_code),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at)
) COMMENT='파트너 초대';
```

### 7. Partner Commissions Table (커미션 관리)
```sql
CREATE TABLE partner_commissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    partner_id BIGINT NOT NULL COMMENT '커미션 받을 파트너 ID',
    source_partner_id BIGINT NOT NULL COMMENT '매출 발생 파트너 ID',
    subscription_id BIGINT NOT NULL COMMENT '구독 ID',
    billing_id BIGINT NOT NULL COMMENT '청구 ID',
    commission_level INTEGER NOT NULL COMMENT '커미션 레벨 (1-7)',
    commission_rate DECIMAL(5,2) NOT NULL COMMENT '적용된 커미션 비율',
    base_amount DECIMAL(12,2) NOT NULL COMMENT '기준 금액',
    commission_amount DECIMAL(12,2) NOT NULL COMMENT '커미션 금액',
    calculation_date DATE NOT NULL COMMENT '계산일',
    payment_date DATE COMMENT '지급일',
    status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending' COMMENT '지급 상태',
    payment_method VARCHAR(50) COMMENT '지급 방법',
    transaction_id VARCHAR(255) COMMENT '지급 거래 ID',
    notes TEXT COMMENT '커미션 관련 메모',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE,
    FOREIGN KEY (source_partner_id) REFERENCES partners(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (billing_id) REFERENCES subscription_billings(id) ON DELETE CASCADE,
    INDEX idx_partner (partner_id),
    INDEX idx_source_partner (source_partner_id),
    INDEX idx_subscription (subscription_id),
    INDEX idx_calculation_date (calculation_date),
    INDEX idx_status (status),
    INDEX idx_level (commission_level),
    UNIQUE KEY unique_commission (billing_id, partner_id, commission_level)
) COMMENT='파트너 커미션';
```

### 8. Service Executions Table (서비스 실행 이력)
```sql
CREATE TABLE service_executions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    subscription_id BIGINT NOT NULL COMMENT '구독 ID',
    partner_id BIGINT COMMENT '담당 파트너 ID',
    scheduled_date DATETIME NOT NULL COMMENT '예정 일시',
    started_at DATETIME COMMENT '시작 시간',
    completed_at DATETIME COMMENT '완료 시간',
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled', 'rescheduled', 'failed') DEFAULT 'scheduled' COMMENT '실행 상태',
    location_address TEXT COMMENT '서비스 주소',
    location_notes TEXT COMMENT '위치 관련 메모',
    work_notes TEXT COMMENT '작업 내용',
    before_images JSON COMMENT '작업 전 사진',
    after_images JSON COMMENT '작업 후 사진',
    customer_rating INTEGER COMMENT '고객 평점 (1-5)',
    customer_feedback TEXT COMMENT '고객 피드백',
    partner_notes TEXT COMMENT '파트너 메모',
    admin_notes TEXT COMMENT '관리자 메모',
    duration_minutes INTEGER COMMENT '소요 시간 (분)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE SET NULL,
    INDEX idx_subscription (subscription_id),
    INDEX idx_partner (partner_id),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_status (status),
    INDEX idx_rating (customer_rating)
) COMMENT='서비스 실행 이력';
```

### 9. Free Trial Configurations Table (무료 체험 설정)
```sql
CREATE TABLE free_trial_configs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT NOT NULL COMMENT '서비스 ID',
    name VARCHAR(255) NOT NULL COMMENT '체험 설정 이름',
    trial_type ENUM('time_based', 'usage_based', 'feature_based', 'hybrid') NOT NULL COMMENT '체험 유형',
    config_data JSON NOT NULL COMMENT '체험 설정 데이터',
    is_default BOOLEAN DEFAULT FALSE COMMENT '기본 설정 여부',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성 상태',
    target_audience JSON COMMENT '대상 고객군',
    conversion_goal DECIMAL(5,2) COMMENT '목표 전환율',
    personalization_rules JSON COMMENT '개인화 규칙',
    ab_test_group VARCHAR(50) COMMENT 'A/B 테스트 그룹',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service (service_id),
    INDEX idx_type (trial_type),
    INDEX idx_active (is_active),
    INDEX idx_ab_group (ab_test_group)
) COMMENT='무료 체험 설정';
```

### 10. Free Trial Instances Table (무료 체험 인스턴스)
```sql
CREATE TABLE free_trial_instances (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL COMMENT '고객 ID',
    service_id BIGINT NOT NULL COMMENT '서비스 ID',
    trial_config_id BIGINT NOT NULL COMMENT '체험 설정 ID',
    status ENUM('active', 'completed', 'expired', 'cancelled', 'converted') DEFAULT 'active' COMMENT '체험 상태',
    start_date DATE NOT NULL COMMENT '시작일',
    end_date DATE NOT NULL COMMENT '종료일',
    usage_data JSON COMMENT '사용량 데이터',
    conversion_date DATE COMMENT '전환일',
    subscription_id BIGINT COMMENT '전환된 구독 ID',
    feedback_rating INTEGER COMMENT '체험 평점',
    feedback_comment TEXT COMMENT '체험 후기',
    personalization_data JSON COMMENT '개인화 데이터',
    touchpoint_history JSON COMMENT '접점 이력',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (trial_config_id) REFERENCES free_trial_configs(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_service (service_id),
    INDEX idx_config (trial_config_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_conversion (conversion_date),
    UNIQUE KEY unique_customer_service_active (customer_id, service_id, status)
) COMMENT='무료 체험 인스턴스';
```

## 마이그레이션 구현 체크리스트

### 기본 마이그레이션
- [ ] **Services 테이블 마이그레이션**
  - [ ] 기본 컬럼 생성
  - [ ] 인덱스 추가
  - [ ] JSON 컬럼 설정
  - [ ] 테스트: HTTP 200 반환

- [ ] **Service Categories 테이블 마이그레이션**
  - [ ] 기본 데이터 시딩
  - [ ] 정렬 순서 설정
  - [ ] 테스트: HTTP 200 반환

- [ ] **Subscriptions 테이블 마이그레이션**
  - [ ] 외래 키 관계 설정
  - [ ] 상태 enum 정의
  - [ ] 날짜 인덱스 최적화
  - [ ] 테스트: HTTP 200 반환

### 파트너 시스템 마이그레이션
- [ ] **Partners 테이블 마이그레이션**
  - [ ] Nested Set Model 컬럼
  - [ ] 커미션 관련 컬럼
  - [ ] 성과 관리 컬럼
  - [ ] 테스트: HTTP 200 반환

- [ ] **Partner Commissions 테이블 마이그레이션**
  - [ ] 7레벨 커미션 구조
  - [ ] 중복 방지 제약조건
  - [ ] 성능 최적화 인덱스
  - [ ] 테스트: HTTP 200 반환

### 무료 체험 시스템 마이그레이션
- [ ] **Free Trial Configs 테이블 마이그레이션**
  - [ ] 4가지 체험 유형 지원
  - [ ] A/B 테스트 구조
  - [ ] 개인화 설정
  - [ ] 테스트: HTTP 200 반환

- [ ] **Free Trial Instances 테이블 마이그레이션**
  - [ ] 체험 추적 구조
  - [ ] 전환 추적 링크
  - [ ] 중복 체험 방지
  - [ ] 테스트: HTTP 200 반환

## 데이터베이스 시딩

### 기본 데이터 시딩
```php
// ServiceCategorySeeder
$categories = [
    ['name' => 'maintenance', 'description' => '정기 유지보수', 'icon' => 'fas fa-tools'],
    ['name' => 'installation', 'description' => '설치 서비스', 'icon' => 'fas fa-hammer'],
    ['name' => 'repair', 'description' => '수리 서비스', 'icon' => 'fas fa-wrench'],
    ['name' => 'cleaning', 'description' => '청소 서비스', 'icon' => 'fas fa-broom'],
];

// CommissionRateSeeder
$rates = [
    1 => 3.0,   // 1단계: 3%
    2 => 2.0,   // 2단계: 2%
    3 => 1.5,   // 3단계: 1.5%
    4 => 1.0,   // 4단계: 1%
    5 => 0.5,   // 5단계: 0.5%
    6 => 0.3,   // 6단계: 0.3%
    7 => 0.2,   // 7단계: 0.2%
];
```

## 성능 최적화

### 인덱스 전략
- **복합 인덱스**: 자주 함께 사용되는 컬럼
- **부분 인덱스**: 상태 기반 필터링
- **전문 검색**: 서비스 이름/설명 검색
- **JSON 인덱스**: 구조화된 데이터 검색

### 샤딩 전략 (jiny/auth 설정 기반)
- **고객 데이터**: jiny/auth 패키지의 샤딩 설정을 동적으로 참조
  ```php
  // jiny/auth 패키지 설정에서 동적으로 읽기
  $shardCount = config('jiny-auth.sharding.shard_count', 100);
  $tablePrefix = config('jiny-auth.sharding.table_prefix', 'users_');
  $maxUsersPerShard = config('jiny-auth.sharding.max_users_per_shard', 1000000);
  $algorithm = config('jiny-auth.sharding.shard_key_algorithm', 'modular');

  // 동적 테이블명 생성
  for ($i = 1; $i <= $shardCount; $i++) {
      $tableName = $tablePrefix . str_pad($i, 3, '0', STR_PAD_LEFT);
      // 예: users_001, users_002, ... users_100 (설정에 따라 변동)
  }
  ```
- **파트너 데이터**: 중앙 집중식 관리 (partners 테이블에 user_shard 필드로 연결)
- **거래 데이터**: 연도별 파티셔닝

## 완료 기준

### 기술적 검증
- [ ] 모든 마이그레이션 성공적으로 실행 (HTTP 200)
- [ ] 외래 키 관계 정상 작동
- [ ] 인덱스 성능 최적화 완료
- [ ] 데이터 무결성 제약조건 적용

### 기능적 검증
- [ ] CRUD 작업 모든 테이블에서 정상 동작
- [ ] Nested Set Model 트리 연산 성공
- [ ] JSON 데이터 저장/검색 정상
- [ ] 동적 샤딩 구조 테스트 완료
- [ ] jiny/auth 설정 변경 시 시스템 자동 적응 확인

---

**이전 태스크**: [001. 프로젝트 개요](001_project_overview.md)
**다음 태스크**: [003. 인증 시스템 구축](003_authentication_system.md)