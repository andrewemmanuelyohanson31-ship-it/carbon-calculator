@extends('layouts.app', ['step' => 3])

@section('title', 'Your results')

@php
    $perPerson = $calc->total_kg / $calc->household_size;
    $vsAverage = $perPerson / $worldAverage * 100;
@endphp

@section('content')
    <h1>Your household's carbon footprint</h1>

    <div class="grid-2">
        <div class="card stat">
            <span>Total per year</span>
            <strong>{{ number_format($calc->total_kg / 1000, 2) }} t</strong>
            <small>{{ number_format($calc->total_kg) }} kg CO₂e</small>
        </div>
        <div class="card stat">
            <span>Per person ({{ $calc->household_size }} {{ Str::plural('person', $calc->household_size) }})</span>
            <strong>{{ number_format($perPerson / 1000, 2) }} t</strong>
            <small>{{ number_format($vsAverage) }}% of the world average ({{ $worldAverage / 1000 }} t)</small>
        </div>
    </div>

    <section class="card">
        <h2>Breakdown</h2>
        @foreach ($calc->breakdown as $category => $kg)
            @php $share = $calc->total_kg > 0 ? $kg / $calc->total_kg * 100 : 0; @endphp
            <div class="bar-row">
                <div class="bar-label">
                    <span>{{ $labels[$category] }}</span>
                    <span>{{ number_format($kg) }} kg · {{ round($share) }}%</span>
                </div>
                <div class="bar"><div style="width: {{ $share }}%"></div></div>
            </div>
        @endforeach
        <p class="muted">Only the categories you chose are included. Calculated {{ $calc->created_at->format('d M Y, H:i') }}.</p>
    </section>

    <div class="actions">
        <a href="{{ route('calculator.choose') }}" class="btn btn-ghost">Start over</a>
        <a href="{{ route('recommendations', $calc) }}" class="btn">See how to reduce it</a>
    </div>
@endsection
