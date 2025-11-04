<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jiny\Service\Models\ServiceUser;
use Jiny\Service\Models\Service;

class ServiceUserFactory extends Factory
{
    protected $model = ServiceUser::class;

    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-6 months', 'now');
        $billingCycle = $this->faker->randomElement(['monthly', 'quarterly', 'yearly', 'lifetime']);

        // 청구 주기에 따른 만료일 계산
        $expiresAt = null;
        if ($billingCycle !== 'lifetime') {
            $addDays = match($billingCycle) {
                'monthly' => 30,
                'quarterly' => 90,
                'yearly' => 365,
                default => 30
            };
            $expiresAt = (new \DateTime($startedAt->format('Y-m-d H:i:s')))->modify("+{$addDays} days");
        }

        $planPrice = $this->faker->randomFloat(2, 9.99, 299.99);
        $monthlyPrice = $billingCycle === 'monthly' ? $planPrice :
                      ($billingCycle === 'quarterly' ? round($planPrice / 3, 2) :
                      ($billingCycle === 'yearly' ? round($planPrice / 12, 2) : $planPrice));

        return [
            'user_uuid' => $this->faker->uuid,
            'user_shard' => 'user_' . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'user_id' => $this->faker->numberBetween(1, 100000),
            'user_email' => $this->faker->unique()->safeEmail,
            'user_name' => $this->faker->name,
            'service_id' => Service::factory(),
            'service_title' => $this->faker->words(2, true) . ' Service',
            'status' => $this->faker->randomElement(['pending', 'active', 'suspended', 'cancelled', 'expired']),
            'billing_cycle' => $billingCycle,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'next_billing_at' => $expiresAt ? (new \DateTime($expiresAt->format('Y-m-d H:i:s')))->modify('+1 day') : null,
            'cancelled_at' => null,
            'plan_name' => $this->faker->randomElement(['Basic Plan', 'Premium Plan', 'Enterprise Plan', 'Starter Plan']),
            'plan_price' => $planPrice,
            'plan_features' => json_encode([
                $this->faker->words(2, true),
                $this->faker->words(3, true),
                $this->faker->words(2, true)
            ]),
            'monthly_price' => $monthlyPrice,
            'total_paid' => $this->faker->randomFloat(2, 0, 1000),
            'payment_method' => $this->faker->randomElement(['신용카드', '계좌이체', 'PayPal', '가상계좌']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'auto_renewal' => $this->faker->boolean(70), // 70% 확률로 true
            'auto_upgrade' => $this->faker->boolean(30), // 30% 확률로 true
            'cancel_reason' => null,
            'refund_amount' => null,
            'refunded_at' => null,
            'admin_notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * 활성 상태의 서비스 사용자 생성
     */
    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'payment_status' => 'paid',
                'started_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
                'expires_at' => $this->faker->dateTimeBetween('now', '+6 months'),
            ];
        });
    }

    /**
     * 대기 상태의 서비스 사용자 생성
     */
    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'payment_status' => 'pending',
                'started_at' => null,
                'expires_at' => null,
            ];
        });
    }

    /**
     * 일시정지 상태의 서비스 사용자 생성
     */
    public function suspended(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'suspended',
                'payment_status' => 'paid',
                'admin_notes' => '관리자에 의해 일시정지됨',
            ];
        });
    }

    /**
     * 취소된 서비스 사용자 생성
     */
    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'cancelled_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'auto_renewal' => false,
                'cancel_reason' => $this->faker->randomElement([
                    '사용자 요청',
                    '결제 실패',
                    '서비스 불만족',
                    '비용 부담'
                ]),
            ];
        });
    }

    /**
     * 만료된 서비스 사용자 생성
     */
    public function expired(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
                'expires_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
                'auto_renewal' => false,
            ];
        });
    }

    /**
     * 월간 구독자 생성
     */
    public function monthly(): self
    {
        return $this->state(function (array $attributes) {
            $planPrice = $this->faker->randomFloat(2, 9.99, 49.99);
            return [
                'billing_cycle' => 'monthly',
                'plan_price' => $planPrice,
                'monthly_price' => $planPrice,
                'expires_at' => $this->faker->dateTimeBetween('now', '+2 months'),
            ];
        });
    }

    /**
     * 연간 구독자 생성
     */
    public function yearly(): self
    {
        return $this->state(function (array $attributes) {
            $planPrice = $this->faker->randomFloat(2, 99.99, 499.99);
            return [
                'billing_cycle' => 'yearly',
                'plan_price' => $planPrice,
                'monthly_price' => round($planPrice / 12, 2),
                'expires_at' => $this->faker->dateTimeBetween('now', '+14 months'),
            ];
        });
    }

    /**
     * 평생 구독자 생성
     */
    public function lifetime(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'billing_cycle' => 'lifetime',
                'plan_price' => $this->faker->randomFloat(2, 299.99, 999.99),
                'expires_at' => null,
                'next_billing_at' => null,
                'auto_renewal' => false,
            ];
        });
    }

    /**
     * 환불된 서비스 사용자 생성
     */
    public function refunded(): self
    {
        return $this->state(function (array $attributes) {
            $refundAmount = $this->faker->randomFloat(2, 10, 200);
            return [
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'refund_amount' => $refundAmount,
                'refunded_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'cancel_reason' => '환불 요청',
                'cancelled_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * 자동 갱신이 활성화된 사용자 생성
     */
    public function autoRenewal(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'auto_renewal' => true,
                'payment_status' => 'paid',
                'payment_method' => $this->faker->randomElement(['신용카드', 'PayPal']),
            ];
        });
    }

    /**
     * 특정 서비스에 속한 사용자 생성
     */
    public function forService($serviceId): self
    {
        return $this->state(function (array $attributes) use ($serviceId) {
            return [
                'service_id' => $serviceId,
            ];
        });
    }

    /**
     * 특정 사용자 샤드에 속한 사용자 생성
     */
    public function inShard($shardNumber): self
    {
        return $this->state(function (array $attributes) use ($shardNumber) {
            return [
                'user_shard' => 'user_' . str_pad($shardNumber, 3, '0', STR_PAD_LEFT),
            ];
        });
    }

    /**
     * 결제 실패 상태의 사용자 생성
     */
    public function paymentFailed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'failed',
                'auto_renewal' => false,
                'admin_notes' => '결제 실패로 인한 구독 정지',
            ];
        });
    }

    /**
     * 프리미엄 플랜 사용자 생성
     */
    public function premium(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'plan_name' => 'Premium Plan',
                'plan_price' => $this->faker->randomFloat(2, 49.99, 99.99),
                'plan_features' => json_encode([
                    'Advanced Features',
                    'Priority Support',
                    'Custom Integration',
                    'Analytics Dashboard'
                ]),
            ];
        });
    }

    /**
     * 베이직 플랜 사용자 생성
     */
    public function basic(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'plan_name' => 'Basic Plan',
                'plan_price' => $this->faker->randomFloat(2, 9.99, 29.99),
                'plan_features' => json_encode([
                    'Basic Features',
                    'Email Support',
                    'Standard Usage'
                ]),
            ];
        });
    }
}