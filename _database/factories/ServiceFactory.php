<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jiny\Service\Models\Service;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true) . ' Service',
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'draft']),
            'category' => $this->faker->randomElement(['web-development', 'mobile-app', 'consulting', 'design', 'marketing']),
            'price' => $this->faker->randomFloat(2, 9.99, 999.99),
            'currency' => 'KRW',
            'billing_type' => $this->faker->randomElement(['one-time', 'subscription', 'usage-based']),
            'features' => json_encode([
                $this->faker->words(3, true),
                $this->faker->words(4, true),
                $this->faker->words(2, true),
            ]),
            'terms' => $this->faker->paragraphs(3, true),
            'is_featured' => $this->faker->boolean(20),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * 활성 서비스 상태
     */
    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    /**
     * 비활성 서비스 상태
     */
    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
            ];
        });
    }

    /**
     * 추천 서비스
     */
    public function featured(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_featured' => true,
                'status' => 'active',
            ];
        });
    }

    /**
     * 구독형 서비스
     */
    public function subscription(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'billing_type' => 'subscription',
                'price' => $this->faker->randomFloat(2, 9.99, 99.99),
            ];
        });
    }

    /**
     * 일회성 서비스
     */
    public function oneTime(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'billing_type' => 'one-time',
                'price' => $this->faker->randomFloat(2, 99.99, 999.99),
            ];
        });
    }
}