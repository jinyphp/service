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
        Schema::create('service_payments', function (Blueprint $table) {
            $table->id();

            // 연관 정보
            $table->unsignedBigInteger('service_user_id')->index();
            $table->string('user_uuid')->index();
            $table->unsignedBigInteger('service_id')->index();

            // 결제 식별 정보
            $table->string('payment_uuid')->unique();
            $table->string('transaction_id')->nullable()->index(); // 외부 결제 시스템 ID
            $table->string('order_id')->nullable()->index(); // 주문 ID

            // 결제 정보
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 12, 2); // 실제 결제 금액
            $table->string('currency', 3)->default('KRW');

            // 결제 방법
            $table->enum('payment_method', [
                'credit_card', 'debit_card', 'bank_transfer',
                'virtual_account', 'mobile_payment', 'crypto',
                'paypal', 'stripe', 'other'
            ]);
            $table->string('payment_provider')->nullable(); // 결제 제공자 (PG사)
            $table->json('payment_details')->nullable(); // 결제 상세 정보

            // 결제 상태
            $table->enum('status', [
                'pending',     // 결제 대기
                'processing',  // 결제 진행 중
                'completed',   // 결제 완료
                'failed',      // 결제 실패
                'cancelled',   // 결제 취소
                'refunded',    // 환불 완료
                'partially_refunded' // 부분 환불
            ])->default('pending');

            // 결제 유형
            $table->enum('payment_type', [
                'subscription', // 정기 구독
                'one_time',     // 일회성 결제
                'upgrade',      // 업그레이드
                'extension',    // 연장
                'late_fee',     // 연체료
                'setup_fee'     // 설치비
            ])->default('subscription');

            // 결제 주기 정보 (정기 결제인 경우)
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly', 'lifetime'])->nullable();
            $table->datetime('billing_period_start')->nullable();
            $table->datetime('billing_period_end')->nullable();

            // 환불 정보
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->datetime('refunded_at')->nullable();
            $table->string('refund_reason')->nullable();
            $table->string('refund_transaction_id')->nullable();

            // 실패 정보
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->integer('retry_count')->default(0);

            // 결제 완료 정보
            $table->datetime('paid_at')->nullable();
            $table->datetime('due_date')->nullable();

            // 메타데이터
            $table->json('metadata')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // 인덱스
            $table->index(['user_uuid', 'status']);
            $table->index(['service_id', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index(['payment_type', 'billing_cycle']);
            $table->index(['status', 'due_date']);
            $table->index(['created_at', 'status']);

            // 외래키
            $table->foreign('service_user_id')->references('id')->on('service_users')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_payments');
    }
};