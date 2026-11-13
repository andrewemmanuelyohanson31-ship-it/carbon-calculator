<?php

namespace App\Services;

/**
 * All results are kg CO2e per year for the whole household.
 * Factors are rounded public averages (IEA, UK DEFRA 2023, US EPA calculator) — adjust for your country.
 */
class CarbonCalculator
{
    public const CATEGORIES = [
        'household' => 'Household energy',
        'transport' => 'Transport',
        'others' => 'Shopping & waste',
    ];

    public const ELECTRICITY_KWH = 0.475;   // kg per kWh, world grid average (IEA)
    public const NATURAL_GAS_M3 = 2.02;     // kg per m³ (DEFRA)
    public const LPG_KG = 2.94;             // kg per kg of LPG (DEFRA)
    public const FUEL_LITRE = ['petrol' => 2.31, 'diesel' => 2.68]; // kg CO2 per litre
    public const NON_CO2_FACTOR = 100 / 95; // EPA: CO2 is ~95% of vehicle GHG emissions
    public const MOTORBIKE_KM = 0.114;      // kg per km (DEFRA)
    public const BUS_KM = 0.105;            // kg per passenger-km (DEFRA)
    public const TRAIN_KM = 0.035;          // kg per passenger-km (DEFRA)
    public const FLIGHT_HOUR = 90;          // kg per passenger flight hour
    public const CLOTHING_ITEM = 15;        // kg per new clothing item, rough average
    public const ELECTRONIC_DEVICE = 100;   // kg per new phone/laptop/etc., rough average
    public const WASTE_PER_PERSON = 314;    // kg per person per year before recycling (EPA: 692 lb)
    public const RECYCLING_SAVINGS = [      // kg saved per person per year (EPA)
        'paper' => 63.8,
        'plastic' => 16.1,
        'glass' => 11.5,
        'metal' => 40.5,
    ];

    public const WORLD_AVERAGE_PER_PERSON = 4700; // kg CO2 per person per year

    // Per person per year. 2 t ≈ what the Paris 1.5–2 °C goals allow by 2030; 10 t ≈ high-income countries.
    public const RATINGS = [
        ['max' => 2000, 'level' => 'low', 'label' => 'Low',
            'text' => 'Great — in line with the roughly 2 t per person needed to meet the Paris climate goals.'],
        ['max' => 4700, 'level' => 'moderate', 'label' => 'Moderate',
            'text' => 'Still fine — below the world average of 4.7 t, but above the Paris target.'],
        ['max' => 10000, 'level' => 'high', 'label' => 'High',
            'text' => 'Above the world average. The recommendations show where to cut first.'],
        ['max' => PHP_FLOAT_MAX, 'level' => 'very-high', 'label' => 'Very high',
            'text' => 'More than twice the world average, similar to people in high-income countries.'],
    ];

    public const GAUGE_MAX = 12000; // right end of the results gauge, kg

    /** @return array{max: float, level: string, label: string, text: string} */
    public function rating(float $kgPerPerson): array
    {
        foreach (self::RATINGS as $rating) {
            if ($kgPerPerson < $rating['max']) {
                return $rating;
            }
        }

        return self::RATINGS[array_key_last(self::RATINGS)];
    }

    /** @return array<string, float> kg CO2e per year for each chosen category */
    public function calculate(array $in): array
    {
        $result = [];
        $people = $in['household_size'];

        if (in_array('household', $in['categories'], true)) {
            $electricity = ($in['electricity_kwh'] ?? 0) * 12 * self::ELECTRICITY_KWH
                * (1 - ($in['green_energy_percent'] ?? 0) / 100);
            $result['household'] = $electricity
                + ($in['natural_gas_m3'] ?? 0) * 12 * self::NATURAL_GAS_M3
                + ($in['lpg_kg'] ?? 0) * 12 * self::LPG_KG;
        }

        if (in_array('transport', $in['categories'], true)) {
            $result['transport'] = $this->carYearly($in)
                + ($in['motorbike_km_week'] ?? 0) * 52 * self::MOTORBIKE_KM
                + ($in['bus_km_week'] ?? 0) * 52 * self::BUS_KM
                + ($in['train_km_week'] ?? 0) * 52 * self::TRAIN_KM
                + ($in['flight_hours_year'] ?? 0) * self::FLIGHT_HOUR;
        }

        if (in_array('others', $in['categories'], true)) {
            $recycled = array_sum(array_intersect_key(self::RECYCLING_SAVINGS, array_flip($in['recycles'] ?? [])));
            $result['others'] = ($in['clothing_items_month'] ?? 0) * 12 * self::CLOTHING_ITEM
                + ($in['electronics_year'] ?? 0) * self::ELECTRONIC_DEVICE
                + max(0, self::WASTE_PER_PERSON - $recycled) * $people;
        }

        return array_map(fn ($kg) => round($kg, 2), $result);
    }

    /** @return list<array{title: string, text: string, saving: float}> sorted by biggest saving */
    public function recommendations(array $in): array
    {
        $tips = [];
        $add = function (string $title, string $text, float $saving) use (&$tips) {
            if ($saving > 0) {
                $tips[] = ['title' => $title, 'text' => $text, 'saving' => round($saving, 1)];
            }
        };
        $cats = $in['categories'];

        if (in_array('household', $cats, true)) {
            $electricity = ($in['electricity_kwh'] ?? 0) * 12 * self::ELECTRICITY_KWH;
            $green = ($in['green_energy_percent'] ?? 0) / 100;
            $add('Switch to LED bulbs and unplug idle devices',
                'Lighting and standby power are about 10% of a typical electricity bill.',
                $electricity * (1 - $green) * 0.10);
            $add('Get part of your power from renewables',
                'A green tariff or rooftop solar covering half of your remaining use.',
                $electricity * (1 - $green) * 0.9 * 0.5); // applied after the LED saving so the total isn't double-counted
            $add('Use less gas for cooking and hot water',
                'Lids on pots, shorter showers and an insulated water heater cut gas use by around 10%.',
                (($in['natural_gas_m3'] ?? 0) * self::NATURAL_GAS_M3 + ($in['lpg_kg'] ?? 0) * self::LPG_KG) * 12 * 0.10);
        }

        if (in_array('transport', $cats, true)) {
            $car = $this->carYearly($in);
            $carKm = ($in['car_km_week'] ?? 0) * 52;
            $add('Swap 1 in 5 car trips for transit, cycling or walking',
                'Shorter trips are the easiest to replace.',
                $car * 0.2 - $carKm * 0.2 * self::BUS_KM);
            $add('Keep your car maintained',
                'Correct tyre pressure and regular servicing improve fuel economy by about 4%.',
                $car * 0.04);
            $add('Fly less',
                'Halving your flight hours — take the train or meet online when you can.',
                ($in['flight_hours_year'] ?? 0) * self::FLIGHT_HOUR * 0.5);
        }

        if (in_array('others', $cats, true)) {
            $add('Buy fewer new clothes',
                'Buying half as many new items, or choosing secondhand, halves their footprint.',
                ($in['clothing_items_month'] ?? 0) * 12 * self::CLOTHING_ITEM * 0.5);
            $add('Keep your electronics longer',
                'Using phones and laptops twice as long halves their yearly manufacturing footprint.',
                ($in['electronics_year'] ?? 0) * self::ELECTRONIC_DEVICE * 0.5);
            foreach (array_diff(array_keys(self::RECYCLING_SAVINGS), $in['recycles'] ?? []) as $material) {
                $add("Start recycling {$material}", "Separate {$material} from your general waste.",
                    self::RECYCLING_SAVINGS[$material] * $in['household_size']);
            }
        }

        usort($tips, fn ($a, $b) => $b['saving'] <=> $a['saving']);

        return $tips;
    }

    private function carYearly(array $in): float
    {
        $kmPerLitre = $in['car_km_per_litre'] ?? 0;
        if (($in['car_km_week'] ?? 0) <= 0 || $kmPerLitre <= 0) {
            return 0;
        }

        return $in['car_km_week'] * 52 / $kmPerLitre
            * self::FUEL_LITRE[$in['car_fuel'] ?? 'petrol'] * self::NON_CO2_FACTOR;
    }
}
