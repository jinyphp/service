# Jiny Service Package Dependencies

## 개요
이 문서는 `jiny/service` 패키지가 기존 Jiny 생태계 패키지들과 어떻게 통합되는지 설명합니다.

## 주요 의존성

### 1. jiny/admin 패키지
```json
{
    "require": {
        "jiny/admin": "^1.0"
    }
}
```

#### 활용 요소
- **사용자 테이블**: `users` (중앙집중식 관리자 관리)
- **인증 미들웨어**: `admin` (기존 관리자 권한 검증)
- **관리 인터페이스**: 기존 Jiny Admin 사이드바 확장
- **권한 시스템**: `isAdmin`, `utype`, `admin_user_types` 테이블 활용

#### 통합 방식
```php
// 기존 jiny/admin 미들웨어 활용
Route::middleware(['admin'])->prefix('admin/service')->group(function () {
    // 서비스 관리 라우트들
});

// 기존 Admin 사이드바 확장
@include('jiny-service::partials.admin.sidebar')
```

### 2. jiny/auth 패키지
```json
{
    "require": {
        "jiny/auth": "^0.5"
    }
}
```

#### 활용 요소
- **사용자 테이블**: `users_001` ~ `users_099` (샤딩 구조)
- **JWT 인증**: 토큰 기반 무상태 인증
- **미들웨어**: `jwt.auth` (고객), `jwt.auth + partner.verify` (파트너)
- **확장성**: 대용량 사용자 처리를 위한 수평적 확장

#### 통합 방식
```php
// 기존 jiny/auth JWT 시스템 활용
Route::middleware(['jwt.auth'])->prefix('home/service')->group(function () {
    // 고객 서비스 라우트들
});

// 파트너용 추가 검증
Route::middleware(['jwt.auth', 'partner.verify'])->prefix('partner')->group(function () {
    // 파트너 서비스 라우트들
});
```

## 사용자 분류 및 데이터 구조

### 관리자 (Admin)
```
패키지: jiny/admin
테이블: users (중앙집중)
인증: 세션 기반
미들웨어: admin
접근: /admin/service/*
권한: isAdmin = true, utype 검증
```

### 고객 (Customer)
```
패키지: jiny/auth
테이블: 동적 샤딩 (jiny/auth 설정 기반)
  - config('jiny-auth.sharding.table_prefix') + 샤드번호
  - 예: users_001 ~ users_099 (기본값)
인증: JWT 토큰
미들웨어: jwt.auth
접근: /home/service/*
확장: 대용량 사용자 지원
```

### 파트너 (Partner/Engineer)
```
패키지: jiny/auth + 추가 검증
테이블: 동적 샤딩 + partners
  - jiny/auth 샤딩 테이블 + partners 연결
인증: JWT 토큰 + 파트너 검증
미들웨어: jwt.auth + partner.verify
접근: /partner/*
특징: MLM 트리 구조 지원
```

## 데이터베이스 통합

### 기존 테이블 활용
- `users` (jiny/admin): 관리자 계정
- 샤딩 테이블 (jiny/auth): 고객/파트너 계정
  - 테이블명: `config('jiny-auth.sharding.table_prefix')` + 샤드번호
  - 샤드 수: `config('jiny-auth.sharding.shard_count')`
  - 기본값: `users_001` ~ `users_099` (100개)
- `admin_user_types` (jiny/admin): 관리자 권한 관리
- `admin_user_logs` (jiny/admin): 관리자 활동 로그

### 새로 추가되는 테이블
- `services`: 서비스 카탈로그
- `subscriptions`: 구독 관리 (customer_id → users_0xx 참조)
- `partners`: 파트너 트리 구조 (user_id → users_0xx 참조)
- `partner_commissions`: 커미션 관리
- `free_trial_configs`: 무료 체험 설정
- `service_executions`: 서비스 실행 이력

### 외래 키 관계 (jiny/auth 설정 기반)
```sql
-- jiny/auth 설정을 읽어서 동적으로 연결
-- config('jiny-auth.sharding.shard_count') 만큼의 샤드 지원

-- 구독 테이블은 jiny/auth의 샤딩 테이블과 연결
ALTER TABLE subscriptions
ADD COLUMN customer_shard VARCHAR(3) NOT NULL COMMENT '고객 샤드 번호 (jiny/auth 설정 기반)';

-- 파트너 테이블도 jiny/auth의 샤딩 테이블과 연결
ALTER TABLE partners
ADD COLUMN user_shard VARCHAR(3) NOT NULL COMMENT '사용자 샤드 번호 (jiny/auth 설정 기반)';

-- CustomerShardService에서 동적으로 처리
-- $shardCount = config('jiny-auth.sharding.shard_count', 100);
-- $tablePrefix = config('jiny-auth.sharding.table_prefix', 'users_');
```

## 라우트 구조

### 관리자 라우트 (`/admin/service/*`)
```php
// jiny/admin 패키지 미들웨어 활용
Route::middleware(['admin'])->prefix('admin/service')->group(function () {
    Route::resource('catalog', AdminServiceCatalogController::class);
    Route::resource('subscriptions', AdminSubscriptionController::class);
    Route::resource('partners', AdminPartnerController::class);
});
```

### 고객 라우트 (`/home/service/*`)
```php
// jiny/auth 패키지 JWT 미들웨어 활용
Route::middleware(['jwt.auth'])->prefix('home/service')->group(function () {
    Route::get('catalog', [CustomerServiceController::class, 'catalog']);
    Route::resource('subscriptions', CustomerSubscriptionController::class);
    Route::resource('trials', CustomerTrialController::class);
});
```

### 파트너 라우트 (`/partner/*`)
```php
// jiny/auth JWT + 추가 파트너 검증
Route::middleware(['jwt.auth', 'partner.verify'])->prefix('partner')->group(function () {
    Route::get('dashboard', [PartnerController::class, 'dashboard']);
    Route::resource('subscriptions', PartnerSubscriptionController::class);
    Route::resource('commissions', PartnerCommissionController::class);
});
```

## 설치 및 설정

### 1. Composer 의존성
```bash
composer require jiny/admin:^1.0
composer require jiny/auth:^0.5
composer require jiny/service:^1.0
```

### 2. 서비스 프로바이더 등록
```php
// config/app.php
'providers' => [
    // ...
    Jiny\Admin\JinyAdminServiceProvider::class,
    Jiny\Auth\JinyAuthServiceProvider::class,
    Jiny\Service\JinyServiceServiceProvider::class,
],
```

### 3. 미들웨어 등록
```php
// app/Http/Kernel.php
protected $routeMiddleware = [
    // 기존 jiny 미들웨어
    'admin' => \Jiny\Admin\Http\Middleware\AdminMiddleware::class,
    'jwt.auth' => \Jiny\Auth\Http\Middleware\JWTAuthMiddleware::class,

    // 새로 추가되는 미들웨어
    'partner.verify' => \Jiny\Service\Http\Middleware\PartnerVerificationMiddleware::class,
];
```

### 4. 마이그레이션 실행
```bash
# 기존 패키지 마이그레이션 (이미 실행된 경우 스킵)
php artisan migrate --path=vendor/jiny/admin/database/migrations
php artisan migrate --path=vendor/jiny/auth/database/migrations

# 새로운 서비스 마이그레이션
php artisan migrate --path=vendor/jiny/service/database/migrations
```

## 개발 시 주의사항

### 1. 기존 시스템과의 호환성
- 기존 jiny/admin, jiny/auth 기능을 손상시키지 않도록 주의
- 새로운 기능은 별도 네임스페이스에서 개발
- 기존 테이블 구조 변경 금지

### 2. 데이터 무결성
- 샤딩된 users_0xx 테이블 참조 시 샤드 번호 함께 저장
- 외래 키 제약조건 설정 시 샤딩 구조 고려
- 트랜잭션 처리 시 여러 샤드에 걸친 작업 주의

### 3. 성능 고려사항
- JWT 토큰 검증 시 캐싱 활용
- 샤딩 테이블 조회 시 적절한 인덱스 사용
- 대용량 데이터 처리 시 배치 작업 활용

### 4. 보안 강화
- 기존 jiny 패키지의 보안 정책 준수
- 추가적인 파트너 검증 로직 구현
- 민감한 데이터 암호화 저장

## 버전 호환성

| jiny/service | jiny/admin | jiny/auth | Laravel |
|-------------|------------|-----------|---------|
| 1.0.x       | ^1.0       | ^0.5      | ^10.0   |
| 1.1.x       | ^1.0       | ^0.5      | ^10.0   |

## 지원 및 문의

- **문서**: `/vendor/jiny/service/docs/`
- **예제**: `/vendor/jiny/service/docs/sample01.md`, `sample02.md`
- **태스크**: `/vendor/jiny/service/task/001_project_overview.md` ~ `016_implementation_checklist.md`
- **GitHub**: https://github.com/jinyphp/service
- **Issues**: 기존 Jiny 패키지와의 통합 문제는 각 패키지 저장소에 문의