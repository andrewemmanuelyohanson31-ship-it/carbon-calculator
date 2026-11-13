@extends('layouts.app', ['step' => 2])

@section('title', 'Your details')

@section('content')
    <h1>Tell us about your household</h1>
    <p class="lead">Leave a field empty if it doesn't apply. Check your bills for the most accurate numbers.</p>

    <form method="POST" action="{{ route('calculator.store') }}" class="stack">
        @csrf
        @foreach ($chosen as $category)
            <input type="hidden" name="categories[]" value="{{ $category }}">
        @endforeach

        <fieldset class="card">
            <legend>General</legend>
            <x-field name="household_size" label="People living in your home" step="1" min="1" max="20" value="1" />
        </fieldset>

        @if (in_array('household', $chosen))
            <fieldset class="card">
                <legend>Household energy</legend>
                <x-field name="electricity_kwh" label="Electricity use" unit="kWh / month" hint="Shown on your electricity bill." />
                <x-field name="green_energy_percent" label="Electricity from renewables (solar, green plan)" unit="%" max="100" />
                <x-field name="natural_gas_m3" label="Natural gas" unit="m³ / month" />
                <x-field name="lpg_kg" label="LPG (cooking gas)" unit="kg / month" hint="A small cylinder is 3 kg, a large one 12 kg." />
            </fieldset>
        @endif

        @if (in_array('transport', $chosen))
            <fieldset class="card">
                <legend>Transport</legend>
                <x-field name="car_km_week" label="Distance by car" unit="km / week" />
                <x-field name="car_km_per_litre" label="Car fuel efficiency" unit="km / litre" min="1" value="12" />
                <div class="field">
                    <label for="car_fuel">Car fuel</label>
                    <select id="car_fuel" name="car_fuel">
                        <option value="petrol" @selected(old('car_fuel') !== 'diesel')>Petrol</option>
                        <option value="diesel" @selected(old('car_fuel') === 'diesel')>Diesel</option>
                    </select>
                </div>
                <x-field name="motorbike_km_week" label="Distance by motorbike" unit="km / week" />
                <x-field name="bus_km_week" label="Distance by bus" unit="km / week" />
                <x-field name="train_km_week" label="Distance by train" unit="km / week" />
                <x-field name="flight_hours_year" label="Time spent flying" unit="hours / year" />
            </fieldset>
        @endif

        @if (in_array('others', $chosen))
            <fieldset class="card">
                <legend>Shopping &amp; waste</legend>
                <x-field name="clothing_items_month" label="New clothing items bought" unit="items / month" step="1" />
                <x-field name="electronics_year" label="New electronic devices bought" unit="devices / year" step="1" hint="Phones, laptops, TVs, etc." />
                <div class="field">
                    <span class="label">Which do you recycle?</span>
                    <div class="checks">
                        @foreach (['paper', 'plastic', 'glass', 'metal'] as $material)
                            <label><input type="checkbox" name="recycles[]" value="{{ $material }}" @checked(in_array($material, old('recycles', [])))> {{ ucfirst($material) }}</label>
                        @endforeach
                    </div>
                </div>
            </fieldset>
        @endif

        <div class="actions">
            <a href="{{ route('calculator.choose') }}" class="btn btn-ghost">Back</a>
            <button type="submit" class="btn">See my results</button>
        </div>
    </form>
@endsection
