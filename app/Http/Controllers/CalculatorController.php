<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Services\CarbonCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CalculatorController extends Controller
{
    public function __construct(private CarbonCalculator $calculator) {}

    public function choose()
    {
        return view('calculator.choose', ['categories' => CarbonCalculator::CATEGORIES]);
    }

    public function form(Request $request)
    {
        $data = $request->validate([
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => [Rule::in(array_keys(CarbonCalculator::CATEGORIES))],
        ], ['categories.required' => 'Pick at least one category to continue.']);

        return view('calculator.form', ['chosen' => $data['categories']]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => [Rule::in(array_keys(CarbonCalculator::CATEGORIES))],
            'household_size' => ['required', 'integer', 'between:1,20'],
            // household energy (per month)
            'electricity_kwh' => ['nullable', 'numeric', 'between:0,100000'],
            'green_energy_percent' => ['nullable', 'numeric', 'between:0,100'],
            'natural_gas_m3' => ['nullable', 'numeric', 'between:0,10000'],
            'lpg_kg' => ['nullable', 'numeric', 'between:0,1000'],
            // transport
            'car_km_week' => ['nullable', 'numeric', 'between:0,10000'],
            'car_km_per_litre' => ['nullable', 'numeric', 'between:1,100'],
            'car_fuel' => ['nullable', Rule::in(array_keys(CarbonCalculator::FUEL_LITRE))],
            'motorbike_km_week' => ['nullable', 'numeric', 'between:0,10000'],
            'bus_km_week' => ['nullable', 'numeric', 'between:0,10000'],
            'train_km_week' => ['nullable', 'numeric', 'between:0,10000'],
            'flight_hours_year' => ['nullable', 'numeric', 'between:0,2000'],
            // shopping & waste
            'clothing_items_month' => ['nullable', 'integer', 'between:0,1000'],
            'electronics_year' => ['nullable', 'integer', 'between:0,100'],
            'recycles' => ['nullable', 'array'],
            'recycles.*' => [Rule::in(array_keys(CarbonCalculator::RECYCLING_SAVINGS))],
        ]);

        $visitorId = $this->visitorId($request) ?? (string) Str::uuid();
        Cookie::queue('visitor_id', $visitorId, 60 * 24 * 365); // encrypted + HttpOnly by Laravel

        $breakdown = $this->calculator->calculate($data);
        $calculation = Calculation::create([
            'visitor_id' => $visitorId,
            'household_size' => $data['household_size'],
            'inputs' => $data,
            'breakdown' => $breakdown,
            'total_kg' => array_sum($breakdown),
        ]);

        return redirect()->route('results', $calculation);
    }

    public function results(Request $request, Calculation $calculation)
    {
        $this->authorizeOwner($request, $calculation);

        return view('calculator.results', [
            'calc' => $calculation,
            'labels' => CarbonCalculator::CATEGORIES,
            'worldAverage' => CarbonCalculator::WORLD_AVERAGE_PER_PERSON,
            'rating' => $this->calculator->rating($calculation->total_kg / $calculation->household_size),
        ]);
    }

    public function recommendations(Request $request, Calculation $calculation)
    {
        $this->authorizeOwner($request, $calculation);

        return view('calculator.recommendations', [
            'calc' => $calculation,
            'tips' => $this->calculator->recommendations($calculation->inputs),
        ]);
    }

    public function history(Request $request)
    {
        $visitorId = $this->visitorId($request);

        return view('calculator.history', [
            'calculations' => $visitorId
                ? Calculation::where('visitor_id', $visitorId)->latest()->paginate(10)
                : collect(),
            'labels' => CarbonCalculator::CATEGORIES,
            'calculator' => $this->calculator,
        ]);
    }

    private function visitorId(Request $request): ?string
    {
        $id = $request->cookie('visitor_id');

        return is_string($id) && Str::isUuid($id) ? $id : null;
    }

    // 404 instead of 403 so other people's result IDs can't be confirmed to exist
    private function authorizeOwner(Request $request, Calculation $calculation): void
    {
        abort_unless($calculation->visitor_id === $this->visitorId($request), 404);
    }
}
