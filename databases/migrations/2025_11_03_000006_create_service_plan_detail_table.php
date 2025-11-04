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
        Schema::create('service_plan_detail', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            // 기본 정보
            $table->unsignedBigInteger('service_plan_id');
            $table->foreign('service_plan_id')->references('id')->on('service_plans')->onDelete('cascade');

            $table->boolean('enable')->default(true);
            $table->integer('pos')->default(0); // 정렬 순서

            $table->string('detail_type'); // 상세 정보 타입 (feature, limitation, requirement, benefit 등)
            $table->string('title'); // 상세 항목 제목
            $table->text('description')->nullable(); // 상세 설명
            $table->string('icon')->nullable(); // 아이콘 클래스
            $table->string('color')->nullable(); // 색상 코드

            // 값 정보
            $table->text('value')->nullable(); // 값 (텍스트, JSON, 숫자 등)
            $table->string('value_type')->default('text'); // 값 타입 (text, number, boolean, json, html)
            $table->string('unit')->nullable(); // 단위 (GB, 개, 회 등)

            // 표시 옵션
            $table->boolean('is_highlighted')->default(false); // 강조 표시 여부
            $table->boolean('show_in_comparison')->default(true); // 플랜 비교표에 표시 여부
            $table->boolean('show_in_summary')->default(false); // 요약에 표시 여부

            // 제한 및 조건
            $table->json('conditions')->nullable(); // 조건 정보 (JSON)
            $table->string('tooltip')->nullable(); // 툴팁 텍스트
            $table->string('link_url')->nullable(); // 관련 링크 URL
            $table->string('link_text')->nullable(); // 링크 텍스트

            // 분류 및 그룹화
            $table->string('category')->nullable(); // 카테고리 (core, addon, support 등)
            $table->string('group_name')->nullable(); // 그룹명 (스토리지, 지원, 기능 등)
            $table->integer('group_order')->default(0); // 그룹 내 순서

            $table->index(['service_plan_id', 'enable']);
            $table->index(['detail_type', 'enable']);
            $table->index(['category', 'group_name', 'pos']);
            $table->index(['show_in_comparison', 'show_in_summary']);
        });

        // Insert default service plan details
        DB::table('service_plan_detail')->insert([
            // 웹사이트 기본 플랜 상세정보
            [
                'service_plan_id' => 1,
                'detail_type' => 'feature',
                'title' => '페이지 수',
                'description' => '생성 가능한 최대 페이지 수',
                'icon' => 'fas fa-file-alt',
                'value' => '5',
                'value_type' => 'number',
                'unit' => '개',
                'is_highlighted' => false,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'core',
                'group_name' => '기본 기능',
                'group_order' => 1,
                'pos' => 1,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 1,
                'detail_type' => 'feature',
                'title' => '저장 공간',
                'description' => '웹사이트 파일 저장 용량',
                'icon' => 'fas fa-hdd',
                'value' => '5',
                'value_type' => 'number',
                'unit' => 'GB',
                'is_highlighted' => false,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'core',
                'group_name' => '스토리지',
                'group_order' => 1,
                'pos' => 2,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 1,
                'detail_type' => 'limitation',
                'title' => '이커머스 불가',
                'description' => '온라인 쇼핑몰 기능 사용 불가',
                'icon' => 'fas fa-times-circle',
                'color' => 'text-red-500',
                'value' => 'false',
                'value_type' => 'boolean',
                'is_highlighted' => true,
                'show_in_comparison' => true,
                'show_in_summary' => false,
                'category' => 'limitation',
                'group_name' => '제한사항',
                'group_order' => 2,
                'pos' => 3,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 웹사이트 프로 플랜 상세정보
            [
                'service_plan_id' => 2,
                'detail_type' => 'feature',
                'title' => '페이지 수',
                'description' => '생성 가능한 최대 페이지 수',
                'icon' => 'fas fa-file-alt',
                'value' => '20',
                'value_type' => 'number',
                'unit' => '개',
                'is_highlighted' => false,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'core',
                'group_name' => '기본 기능',
                'group_order' => 1,
                'pos' => 1,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 2,
                'detail_type' => 'feature',
                'title' => '저장 공간',
                'description' => '웹사이트 파일 저장 용량',
                'icon' => 'fas fa-hdd',
                'value' => '20',
                'value_type' => 'number',
                'unit' => 'GB',
                'is_highlighted' => false,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'core',
                'group_name' => '스토리지',
                'group_order' => 1,
                'pos' => 2,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 2,
                'detail_type' => 'feature',
                'title' => '이커머스 지원',
                'description' => '온라인 쇼핑몰 기능 완전 지원',
                'icon' => 'fas fa-shopping-cart',
                'color' => 'text-green-500',
                'value' => 'true',
                'value_type' => 'boolean',
                'is_highlighted' => true,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'feature',
                'group_name' => '고급 기능',
                'group_order' => 1,
                'pos' => 3,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 2,
                'detail_type' => 'feature',
                'title' => '우선 지원',
                'description' => '24시간 우선 기술 지원',
                'icon' => 'fas fa-headset',
                'color' => 'text-blue-500',
                'value' => '24시간',
                'value_type' => 'text',
                'is_highlighted' => true,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'support',
                'group_name' => '지원 서비스',
                'group_order' => 3,
                'pos' => 4,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 모바일 앱 기본 플랜 상세정보
            [
                'service_plan_id' => 3,
                'detail_type' => 'feature',
                'title' => '화면 수',
                'description' => '앱에서 생성 가능한 화면 수',
                'icon' => 'fas fa-mobile-alt',
                'value' => '10',
                'value_type' => 'number',
                'unit' => '개',
                'is_highlighted' => false,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'core',
                'group_name' => '기본 기능',
                'group_order' => 1,
                'pos' => 1,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 3,
                'detail_type' => 'feature',
                'title' => '푸시 알림',
                'description' => '월간 푸시 알림 발송 가능 수',
                'icon' => 'fas fa-bell',
                'value' => '1000',
                'value_type' => 'number',
                'unit' => '회/월',
                'is_highlighted' => false,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'core',
                'group_name' => '알림 서비스',
                'group_order' => 2,
                'pos' => 2,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 디자인 기본 패키지 상세정보
            [
                'service_plan_id' => 4,
                'detail_type' => 'feature',
                'title' => '디자인 개수',
                'description' => '제공되는 디자인 시안 개수',
                'icon' => 'fas fa-palette',
                'value' => '10',
                'value_type' => 'number',
                'unit' => '개',
                'is_highlighted' => false,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'core',
                'group_name' => '디자인 작업',
                'group_order' => 1,
                'pos' => 1,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_plan_id' => 4,
                'detail_type' => 'feature',
                'title' => '수정 횟수',
                'description' => '무료 수정 가능 횟수',
                'icon' => 'fas fa-edit',
                'value' => '3',
                'value_type' => 'number',
                'unit' => '회',
                'is_highlighted' => false,
                'show_in_comparison' => true,
                'show_in_summary' => true,
                'category' => 'core',
                'group_name' => '디자인 작업',
                'group_order' => 1,
                'pos' => 2,
                'enable' => true,
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
        Schema::dropIfExists('service_plan_detail');
    }
};