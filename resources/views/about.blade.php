@extends('layouts.app')

@section('title', 'About')

@php $c = \App\Services\CarbonCalculator::class; @endphp

@section('content')
    <h1>About CarbonCheck</h1>
    <p class="lead">Why this website exists, how to use it, and how the numbers are calculated.</p>

    <nav class="toc card" aria-label="On this page">
        <a href="#background">Background</a>
        <a href="#how-it-works">How it works</a>
        <a href="#calculation">How we calculate</a>
        <a href="#rating">Rating scale</a>
        <a href="#limits">Privacy &amp; limitations</a>
    </nav>

    <section id="background" class="prose">
        <h2>Background</h2>
        <p>Climate change is driven mainly by greenhouse gases such as carbon dioxide (CO₂) released when we burn
            fossil fuels. These emissions don't only come from factories and power plants: household consumption —
            the electricity we use, the way we travel, and the things we buy and throw away — accounts for more
            than 60% of global greenhouse gas emissions.</p>
        <p>Even so, most people have no idea how big their own footprint is. Electricity bills show kWh and fuel
            receipts show litres, but rarely what that means for the climate. Without a number to look at, it is
            hard to know whether our habits are fine or need to change, and which change would matter most.</p>
        <p>Tools like the US EPA Carbon Footprint Calculator help, but they use US units and assumptions. CarbonCheck
            was built as a college project to offer a simple, metric version: answer a few questions, see your yearly
            footprint, find out whether it is low or high, and get concrete steps to reduce it.</p>
        <h3>Goals</h3>
        <ul>
            <li>Help households estimate their yearly CO₂ emissions in a few minutes.</li>
            <li>Show clearly whether the result is fine or too high, compared to global targets and averages.</li>
            <li>Give practical recommendations with the amount of CO₂ each change would save.</li>
            <li>Let people track their results over time to see progress.</li>
        </ul>
    </section>

    <section id="how-it-works" class="prose">
        <h2>How it works</h2>
        <ol class="flow">
            <li><strong>Choose categories</strong> — household energy, transport, and/or shopping &amp; waste.
                You can do one at a time.</li>
            <li><strong>Fill in your details</strong> — monthly electricity and gas, weekly travel distances,
                flights per year, things you buy and what you recycle. Empty fields count as zero.</li>
            <li><strong>See your results</strong> — your household's yearly total, the amount per person,
                a breakdown per category, and a rating from Low to Very high.</li>
            <li><strong>Get recommendations</strong> — personal tips, sorted by how much CO₂ they would save.</li>
            <li><strong>Check your history</strong> — every result is saved so you can compare later,
                or start over with new numbers.</li>
        </ol>
    </section>

    <section id="calculation" class="prose">
        <h2>How we calculate</h2>
        <p>Each answer is converted to a yearly amount (monthly × 12, weekly × 52) and multiplied by an
            <em>emission factor</em> — the kilograms of CO₂-equivalent (CO₂e) released per unit:</p>
        <p class="formula">emissions (kg CO₂e / year) = amount per year × emission factor</p>

        <div class="card table-wrap">
            <table>
                <thead><tr><th>Activity</th><th>Emission factor</th></tr></thead>
                <tbody>
                    <tr><td>Electricity</td><td>{{ $c::ELECTRICITY_KWH }} kg per kWh</td></tr>
                    <tr><td>Natural gas</td><td>{{ $c::NATURAL_GAS_M3 }} kg per m³</td></tr>
                    <tr><td>LPG</td><td>{{ $c::LPG_KG }} kg per kg</td></tr>
                    <tr><td>Car — petrol / diesel</td><td>{{ $c::FUEL_LITRE['petrol'] }} / {{ $c::FUEL_LITRE['diesel'] }} kg per litre (+5% for other gases)</td></tr>
                    <tr><td>Motorbike</td><td>{{ $c::MOTORBIKE_KM }} kg per km</td></tr>
                    <tr><td>Bus / train</td><td>{{ $c::BUS_KM }} / {{ $c::TRAIN_KM }} kg per km</td></tr>
                    <tr><td>Flying</td><td>{{ $c::FLIGHT_HOUR }} kg per hour</td></tr>
                    <tr><td>New clothing item</td><td>{{ $c::CLOTHING_ITEM }} kg each</td></tr>
                    <tr><td>New electronic device</td><td>{{ $c::ELECTRONIC_DEVICE }} kg each</td></tr>
                    <tr><td>Household waste</td><td>{{ $c::WASTE_PER_PERSON }} kg per person per year, minus what you recycle</td></tr>
                </tbody>
            </table>
        </div>
        <p>Car fuel use is your weekly distance divided by your car's km per litre. Electricity from renewables
            is subtracted from your electricity emissions. Finally, the household total is divided by the number
            of people living in the home to get the amount per person.</p>
    </section>

    <section id="rating" class="prose">
        <h2>Rating scale</h2>
        <p>The rating uses your emissions <strong>per person per year</strong>, so big and small households
            can be compared fairly.</p>
        <div class="card"><x-rating-scale /></div>
        <ul>
            @foreach ($c::RATINGS as $r)
                <li><strong>{{ $r['label'] }}</strong> — {{ $r['text'] }}</li>
            @endforeach
        </ul>
        <p>The 2 t line is roughly the yearly footprint per person needed by 2030 to keep global warming
            within the Paris Agreement limits. The world average is about {{ $c::WORLD_AVERAGE_PER_PERSON / 1000 }} t.</p>
    </section>

    <section id="limits" class="prose">
        <h2>Privacy &amp; limitations</h2>
        <ul>
            <li>No account is needed. Your results are linked to a random ID stored in this browser,
                so only you can see them. Clearing your cookies starts a new history.</li>
            <li>Results are <strong>estimates</strong> based on average factors. Real emissions depend on your
                country's electricity grid, your vehicle, and how products were made.</li>
            <li>Only the categories you choose are counted, so a single category gives a lower total
                than your full footprint. Food and diet are not included.</li>
        </ul>
        <p class="muted">Sources: IEA (grid electricity), UK DEFRA 2023 conversion factors (fuels, transport),
            US EPA Carbon Footprint Calculator (vehicle and waste assumptions).</p>
    </section>

    <div class="actions">
        <a href="{{ route('calculator.choose') }}" class="btn">Start calculating</a>
    </div>
@endsection
