<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_users', function (Blueprint $table) {
            $table->id();

            // 사용자 정보 (샤딩 고려)
            $table->string('user_uuid')->index(); // 사용자 UUID
            $table->string('user_shard')->index(); // 샤딩 테이블명 (예: user_001)
            $table->unsignedBigInteger('user_id')->index(); // 샤딩 테이블 내 user ID
            $table->string('user_email')->nullable(); // 캐시된 이메일
            $table->string('user_name')->nullable(); // 캐시된 사용자명

            // 서비스 정보
            $table->unsignedBigInteger('service_id')->index();
            $table->string('service_title')->nullable(); // 캐시된 서비스명

            // 구독 상태
            $table->enum('status', ['active', 'suspended', 'cancelled', 'expired', 'pending'])->default('pending');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly', 'lifetime'])->default('monthly');

            // 구독 기간
            $table->datetime('started_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->datetime('next_billing_at')->nullable();

            // 구독 플랜 정보
            $table->string('plan_name')->nullable();
            $table->decimal('plan_price', 10, 2)->default(0);
            $table->json('plan_features')->nullable(); // 플랜 기능 정보

            // 결제 정보
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');

            // 자동 갱신
            $table->boolean('auto_renewal')->default(true);
            $table->boolean('auto_upgrade')->default(false);

            // 취소/환불 정보
            $table->datetime('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->datetime('refunded_at')->nullable();

            // 관리자 메모
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // 인덱스
            $table->index(['user_uuid', 'service_id']);
            $table->index(['status', 'expires_at']);
            $table->index(['billing_cycle', 'next_billing_at']);
            $table->index(['payment_status', 'next_billing_at']);

            // 외래키
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_users');
    }
};