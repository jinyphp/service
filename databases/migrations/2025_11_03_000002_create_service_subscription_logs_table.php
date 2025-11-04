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
        Schema::create('service_subscription_logs', function (Blueprint $table) {
            $table->id();

            // 관련 구독 정보
            $table->unsignedBigInteger('service_user_id')->index();
            $table->string('user_uuid')->index();
            $table->unsignedBigInteger('service_id')->index();

            // 액션 정보
            $table->enum('action', [
                'subscribe',       // 구독 신청
                'activate',        // 구독 활성화
                'suspend',         // 구독 일시정지
                'resume',          // 구독 재개
                'cancel',          // 구독 취소
                'expire',          // 구독 만료
                'renew',           // 구독 갱신
                'upgrade',         // 등급 업그레이드
                'downgrade',       // 등급 다운그레이드
                'extend',          // 구독 연장
                'refund',          // 환불
                'payment_success', // 결제 성공
                'payment_failed',  // 결제 실패
                'admin_action'     // 관리자 직접 조치
            ])->index();

            // 액션 상세 정보
            $table->string('action_title')->nullable();
            $table->text('action_description')->nullable();
            $table->json('action_data')->nullable(); // 추가 액션 데이터

            // 변경 전후 상태
            $table->string('status_before')->nullable();
            $table->string('status_after')->nullable();

            // 금액 정보
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('KRW');

            // 플랜 변경 정보
            $table->string('plan_before')->nullable();
            $table->string('plan_after')->nullable();

            // 기간 변경 정보
            $table->datetime('expires_before')->nullable();
            $table->datetime('expires_after')->nullable();

            // 처리자 정보
            $table->enum('processed_by', ['system', 'admin', 'user'])->default('system');
            $table->string('processor_id')->nullable(); // 처리자 ID
            $table->string('processor_name')->nullable(); // 처리자 이름

            // 결과
            $table->enum('result', ['success', 'failed', 'pending'])->default('success');
            $table->text('error_message')->nullable();

            // IP 및 User-Agent
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // 인덱스
            $table->index(['action', 'created_at']);
            $table->index(['user_uuid', 'action']);
            $table->index(['service_id', 'action']);
            $table->index(['processed_by', 'created_at']);
            $table->index(['result', 'created_at']);

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
        Schema::dropIfExists('service_subscription_logs');
    }
};