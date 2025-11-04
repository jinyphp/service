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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->boolean('enable')->default(true);
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);

            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('category')->nullable();
            $table->unsignedBigInteger('category_id')->nullable(); // Foreign key to service_categories

            // 서비스 정보
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable(); // 할인 판매가
            $table->string('duration')->nullable(); // 예: "1-2주", "30일"

            // 이미지
            $table->string('image', 500)->nullable();
            $table->text('images')->nullable(); // JSON 배열

            // 서비스 상세 정보
            $table->text('features')->nullable(); // JSON 배열 - 서비스 특징
            $table->text('process')->nullable(); // JSON 배열 - 서비스 프로세스
            $table->text('requirements')->nullable(); // JSON 배열 - 요구사항
            $table->text('deliverables')->nullable(); // JSON 배열 - 결과물
            $table->string('tags')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // 관리
            $table->string('manager')->nullable();

            // Foreign key constraints
            $table->foreign('category_id')->references('id')->on('service_categories')->onDelete('set null');

            // Indexes
            $table->index(['enable', 'deleted_at']);
            $table->index(['category', 'deleted_at']);
            $table->index(['featured', 'deleted_at']);
            $table->index(['view_count']);
            $table->index(['category_id', 'deleted_at']);
        });

        // Insert default services
        DB::table('services')->insert([
            [
                'slug' => 'web-development',
                'title' => '웹사이트 개발',
                'description' => '반응형 웹사이트 및 웹 애플리케이션 개발 서비스',
                'content' => '<h3>서비스 내용</h3><p>최신 기술 스택을 활용한 웹사이트 개발</p><ul><li>React, Vue.js 등 모던 프레임워크</li><li>반응형 디자인</li><li>SEO 최적화</li><li>성능 최적화</li></ul>',
                'category' => 'web-development',
                'category_id' => 1,
                'price' => 1000000.00,
                'sale_price' => 800000.00,
                'duration' => '2-4주',
                'features' => json_encode(['반응형 디자인', 'SEO 최적화', '관리자 패널', '유지보수 1개월']),
                'process' => json_encode(['요구사항 분석', '디자인 작업', '개발', '테스트', '배포']),
                'requirements' => json_encode(['도메인', '호스팅', '콘텐츠 제공']),
                'deliverables' => json_encode(['완성된 웹사이트', '소스코드', '사용자 매뉴얼']),
                'tags' => 'HTML,CSS,JavaScript,PHP,Laravel',
                'meta_title' => '전문 웹사이트 개발 서비스',
                'meta_description' => '최신 기술로 반응형 웹사이트를 개발해드립니다.',
                'enable' => true,
                'featured' => true,
                'view_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'mobile-app-development',
                'title' => '모바일 앱 개발',
                'description' => 'iOS 및 Android 네이티브/하이브리드 앱 개발',
                'content' => '<h3>모바일 앱 개발</h3><p>크로스플랫폼 및 네이티브 앱 개발</p><ul><li>React Native / Flutter</li><li>iOS / Android 네이티브</li><li>API 연동</li><li>앱스토어 등록 지원</li></ul>',
                'category' => 'mobile-development',
                'category_id' => 2,
                'price' => 2000000.00,
                'sale_price' => 1600000.00,
                'duration' => '4-8주',
                'features' => json_encode(['크로스플랫폼', 'Push 알림', 'API 연동', '앱스토어 등록']),
                'process' => json_encode(['기획', 'UI/UX 디자인', '개발', '테스트', '배포']),
                'requirements' => json_encode(['앱 아이디어', '필요 기능 명세', '디자인 가이드']),
                'deliverables' => json_encode(['완성된 앱', '소스코드', '앱스토어 등록']),
                'tags' => 'React Native,Flutter,iOS,Android',
                'meta_title' => '모바일 앱 개발 전문 서비스',
                'meta_description' => 'iOS와 Android 앱을 전문적으로 개발해드립니다.',
                'enable' => true,
                'featured' => true,
                'view_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'ui-ux-design',
                'title' => 'UI/UX 디자인',
                'description' => '사용자 중심의 인터페이스 및 사용자 경험 디자인',
                'content' => '<h3>UI/UX 디자인</h3><p>사용자 경험을 고려한 디자인</p><ul><li>사용자 리서치</li><li>와이어프레임 및 프로토타입</li><li>반응형 디자인</li><li>사용성 테스트</li></ul>',
                'category' => 'design',
                'category_id' => 3,
                'price' => 500000.00,
                'sale_price' => 400000.00,
                'duration' => '1-2주',
                'features' => json_encode(['사용자 리서치', '와이어프레임', '프로토타입', '디자인 시스템']),
                'process' => json_encode(['리서치', '아이디어', '디자인', '프로토타입', '테스트']),
                'requirements' => json_encode(['프로젝트 목표', '타겟 사용자 정보', '참고 자료']),
                'deliverables' => json_encode(['디자인 파일', '프로토타입', '디자인 가이드']),
                'tags' => 'Figma,Sketch,Adobe XD,UI,UX',
                'meta_title' => '전문 UI/UX 디자인 서비스',
                'meta_description' => '사용자 중심의 UI/UX 디자인을 제공합니다.',
                'enable' => true,
                'featured' => false,
                'view_count' => 0,
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
        Schema::dropIfExists('services');
    }
};