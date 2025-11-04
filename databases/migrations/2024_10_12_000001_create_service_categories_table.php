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
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->boolean('enable')->default(true);
            $table->integer('pos')->default(0); // 정렬 순서

            $table->string('code')->unique(); // 카테고리 코드 (영문)
            $table->string('title'); // 카테고리 이름
            $table->text('description')->nullable(); // 카테고리 설명
            $table->string('image', 500)->nullable(); // 카테고리 이미지
            $table->string('color', 7)->nullable(); // 카테고리 색상 (#ffffff)
            $table->string('icon')->nullable(); // 아이콘 클래스

            // 계층형 구조를 위한 부모 카테고리 ID
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('service_categories')->onDelete('set null');

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // 관리
            $table->string('manager')->nullable();

            $table->index(['enable', 'deleted_at']);
            $table->index(['parent_id', 'pos']);
            $table->index(['code']);
        });

        // Insert default categories
        DB::table('service_categories')->insert([
            [
                'code' => 'web-development',
                'title' => '웹 개발',
                'description' => '웹사이트 및 웹 애플리케이션 개발 서비스',
                'color' => '#3B82F6',
                'icon' => 'fas fa-code',
                'pos' => 1,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'mobile-development',
                'title' => '모바일 개발',
                'description' => '모바일 앱 개발 서비스',
                'color' => '#10B981',
                'icon' => 'fas fa-mobile-alt',
                'pos' => 2,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'design',
                'title' => '디자인',
                'description' => 'UI/UX 디자인 및 그래픽 디자인 서비스',
                'color' => '#F59E0B',
                'icon' => 'fas fa-palette',
                'pos' => 3,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'marketing',
                'title' => '마케팅',
                'description' => '디지털 마케팅 및 SEO 서비스',
                'color' => '#EF4444',
                'icon' => 'fas fa-bullhorn',
                'pos' => 4,
                'enable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'consulting',
                'title' => '컨설팅',
                'description' => '기술 컨설팅 및 비즈니스 컨설팅 서비스',
                'color' => '#8B5CF6',
                'icon' => 'fas fa-chart-line',
                'pos' => 5,
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
        Schema::dropIfExists('service_categories');
    }
};