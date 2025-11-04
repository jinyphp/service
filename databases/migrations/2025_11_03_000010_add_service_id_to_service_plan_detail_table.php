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
        Schema::table('service_plan_detail', function (Blueprint $table) {
            // service_id 컬럼 추가 (서비스에 직접 연결)
            $table->unsignedBigInteger('service_id')->nullable()->after('id');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

            // service_plan_id를 nullable로 변경 (플랜과 서비스 둘 다 지원)
            $table->unsignedBigInteger('service_plan_id')->nullable()->change();

            // 인덱스 추가
            $table->index(['service_id', 'enable']);
            $table->index(['service_id', 'detail_type', 'enable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_plan_detail', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            $table->unsignedBigInteger('service_plan_id')->nullable(false)->change();
        });
    }
};