<?php

namespace Tests\Feature;

use App\Models\Calculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorFlowTest extends TestCase
{
    use RefreshDatabase;

    private function submit(array $extra = [])
    {
        return $this->post('/calculator', $extra + [
            'categories' => ['household', 'transport'],
            'household_size' => 2,
            'electricity_kwh' => 200,
            'car_km_week' => 100,
            'car_km_per_litre' => 10,
            'car_fuel' => 'petrol',
        ]);
    }

    public function test_full_flow_saves_and_shows_results_recommendations_and_history(): void
    {
        $this->get('/')->assertOk()->assertSee('Start calculating');
        $this->get('/calculator')->assertOk();
        $this->get('/calculator/form?categories[]=transport')->assertOk()
            ->assertSee('Distance by car')->assertSee('name="_token"', false);

        $response = $this->submit();
        $calc = Calculation::sole();
        $response->assertRedirect("/results/{$calc->id}");
        $visitor = $response->getCookie('visitor_id')->getValue();

        $this->withCookie('visitor_id', $visitor)->get("/results/{$calc->id}")
            ->assertOk()->assertSee('Household energy')->assertSee('Transport')
            ->assertHeader('X-Frame-Options', 'DENY');
        $this->withCookie('visitor_id', $visitor)->get("/results/{$calc->id}/recommendations")
            ->assertOk()->assertSee('Keep your car maintained');
        $this->withCookie('visitor_id', $visitor)->get('/history')
            ->assertOk()->assertSee(number_format($calc->total_kg / 1000, 2));
    }

    public function test_other_visitors_cannot_see_a_result(): void
    {
        $this->submit();
        $calc = Calculation::sole();

        $this->get("/results/{$calc->id}")->assertNotFound();
        $this->withCookie('visitor_id', '00000000-0000-4000-8000-000000000000')
            ->get("/results/{$calc->id}/recommendations")->assertNotFound();
        $this->get('/history')->assertOk()->assertSee('No calculations yet');
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->submit(['electricity_kwh' => -5])->assertSessionHasErrors('electricity_kwh');
        $this->submit(['categories' => ['hacking']])->assertSessionHasErrors('categories.0');
        $this->get('/calculator/form')->assertSessionHasErrors('categories');
        $this->assertSame(0, Calculation::count());
    }
}
