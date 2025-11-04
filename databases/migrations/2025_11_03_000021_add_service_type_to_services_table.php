<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 서비스 테이블에 service_type 컬럼 추가
     *
     * 서비스와 파트너 전문분야 간의 연관성을 위한 service_type 필드 추가
     * 파트너 배정 시 전문분야 매칭에 활용됨
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // 서비스 타입 (파트너 전문분야와 매핑용)
            $table->string('service_type', 50)->nullable()->after('description');
            // 예시: 'air_conditioning', 'plumbing', 'electrical', 'cleaning', 'maintenance'

            // 파트너 요구 등급 (선택사항)
            $table->string('required_partner_tier', 20)->nullable()->after('service_type');
            // bronze, silver, gold, platinum - 해당 등급 이상의 파트너만 배정

            // 전문 기술 요구 여부
            $table->boolean('requires_specialist')->default(false)->after('required_partner_tier');
            // 전문가 필요 여부 (복잡한 작업의 경우)

            // 인덱스 추가
            $table->index(['service_type', 'enabled']); // 서비스 타입별 활성 서비스 조회용
            $table->index(['required_partner_tier', 'service_type']); // 등급 및 타입별 조회용
        });

        // 기본 서비스 타입 데이터 삽입
        $this->insertDefaultServiceTypes();
    }

    /**
     * 기본 서비스 타입 설정
     */
    private function insertDefaultServiceTypes()
    {
        // 기존 서비스들에 기본 service_type 설정
        DB::table('services')->where('name', 'LIKE', '%에어컨%')->update([
            'service_type' => 'air_conditioning',
            'required_partner_tier' => 'bronze',
            'requires_specialist' => false
        ]);

        DB::table('services')->where('name', 'LIKE', '%배관%')->update([
            'service_type' => 'plumbing',
            'required_partner_tier' => 'silver',
            'requires_specialist' => true
        ]);

        DB::table('services')->where('name', 'LIKE', '%전기%')->update([
            'service_type' => 'electrical',
            'required_partner_tier' => 'silver',
            'requires_specialist' => true
        ]);

        DB::table('services')->where('name', 'LIKE', '%청소%')->update([
            'service_type' => 'cleaning',
            'required_partner_tier' => 'bronze',
            'requires_specialist' => false
        ]);

        DB::table('services')->where('name', 'LIKE', '%수리%')->update([
            'service_type' => 'maintenance',
            'required_partner_tier' => 'bronze',
            'requires_specialist' => false
        ]);

        // 나머지 서비스들은 일반 maintenance로 설정
        DB::table('services')->whereNull('service_type')->update([
            'service_type' => 'general',
            'required_partner_tier' => 'bronze',
            'requires_specialist' => false
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['service_type', 'enabled']);
            $table->dropIndex(['required_partner_tier', 'service_type']);
            $table->dropColumn(['service_type', 'required_partner_tier', 'requires_specialist']);
        });
    }
};