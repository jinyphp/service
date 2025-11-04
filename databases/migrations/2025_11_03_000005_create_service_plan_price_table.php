<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 서비스 플랜 가격 옵션 테이블
        Schema::create('service_plan_price', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            // 기본 정보
            $table->unsignedBigInteger('service_plan_id');
            $table->foreign('service_plan_id')->references('id')->on('service_plans')->onDelete('cascade');

            $table->boolean('enable')->default(true);
            $table->integer('pos')->default(0); // 정렬 순서

            $table->string('name'); // 가격 옵션명 (예: "월간", "연간", "일회성")
            $table->string('code')->nullable(); // 가격 옵션 코드
            $table->text('description')->nullable(); // 가격 옵션 설명

            // 가격 정보
            $table->decimal('price', 10, 2); // 기본 가격
            $table->decimal('sale_price', 10, 2)->nullable(); // 할인가
            $table->string('currency', 3)->default('KRW'); // 통화
            $table->enum('billing_period', ['monthly', 'quarterly', 'yearly', 'once'])->default('monthly'); // 결제 주기
            $table->integer('billing_cycle_count')->default(1); // 결제 주기 횟수 (예: 3개월 = quarterly + 1)

            // 할인 및 프로모션
            $table->decimal('discount_percentage', 5, 2)->default(0); // 할인율
            $table->decimal('setup_fee', 10, 2)->default(0); // 초기 설정비
            $table->integer('trial_days')->default(0); // 무료 체험 일수

            // 가격 옵션 상세 정보
            $table->json('additional_features')->nullable(); // 추가 기능들
            $table->json('pricing_rules')->nullable(); // 가격 규칙 (할인 조건 등)
            $table->boolean('auto_renewal')->default(true); // 자동 갱신 여부
            $table->boolean('is_popular')->default(false); // 인기 옵션 여부
            $table->boolean('is_recommended')->default(false); // 추천 옵션 여부

            // 제한 사항
            $table->integer('min_quantity')->default(1); // 최소 수량
            $table->integer('max_quantity')->nullable(); // 최대 수량
            $table->date('valid_from')->nullable(); // 유효 시작일
            $table->date('valid_until')->nullable(); // 유효 종료일

            $table->index(['service_plan_id', 'enable']);
            $table->index(['billing_period', 'enable']);
            $table->index(['is_popular', 'pos']);
        });

        // Insert default service plan price options
        DB::table('service_plan_price')->insert([
            // 웹사이트 기본 플랜 가격 옵션들
            [
                'service_plan_id' => 1, // 기본 웹사이트 플랜
                'name' => '월간 구독',
                'code' => 'web-basic-monthly',
                'description' => '매월 결제하는 기본 웹사이트 플랜',
                'price' => 50000.00,
                'sale_price' => null,
                'currency' => 'KRW',
                'billing_period' => 'monthly',
                'billing_cycle_count' => 1,
                'discount_percentage' => 0,
                'trial_days' => 14,
                'auto_renewal' => true,
                'is_popular' => false,
                'is_recommended' => false,
                'pos' => 1,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 1, // 기본 웹사이트 플랜
                'name' => '연간 구독',
                'code' => 'web-basic-yearly',
                'description' => '연간 결제로 20% 할인 혜택',
                'price' => 600000.00,
                'sale_price' => 480000.00,
                'currency' => 'KRW',
                'billing_period' => 'yearly',
                'billing_cycle_count' => 1,
                'discount_percentage' => 20,
                'trial_days' => 30,
                'auto_renewal' => true,
                'is_popular' => true,
                'is_recommended' => true,
                'pos' => 2,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // 프로 웹사이트 플랜 가격 옵션들
            [
                'service_plan_id' => 2, // 프로 웹사이트 플랜
                'name' => '월간 구독',
                'code' => 'web-pro-monthly',
                'description' => '매월 결제하는 프로 웹사이트 플랜',
                'price' => 120000.00,
                'sale_price' => null,
                'currency' => 'KRW',
                'billing_period' => 'monthly',
                'billing_cycle_count' => 1,
                'discount_percentage' => 0,
                'trial_days' => 30,
                'auto_renewal' => true,
                'is_popular' => true,
                'is_recommended' => false,
                'pos' => 1,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 2, // 프로 웹사이트 플랜
                'name' => '연간 구독',
                'code' => 'web-pro-yearly',
                'description' => '연간 결제로 17% 할인 혜택',
                'price' => 1440000.00,
                'sale_price' => 1200000.00,
                'currency' => 'KRW',
                'billing_period' => 'yearly',
                'billing_cycle_count' => 1,
                'discount_percentage' => 17,
                'trial_days' => 30,
                'auto_renewal' => true,
                'is_popular' => false,
                'is_recommended' => true,
                'pos' => 2,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // 모바일 앱 플랜 가격 옵션들
            [
                'service_plan_id' => 3, // 기본 모바일 앱 플랜
                'name' => '월간 구독',
                'code' => 'mobile-basic-monthly',
                'description' => '매월 결제하는 기본 모바일 앱 플랜',
                'price' => 200000.00,
                'sale_price' => null,
                'currency' => 'KRW',
                'billing_period' => 'monthly',
                'billing_cycle_count' => 1,
                'discount_percentage' => 0,
                'trial_days' => 7,
                'auto_renewal' => true,
                'is_popular' => false,
                'is_recommended' => false,
                'pos' => 1,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 4, // 기본 디자인 패키지
                'name' => '일회성 결제',
                'code' => 'design-basic-once',
                'description' => '한 번만 결제하는 기본 디자인 패키지',
                'price' => 800000.00,
                'sale_price' => null,
                'currency' => 'KRW',
                'billing_period' => 'once',
                'billing_cycle_count' => 1,
                'discount_percentage' => 0,
                'trial_days' => 14,
                'auto_renewal' => false,
                'is_popular' => true,
                'is_recommended' => false,
                'pos' => 1,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_plan_price');
    }
};