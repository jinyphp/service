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
        Schema::table('service_plan_price', function (Blueprint $table) {
            // service_id 컬럼 추가 (서비스에 직접 연결용)
            $table->unsignedBigInteger('service_id')->nullable()->after('service_plan_id');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

            // service_plan_id를 nullable로 변경 (기존 데이터 유지)
            $table->unsignedBigInteger('service_plan_id')->nullable()->change();

            // 인덱스 추가
            $table->index(['service_id', 'enable']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_plan_price', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            $table->unsignedBigInteger('service_plan_id')->nullable(false)->change();
        });
    }
};