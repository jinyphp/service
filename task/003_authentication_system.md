# 003. Authentication System - TDD Implementation

## 개요
Jiny 생태계 패키지 기반 3-tier 사용자 인증 시스템 구현

### 패키지 의존성
- **jiny/admin**: 관리자 세션 인증 (`users` 테이블)
- **jiny/auth**: 고객/파트너 JWT 인증 (`users_0xx` 샤딩)

### 인증 시스템 구조
1. **Admin (jiny/admin)**: 세션 + `admin` 미들웨어
2. **Customer (jiny/auth)**: JWT + `users_0xx` 샤딩
3. **Partner (jiny/auth)**: JWT + `partner.verify` 추가 검증

## 의존관계
- **선행 태스크**: [002. 데이터베이스 스키마](002_database_schema.md)
- **후속 태스크**: [004. 서비스 카탈로그](004_service_catalog.md)

## TDD 테스트 시나리오 (모두 HTTP 200 반환)

### Admin 인증 테스트
**테스트**: `AdminAuthenticationTest`

```php
public function test_admin_login_returns_200()
{
    // Given: 관리자 사용자
    $admin = User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password')
    ]);

    // When: 로그인 시도
    $response = $this->post('/admin/login', [
        'email' => 'admin@test.com',
        'password' => 'password'
    ]);

    // Then: HTTP 200과 세션 생성
    $response->assertStatus(200);
    $this->assertAuthenticatedAs($admin);
}

public function test_admin_middleware_blocks_non_admin()
{
    // Given: 일반 사용자
    $user = User::factory()->create(['isAdmin' => false]);

    // When: 관리자 페이지 접근
    $response = $this->actingAs($user)->get('/admin/service/catalog');

    // Then: 403 Forbidden
    $response->assertStatus(403);
}
```

### Customer JWT 인증 테스트
**테스트**: `CustomerJWTAuthenticationTest`

```php
public function test_customer_jwt_login_returns_200()
{
    // Given: 고객 사용자 (users_001 테이블)
    $customer = $this->createCustomerInShard('001', [
        'email' => 'customer@test.com',
        'password' => bcrypt('password')
    ]);

    // When: JWT 로그인
    $response = $this->post('/api/auth/login', [
        'email' => 'customer@test.com',
        'password' => 'password'
    ]);

    // Then: HTTP 200과 JWT 토큰
    $response->assertStatus(200);
    $response->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
}

public function test_jwt_protected_routes_return_200_with_valid_token()
{
    // Given: JWT 토큰
    $token = $this->generateCustomerJWT();

    // When: 보호된 엔드포인트 접근
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token
    ])->get('/home/service/subscriptions');

    // Then: HTTP 200
    $response->assertStatus(200);
}
```

### Partner 인증 테스트
**테스트**: `PartnerAuthenticationTest`

```php
public function test_partner_access_returns_200_with_valid_partner()
{
    // Given: 파트너 사용자
    [$customer, $partner] = $this->createPartnerWithCustomer();
    $token = $this->generateJWTForCustomer($customer);

    // When: 파트너 엔드포인트 접근
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token
    ])->get('/partner/dashboard');

    // Then: HTTP 200
    $response->assertStatus(200);
}

public function test_partner_middleware_blocks_non_partner()
{
    // Given: 파트너가 아닌 고객
    $customer = $this->createCustomerInShard('001');
    $token = $this->generateJWTForCustomer($customer);

    // When: 파트너 엔드포인트 접근
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token
    ])->get('/partner/dashboard');

    // Then: 403 Forbidden
    $response->assertStatus(403);
}
```

## 인증 시스템 구현

### 1. Admin 인증 (Session-based)

#### AdminMiddleware (jiny/admin 패키지 활용)
```php
<?php

namespace Jiny\Service\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * jiny/admin 패키지의 AdminMiddleware를 확장하여 사용
 * 기존 Jiny Admin 인증 시스템과 완전 호환
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 세션 인증 확인
        if (!Auth::check()) {
            return redirect('/admin/login')->with('error', 'Please login to continue');
        }

        $user = Auth::user();

        // 관리자 권한 확인
        if (!$user->isAdmin) {
            return response()->json(['error' => 'Admin access required'], 403);
        }

        // 사용자 타입 확인
        if (!$user->utype || !$this->validateUserType($user->utype)) {
            return response()->json(['error' => 'Invalid user type'], 403);
        }

        // 계정 차단 확인
        if ($user->is_blocked) {
            Auth::logout();
            return response()->json(['error' => 'Account is blocked'], 403);
        }

        // 활동 기록 업데이트 (1분마다)
        $this->updateLastActivity($user);

        // 관리자 접근 로그
        $this->logAdminAccess($user, $request);

        return $next($request);
    }

    private function validateUserType($utype): bool
    {
        return \DB::table('admin_user_types')
            ->where('type', $utype)
            ->where('enable', true)
            ->exists();
    }

    private function updateLastActivity($user): void
    {
        $now = now();
        $lastUpdate = $user->last_activity_at;

        // 1분 이상 차이날 때만 업데이트
        if (!$lastUpdate || $now->diffInMinutes($lastUpdate) >= 1) {
            $user->update(['last_activity_at' => $now]);
        }
    }

    private function logAdminAccess($user, Request $request): void
    {
        \DB::table('admin_user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'page_access',
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);
    }
}
```

#### Admin Login Controller
```php
<?php

namespace Jiny\Service\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('jiny-service::admin.auth.login');
    }

    public function login(Request $request)
    {
        // Rate limiting
        $key = 'admin-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'error' => 'Too many login attempts. Please try again later.'
            ], 429);
        }

        // 입력 검증
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        // 로그인 시도
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // 관리자 권한 확인
            if (!$user->isAdmin) {
                Auth::logout();
                RateLimiter::hit($key);
                return response()->json(['error' => 'Admin access required'], 403);
            }

            // 계정 상태 확인
            if ($user->is_blocked) {
                Auth::logout();
                RateLimiter::hit($key);
                return response()->json(['error' => 'Account is blocked'], 403);
            }

            // 성공 시 rate limit 초기화
            RateLimiter::clear($key);

            // 세션 재생성 (보안)
            $request->session()->regenerate();

            // 로그인 성공 로그
            $this->logSuccessfulLogin($user, $request);

            return response()->json([
                'message' => 'Login successful',
                'redirect' => '/admin/dashboard'
            ], 200);
        }

        // 실패 시 rate limit 증가
        RateLimiter::hit($key);

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // 로그아웃 로그
        if ($user) {
            $this->logLogout($user, $request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    private function logSuccessfulLogin($user, Request $request): void
    {
        \DB::table('admin_user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);
    }

    private function logLogout($user, Request $request): void
    {
        \DB::table('admin_user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);
    }
}
```

### 2. Customer JWT 인증 (jiny/auth 패키지 확장)

#### JWT Service (jiny/auth 패키지 기반)
```php
<?php

namespace Jiny\Service\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use Jiny\Auth\Services\JWTAuthService; // jiny/auth 패키지 활용

/**
 * jiny/auth 패키지의 JWT 서비스를 확장하여 서비스 특화 기능 추가
 * 기존 JWT 인증 시스템과 완전 호환
 */
class JWTService extends JWTAuthService
{
    private string $secret;
    private string $algorithm = 'HS256';
    private int $ttl = 3600; // 1시간

    public function __construct()
    {
        $this->secret = config('app.jwt_secret', config('app.key'));
    }

    public function generateToken(array $payload): string
    {
        $now = Carbon::now();

        $payload = array_merge($payload, [
            'iat' => $now->timestamp,
            'exp' => $now->addSeconds($this->ttl)->timestamp,
            'iss' => config('app.url'),
            'jti' => uniqid(), // JWT ID for tracking
        ]);

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function refreshToken(string $token): ?string
    {
        $payload = $this->validateToken($token);

        if (!$payload) {
            return null;
        }

        // 토큰이 만료 30분 전이면 갱신
        $exp = $payload['exp'];
        if (Carbon::createFromTimestamp($exp)->diffInMinutes() <= 30) {
            unset($payload['iat'], $payload['exp'], $payload['jti']);
            return $this->generateToken($payload);
        }

        return null;
    }

    public function getCustomerFromToken(string $token): ?object
    {
        $payload = $this->validateToken($token);

        if (!$payload || !isset($payload['customer_id'], $payload['shard'])) {
            return null;
        }

        return $this->getCustomerFromShard($payload['customer_id'], $payload['shard']);
    }

    private function getCustomerFromShard(int $customerId, string $shard): ?object
    {
        $table = "users_{$shard}";

        return \DB::table($table)
            ->where('id', $customerId)
            ->where('is_active', true)
            ->first();
    }
}
```

#### JWT Middleware
```php
<?php

namespace Jiny\Service\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jiny\Service\Services\JWTService;

class JWTAuthMiddleware
{
    private JWTService $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        $customer = $this->jwtService->getCustomerFromToken($token);

        if (!$customer) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        // 계정 상태 확인
        if ($customer->is_blocked ?? false) {
            return response()->json(['error' => 'Account is blocked'], 403);
        }

        // 고객 정보를 요청에 추가
        $request->merge(['authenticated_customer' => $customer]);

        // 토큰 갱신 확인
        $refreshedToken = $this->jwtService->refreshToken($token);
        if ($refreshedToken) {
            // 새 토큰을 응답 헤더에 추가
            $response = $next($request);
            return $response->header('X-New-Token', $refreshedToken);
        }

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return null;
        }

        return substr($header, 7);
    }
}
```

#### Customer Auth Controller
```php
<?php

namespace Jiny\Service\Http\Controllers\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Jiny\Service\Services\JWTService;
use Jiny\Service\Services\CustomerShardService;

class CustomerAuthController extends Controller
{
    private JWTService $jwtService;
    private CustomerShardService $shardService;

    public function __construct(JWTService $jwtService, CustomerShardService $shardService)
    {
        $this->jwtService = $jwtService;
        $this->shardService = $shardService;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // 샤드에서 고객 찾기
        $customer = $this->shardService->findCustomerByEmail($credentials['email']);

        if (!$customer || !Hash::check($credentials['password'], $customer->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // 계정 상태 확인
        if ($customer->is_blocked ?? false) {
            return response()->json(['error' => 'Account is blocked'], 403);
        }

        // JWT 토큰 생성
        $token = $this->jwtService->generateToken([
            'customer_id' => $customer->id,
            'shard' => $customer->shard,
            'email' => $customer->email
        ]);

        // 로그인 로그
        $this->logCustomerLogin($customer, $request);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email
            ]
        ], 200);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users_001,email', // 샤드별 유니크 체크 필요
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20'
        ]);

        // 샤드 선택
        $shard = $this->shardService->selectShardForNewCustomer();

        // 고객 생성
        $customer = $this->shardService->createCustomer($shard, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'email_verified_at' => null,
            'is_active' => true,
            'created_at' => now()
        ]);

        // 환영 이메일 발송 (큐에 추가)
        // dispatch(new SendWelcomeEmail($customer));

        return response()->json([
            'message' => 'Registration successful',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email
            ]
        ], 201);
    }

    public function refresh(Request $request)
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        $newToken = $this->jwtService->refreshToken($token);

        if (!$newToken) {
            return response()->json(['error' => 'Cannot refresh token'], 401);
        }

        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ], 200);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        return $header && str_starts_with($header, 'Bearer ') ? substr($header, 7) : null;
    }

    private function logCustomerLogin($customer, Request $request): void
    {
        \DB::table('customer_login_logs')->insert([
            'customer_id' => $customer->id,
            'shard' => $customer->shard,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => now()
        ]);
    }
}
```

### 3. Partner 검증 시스템

#### Partner Verification Middleware
```php
<?php

namespace Jiny\Service\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PartnerVerificationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $customer = $request->input('authenticated_customer');

        if (!$customer) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        // 파트너 정보 조회
        $partner = \DB::table('partners')
            ->where('user_id', $customer->id)
            ->where('status', 'active')
            ->first();

        if (!$partner) {
            return response()->json(['error' => 'Partner access required'], 403);
        }

        // 계약 기간 확인
        if ($partner->contract_end_date && Carbon::parse($partner->contract_end_date)->isPast()) {
            return response()->json(['error' => 'Partner contract expired'], 403);
        }

        // 파트너 정보를 요청에 추가
        $request->merge([
            'authenticated_partner' => $partner,
            'partner_territory' => json_decode($partner->territory, true),
            'partner_level' => $partner->level
        ]);

        // 파트너 활동 로그
        $this->logPartnerActivity($partner, $request);

        return $next($request);
    }

    private function logPartnerActivity($partner, Request $request): void
    {
        \DB::table('partner_activity_logs')->insert([
            'partner_id' => $partner->id,
            'action' => 'page_access',
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'created_at' => now()
        ]);
    }
}
```

## Customer Shard Service (jiny/auth 패키지 확장)

```php
<?php

namespace Jiny\Service\Services;

use Jiny\Auth\Services\UserShardService; // jiny/auth 패키지 활용

/**
 * jiny/auth 패키지의 UserShardService를 확장하여 서비스 특화 기능 추가
 */
class CustomerShardService extends UserShardService
{
    // jiny/auth 패키지 설정에서 샤드 설정을 읽어옴
    private int $shardCount;
    private int $maxUsersPerShard;
    private string $shardTablePrefix;

    public function __construct()
    {
        parent::__construct();

        // jiny/auth 패키지 설정 읽기
        $this->shardCount = config('jiny-auth.sharding.shard_count', 100);
        $this->maxUsersPerShard = config('jiny-auth.sharding.max_users_per_shard', 1000000);
        $this->shardTablePrefix = config('jiny-auth.sharding.table_prefix', 'users_');
    }

    public function findCustomerByEmail(string $email): ?object
    {
        // jiny/auth 설정에서 읽은 샤드 수만큼 검색
        for ($i = 0; $i < $this->shardCount; $i++) {
            $shard = str_pad($i, 3, '0', STR_PAD_LEFT);
            $table = "users_{$shard}";

            $customer = \DB::table($table)
                ->where('email', $email)
                ->first();

            if ($customer) {
                $customer->shard = $shard;
                return $customer;
            }
        }

        return null;
    }

    public function selectShardForNewCustomer(): string
    {
        // 가장 적은 사용자를 가진 샤드 선택
        $shardCounts = [];

        for ($i = 0; $i < $this->shardCount; $i++) {
            $shard = str_pad($i, 3, '0', STR_PAD_LEFT);
            $table = $this->shardTablePrefix . $shard;

            $count = \DB::table($table)->count();

            if ($count < $this->maxUsersPerShard) {
                $shardCounts[$shard] = $count;
            }
        }

        if (empty($shardCounts)) {
            throw new \Exception('All shards are full');
        }

        // 가장 적은 사용자를 가진 샤드 반환
        return array_keys($shardCounts, min($shardCounts))[0];
    }

    public function createCustomer(string $shard, array $data): object
    {
        $table = $this->shardTablePrefix . $shard;

        $id = \DB::table($table)->insertGetId($data);

        $customer = \DB::table($table)->where('id', $id)->first();
        $customer->shard = $shard;

        return $customer;
    }

    public function getCustomer(int $customerId, string $shard): ?object
    {
        $table = $this->shardTablePrefix . $shard;

        $customer = \DB::table($table)
            ->where('id', $customerId)
            ->first();

        if ($customer) {
            $customer->shard = $shard;
        }

        return $customer;
    }

    /**
     * jiny/auth 설정에서 샤드 정보를 가져오는 헬퍼 메서드들
     */
    public function getShardCount(): int
    {
        return $this->shardCount;
    }

    public function getMaxUsersPerShard(): int
    {
        return $this->maxUsersPerShard;
    }

    public function getShardTablePrefix(): string
    {
        return $this->shardTablePrefix;
    }

    /**
     * 특정 샤드의 사용자 수 조회
     */
    public function getShardUserCount(string $shard): int
    {
        $table = $this->shardTablePrefix . $shard;
        return \DB::table($table)->count();
    }

    /**
     * 모든 샤드의 사용자 분포 조회
     */
    public function getShardDistribution(): array
    {
        $distribution = [];

        for ($i = 0; $i < $this->shardCount; $i++) {
            $shard = str_pad($i, 3, '0', STR_PAD_LEFT);
            $distribution[$shard] = $this->getShardUserCount($shard);
        }

        return $distribution;
    }
}
```

## 구현 체크리스트

### Admin 인증 시스템
- [ ] **AdminMiddleware 구현**
  - [ ] 세션 검증 로직
  - [ ] 관리자 권한 확인
  - [ ] 사용자 타입 검증
  - [ ] 계정 차단 확인
  - [ ] 활동 로그 기록
  - [ ] 테스트: HTTP 200 반환

- [ ] **Admin Login Controller**
  - [ ] 로그인 폼 표시
  - [ ] 로그인 처리 (Rate limiting)
  - [ ] 로그아웃 처리
  - [ ] 보안 로그 기록
  - [ ] 테스트: HTTP 200 반환

### Customer JWT 인증
- [ ] **JWT Service 구현**
  - [ ] 토큰 생성/검증
  - [ ] 토큰 갱신 로직
  - [ ] 고객 정보 추출
  - [ ] 샤드 연동
  - [ ] 테스트: HTTP 200 반환

- [ ] **JWT Middleware**
  - [ ] 토큰 추출/검증
  - [ ] 고객 정보 주입
  - [ ] 자동 토큰 갱신
  - [ ] 계정 상태 확인
  - [ ] 테스트: HTTP 200 반환

- [ ] **Customer Auth Controller**
  - [ ] 로그인/회원가입
  - [ ] 토큰 갱신
  - [ ] 샤드 기반 사용자 관리
  - [ ] 로그인 로그 기록
  - [ ] 테스트: HTTP 200 반환

### Partner 검증 시스템
- [ ] **Partner Verification Middleware**
  - [ ] 파트너 자격 확인
  - [ ] 계약 기간 검증
  - [ ] 지역 권한 확인
  - [ ] 활동 로그 기록
  - [ ] 테스트: HTTP 200 반환

### Customer Shard Service
- [ ] **샤드 관리 로직**
  - [ ] 이메일 기반 고객 검색
  - [ ] 신규 고객 샤드 선택
  - [ ] 고객 생성/조회
  - [ ] 샤드 밸런싱
  - [ ] 테스트: HTTP 200 반환

## 보안 강화

### 추가 보안 조치
- [ ] **Rate Limiting**
  - [ ] 로그인 시도 제한
  - [ ] API 요청 제한
  - [ ] IP 기반 제한

- [ ] **토큰 보안**
  - [ ] JWT 서명 검증
  - [ ] 토큰 블랙리스트
  - [ ] 갱신 토큰 로테이션

- [ ] **계정 보안**
  - [ ] 비밀번호 정책
  - [ ] 2FA 지원 준비
  - [ ] 계정 잠금 정책

## 완료 기준

### 기능적 검증
- [ ] 모든 인증 엔드포인트 HTTP 200 반환
- [ ] 3-tier 인증 시스템 정상 작동
- [ ] JWT 토큰 생성/검증 성공
- [ ] 파트너 권한 검증 정상
- [ ] 샤드 기반 고객 관리 작동

### 보안 검증
- [ ] 무단 접근 차단 (401, 403)
- [ ] 토큰 탈취 방지
- [ ] 세션 보안 강화
- [ ] Rate limiting 적용
- [ ] 로그 기록 완료

---

**이전 태스크**: [002. 데이터베이스 스키마](002_database_schema.md)
**다음 태스크**: [004. 서비스 카탈로그 관리](004_service_catalog.md)