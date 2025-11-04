<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_plans', function (Blueprint $table) {
            $table->id();

            // 기본 정보
            $table->unsignedBigInteger('service_id')->index();
            $table->string('plan_name');
            $table->string('plan_code')->unique(); // 플랜 고유 코드
            $table->text('description')->nullable();

            // 가격 정보
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('quarterly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->decimal('lifetime_price', 10, 2)->default(0);
            $table->decimal('setup_fee', 10, 2)->default(0);

            // 할인 정보
            $table->json('discount_rules')->nullable(); // 할인 규칙
            $table->decimal('trial_period_days', 5, 0)->default(0); // 무료 체험 기간

            // 플랜 제한사항
            $table->json('features')->nullable(); // 기능 목록
            $table->json('limitations')->nullable(); // 제한사항 (API 호출 수 등)
            $table->json('quotas')->nullable(); // 할당량 정보

            // 플랜 분류
            $table->enum('plan_type', [
                'basic', 'standard', 'premium', 'enterprise', 'custom'
            ])->default('basic');
            $table->enum('billing_type', [
                'subscription', 'one_time', 'usage_based', 'hybrid'
            ])->default('subscription');

            // 이용 가능한 결제 주기
            $table->boolean('monthly_available')->default(true);
            $table->boolean('quarterly_available')->default(true);
            $table->boolean('yearly_available')->default(true);
            $table->boolean('lifetime_available')->default(false);

            // 플랜 상태
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('allow_trial')->default(true);
            $table->boolean('auto_renewal')->default(true);

            // 표시 정보
            $table->integer('sort_order')->default(0);
            $table->string('color_code')->nullable(); // UI 표시용 색상
            $table->string('icon')->nullable(); // 아이콘

            // 업그레이드/다운그레이드 규칙
            $table->json('upgrade_paths')->nullable(); // 업그레이드 가능한 플랜들
            $table->json('downgrade_paths')->nullable(); // 다운그레이드 가능한 플랜들
            $table->boolean('immediate_upgrade')->default(true);
            $table->boolean('immediate_downgrade')->default(false);

            // 제한 및 정책
            $table->integer('max_users')->nullable(); // 최대 사용자 수
            $table->integer('max_projects')->nullable(); // 최대 프로젝트 수
            $table->decimal('storage_limit_gb', 8, 2)->nullable(); // 저장공간 제한
            $table->integer('api_calls_per_month')->nullable(); // 월간 API 호출 제한

            // 지역 제한
            $table->json('available_regions')->nullable(); // 이용 가능 지역
            $table->json('restricted_regions')->nullable(); // 제한 지역

            $table->timestamps();
            $table->softDeletes();

            // 인덱스
            $table->index(['service_id', 'is_active']);
            $table->index(['plan_type', 'is_active']);
            $table->index(['is_featured', 'sort_order']);
            $table->index(['monthly_price', 'is_active']);

            // 외래키
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        // Insert default service plans
        DB::table('service_plans')->insert([
            // 웹사이트 개발 플랜들
            [
                'service_id' => 1,
                'plan_name' => '기본 웹사이트',
                'plan_code' => 'web-basic',
                'description' => '개인 또는 소규모 비즈니스를 위한 기본 웹사이트',
                'monthly_price' => 50000.00,
                'quarterly_price' => 130000.00,
                'yearly_price' => 480000.00,
                'plan_type' => 'basic',
                'billing_type' => 'subscription',
                'monthly_available' => true,
                'quarterly_available' => true,
                'yearly_available' => true,
                'lifetime_available' => false,
                'is_active' => true,
                'is_featured' => false,
                'is_popular' => false,
                'allow_trial' => true,
                'trial_period_days' => 14,
                'auto_renewal' => true,
                'sort_order' => 1,
                'color_code' => '#6B7280',
                'icon' => 'fas fa-laptop',
                'max_users' => 1,
                'max_projects' => 1,
                'storage_limit_gb' => 5.0,
                'api_calls_per_month' => 1000,
                'features' => json_encode([
                    'basic_design',
                    'mobile_responsive',
                    'ssl_certificate',
                    'basic_seo'
                ]),
                'limitations' => json_encode([
                    'no_ecommerce',
                    'basic_support_only',
                    'limited_customization'
                ]),
                'quotas' => json_encode([
                    'pages' => 5,
                    'forms' => 1,
                    'galleries' => 1
                ]),
                'upgrade_paths' => json_encode(['web-pro']),
                'downgrade_paths' => null,
                'immediate_upgrade' => true,
                'immediate_downgrade' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 1,
                'plan_name' => '프로 웹사이트',
                'plan_code' => 'web-pro',
                'description' => '중소기업을 위한 전문 웹사이트 솔루션',
                'monthly_price' => 120000.00,
                'quarterly_price' => 320000.00,
                'yearly_price' => 1200000.00,
                'plan_type' => 'premium',
                'billing_type' => 'subscription',
                'monthly_available' => true,
                'quarterly_available' => true,
                'yearly_available' => true,
                'lifetime_available' => false,
                'is_active' => true,
                'is_featured' => true,
                'is_popular' => true,
                'allow_trial' => true,
                'trial_period_days' => 30,
                'auto_renewal' => true,
                'sort_order' => 2,
                'color_code' => '#3B82F6',
                'icon' => 'fas fa-rocket',
                'max_users' => 5,
                'max_projects' => 3,
                'storage_limit_gb' => 20.0,
                'api_calls_per_month' => 10000,
                'features' => json_encode([
                    'custom_design',
                    'mobile_responsive',
                    'ssl_certificate',
                    'advanced_seo',
                    'ecommerce_ready',
                    'analytics_integration',
                    'priority_support'
                ]),
                'limitations' => json_encode([
                    'advanced_features_limited'
                ]),
                'quotas' => json_encode([
                    'pages' => 20,
                    'forms' => 5,
                    'galleries' => 10,
                    'products' => 100
                ]),
                'upgrade_paths' => json_encode(['web-enterprise']),
                'downgrade_paths' => json_encode(['web-basic']),
                'immediate_upgrade' => true,
                'immediate_downgrade' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // 모바일 앱 개발 플랜들
            [
                'service_id' => 2,
                'plan_name' => '기본 모바일 앱',
                'plan_code' => 'mobile-basic',
                'description' => '간단한 모바일 앱 개발',
                'monthly_price' => 200000.00,
                'quarterly_price' => 550000.00,
                'yearly_price' => 2000000.00,
                'plan_type' => 'basic',
                'billing_type' => 'subscription',
                'monthly_available' => true,
                'quarterly_available' => true,
                'yearly_available' => true,
                'lifetime_available' => false,
                'is_active' => true,
                'is_featured' => false,
                'is_popular' => false,
                'allow_trial' => true,
                'trial_period_days' => 7,
                'auto_renewal' => true,
                'sort_order' => 1,
                'color_code' => '#10B981',
                'icon' => 'fas fa-mobile-alt',
                'max_users' => 2,
                'max_projects' => 1,
                'storage_limit_gb' => 10.0,
                'api_calls_per_month' => 5000,
                'features' => json_encode([
                    'cross_platform',
                    'basic_ui',
                    'app_store_submission',
                    'basic_analytics'
                ]),
                'limitations' => json_encode([
                    'no_advanced_features',
                    'limited_integrations'
                ]),
                'quotas' => json_encode([
                    'screens' => 10,
                    'push_notifications' => 1000,
                    'api_endpoints' => 5
                ]),
                'upgrade_paths' => json_encode(['mobile-pro']),
                'downgrade_paths' => null,
                'immediate_upgrade' => true,
                'immediate_downgrade' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // UI/UX 디자인 플랜들
            [
                'service_id' => 3,
                'plan_name' => '기본 디자인 패키지',
                'plan_code' => 'design-basic',
                'description' => '기본 UI/UX 디자인 서비스',
                'monthly_price' => 80000.00,
                'quarterly_price' => 210000.00,
                'yearly_price' => 800000.00,
                'plan_type' => 'basic',
                'billing_type' => 'subscription',
                'monthly_available' => true,
                'quarterly_available' => true,
                'yearly_available' => true,
                'lifetime_available' => false,
                'is_active' => true,
                'is_featured' => false,
                'is_popular' => true,
                'allow_trial' => true,
                'trial_period_days' => 14,
                'auto_renewal' => true,
                'sort_order' => 1,
                'color_code' => '#F59E0B',
                'icon' => 'fas fa-palette',
                'max_users' => 2,
                'max_projects' => 2,
                'storage_limit_gb' => 15.0,
                'api_calls_per_month' => 2000,
                'features' => json_encode([
                    'wireframes',
                    'mockups',
                    'responsive_design',
                    'style_guide',
                    'basic_prototype'
                ]),
                'limitations' => json_encode([
                    'no_user_testing',
                    'limited_revisions'
                ]),
                'quotas' => json_encode([
                    'designs' => 10,
                    'revisions' => 3,
                    'prototypes' => 2
                ]),
                'upgrade_paths' => json_encode(['design-pro']),
                'downgrade_paths' => null,
                'immediate_upgrade' => true,
                'immediate_downgrade' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plans');
    }
};