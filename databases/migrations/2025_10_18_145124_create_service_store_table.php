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
        Schema::create('service_store', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            // 기본 정보
            $table->boolean('enable')->default(true);
            $table->boolean('featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();

            // 카테고리
            $table->string('category')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('store_categories')->onDelete('set null');

            // 가격
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();

            // 이미지 및 미디어
            $table->string('image')->nullable();
            $table->text('images')->nullable(); // JSON

            // 서비스 상세 정보
            $table->text('features')->nullable(); // JSON
            $table->text('specifications')->nullable(); // JSON
            $table->string('tags')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // 서비스 옵션
            $table->boolean('enable_purchase')->default(true);
            $table->boolean('enable_cart')->default(true);
            $table->boolean('enable_quote')->default(true);
            $table->boolean('enable_contact')->default(true);
            $table->boolean('enable_social_share')->default(true);

            // 관리자
            $table->string('manager')->nullable();

            // 서비스 특화 필드
            $table->string('duration')->nullable(); // 소요 기간
            $table->text('deliverables')->nullable(); // JSON - 결과물
            $table->integer('revision_limit')->nullable(); // 수정 횟수 제한

            // 인덱스
            $table->index(['enable', 'deleted_at']);
            $table->index(['category', 'deleted_at']);
            $table->index(['featured', 'deleted_at']);
            $table->index(['view_count']);
            $table->index(['category_id', 'deleted_at']);
            $table->index(['enable_purchase', 'enable_cart', 'enable_quote', 'enable_contact']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_store');
    }
};
