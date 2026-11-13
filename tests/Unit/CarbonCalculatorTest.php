<?php

namespace Tests\Unit;

use App\Services\CarbonCalculator;
use PHPUnit\Framework\TestCase;

class CarbonCalculatorTest extends TestCase
{
    private array $input = [
        'categories' => ['household', 'transport', 'others'],
        'household_size' => 2,
        'electricity_kwh' => 200, 'lpg_kg' => 3,                                    // 1140 + 105.84
        'car_km_week' => 100, 'car_km_per_litre' => 10, 'car_fuel' => 'petrol',     // 520 L * 2.31 / 0.95
        'flight_hours_year' => 2,                                                   // 180
        'clothing_items_month' => 2, 'electronics_year' => 1,                       // 360 + 100
        'recycles' => ['paper', 'plastic'],                                         // (314 - 79.9) * 2
    ];

    public function test_calculates_each_category(): void
    {
        $result = (new CarbonCalculator)->calculate($this->input);

        $this->assertEqualsWithDelta(1245.84, $result['household'], 0.01);
        $this->assertEqualsWithDelta(1444.42, $result['transport'], 0.01);
        $this->assertEqualsWithDelta(928.20, $result['others'], 0.01);
    }

    public function test_only_chosen_categories_are_calculated(): void
    {
        $result = (new CarbonCalculator)->calculate(['categories' => ['transport']] + $this->input);

        $this->assertSame(['transport'], array_keys($result));
    }

    public function test_recommendations_are_sorted_and_skip_what_is_already_done(): void
    {
        $tips = (new CarbonCalculator)->recommendations($this->input);
        $titles = array_column($tips, 'title');

        $this->assertContains('Start recycling metal', $titles);
        $this->assertNotContains('Start recycling paper', $titles);
        $savings = array_column($tips, 'saving');
        $sorted = $savings;
        rsort($sorted);
        $this->assertSame($sorted, $savings);
    }
}
