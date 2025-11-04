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
        // 추가 서비스 카테고리 삽입
        $additionalCategories = [
            [
                'code' => 'data-analysis',
                'title' => '데이터 분석',
                'description' => '빅데이터 분석, 머신러닝, AI 관련 서비스',
                'color' => '#06B6D4',
                'icon' => 'fas fa-chart-bar',
                'pos' => 6,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'security',
                'title' => '보안',
                'description' => '웹 보안, 네트워크 보안, 보안 컨설팅 서비스',
                'color' => '#DC2626',
                'icon' => 'fas fa-shield-alt',
                'pos' => 7,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'cloud-services',
                'title' => '클라우드 서비스',
                'description' => 'AWS, Azure, GCP 등 클라우드 인프라 구축 및 관리',
                'color' => '#0891B2',
                'icon' => 'fas fa-cloud',
                'pos' => 8,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'devops',
                'title' => 'DevOps',
                'description' => 'CI/CD, 자동화, 인프라 관리 서비스',
                'color' => '#059669',
                'icon' => 'fas fa-cogs',
                'pos' => 9,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ecommerce',
                'title' => '이커머스',
                'description' => '온라인 쇼핑몰 구축 및 운영 서비스',
                'color' => '#7C3AED',
                'icon' => 'fas fa-shopping-cart',
                'pos' => 10,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'content-management',
                'title' => '콘텐츠 관리',
                'description' => 'CMS 구축, 콘텐츠 제작 및 관리 서비스',
                'color' => '#DB2777',
                'icon' => 'fas fa-file-alt',
                'pos' => 11,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'automation',
                'title' => '자동화',
                'description' => '업무 프로세스 자동화, RPA 서비스',
                'color' => '#9333EA',
                'icon' => 'fas fa-robot',
                'pos' => 12,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'education',
                'title' => '교육',
                'description' => '온라인 교육 플랫폼, LMS 구축 서비스',
                'color' => '#EA580C',
                'icon' => 'fas fa-graduation-cap',
                'pos' => 13,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'healthcare',
                'title' => '헬스케어',
                'description' => '의료 IT, 헬스케어 앱 개발 서비스',
                'color' => '#16A34A',
                'icon' => 'fas fa-heartbeat',
                'pos' => 14,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'fintech',
                'title' => '핀테크',
                'description' => '금융 기술, 결제 시스템 개발 서비스',
                'color' => '#B91C1C',
                'icon' => 'fas fa-credit-card',
                'pos' => 15,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'blockchain',
                'title' => '블록체인',
                'description' => '블록체인 개발, 암호화폐 관련 서비스',
                'color' => '#1D4ED8',
                'icon' => 'fas fa-link',
                'pos' => 16,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'iot',
                'title' => 'IoT',
                'description' => '사물인터넷 개발, 스마트 디바이스 연동 서비스',
                'color' => '#0F766E',
                'icon' => 'fas fa-wifi',
                'pos' => 17,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'maintenance',
                'title' => '유지보수',
                'description' => '시스템 유지보수, 기술 지원 서비스',
                'color' => '#6B7280',
                'icon' => 'fas fa-tools',
                'pos' => 18,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'training',
                'title' => '교육 훈련',
                'description' => '기술 교육, 직무 교육, 코칭 서비스',
                'color' => '#0D9488',
                'icon' => 'fas fa-chalkboard-teacher',
                'pos' => 19,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'translation',
                'title' => '번역',
                'description' => '다국어 번역, 현지화 서비스',
                'color' => '#7C2D12',
                'icon' => 'fas fa-language',
                'pos' => 20,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // 기존 카테고리가 없는 경우에만 삽입
        foreach ($additionalCategories as $category) {
            $exists = DB::table('service_categories')
                ->where('code', $category['code'])
                ->exists();

            if (!$exists) {
                DB::table('service_categories')->insert($category);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 추가한 카테고리들을 삭제
        $categoryCodes = [
            'data-analysis', 'security', 'cloud-services', 'devops', 'ecommerce',
            'content-management', 'automation', 'education', 'healthcare', 'fintech',
            'blockchain', 'iot', 'maintenance', 'training', 'translation'
        ];

        DB::table('service_categories')
            ->whereIn('code', $categoryCodes)
            ->delete();
    }
};