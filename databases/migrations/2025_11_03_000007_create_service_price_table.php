<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_price', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            // 서비스 연결
            $table->unsignedBigInteger('service_id');

            // 가격 기본 정보
            $table->string('name'); // 가격 옵션명 (예: "기본", "스탠다드", "프리미엄")
            $table->string('code')->nullable(); // 가격 코드
            $table->text('description')->nullable(); // 설명

            // 가격 정보
            $table->decimal('price', 12, 2); // 정가
            $table->decimal('sale_price', 12, 2)->nullable(); // 할인가
            $table->string('currency', 3)->default('KRW'); // 통화

            // 할인 정보
            $table->decimal('discount_percentage', 5, 2)->nullable(); // 할인율
            $table->date('discount_start_date')->nullable(); // 할인 시작일
            $table->date('discount_end_date')->nullable(); // 할인 종료일

            // 부가 비용
            $table->decimal('setup_fee', 10, 2)->default(0); // 설치비/설정비
            $table->decimal('maintenance_fee', 10, 2)->default(0); // 유지보수비

            // 무료체험
            $table->boolean('has_trial')->default(false); // 무료체험 제공 여부
            $table->integer('trial_days')->default(0); // 무료체험 일수

            // 수량 제한
            $table->integer('min_quantity')->default(1); // 최소 주문 수량
            $table->integer('max_quantity')->nullable(); // 최대 주문 수량

            // 추가 기능 및 제한사항
            $table->json('additional_features')->nullable(); // 추가 기능 (JSON)
            $table->json('limitations')->nullable(); // 제한사항 (JSON)
            $table->json('pricing_rules')->nullable(); // 가격 규칙 (JSON)

            // 유효 기간
            $table->date('valid_from')->nullable(); // 유효 시작일
            $table->date('valid_until')->nullable(); // 유효 종료일

            // 상태 및 표시 옵션
            $table->boolean('is_popular')->default(false); // 인기 옵션
            $table->boolean('is_recommended')->default(false); // 추천 옵션
            $table->boolean('is_default')->default(false); // 기본 옵션
            $table->integer('sort_order')->default(0); // 정렬 순서
            $table->boolean('enable')->default(true); // 활성 상태

            // Foreign key constraints
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

            // Indexes
            $table->index(['service_id', 'deleted_at']);
            $table->index(['enable', 'deleted_at']);
            $table->index(['is_popular', 'deleted_at']);
            $table->index(['is_recommended', 'deleted_at']);
            $table->index(['sort_order']);
            $table->index(['valid_from', 'valid_until']);

            // Unique constraints
            $table->unique(['service_id', 'code'], 'service_price_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_price');
    }
};