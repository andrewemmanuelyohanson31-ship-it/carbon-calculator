@extends('layouts.app', ['step' => 4])

@section('title', 'Recommendations')

@php $totalSaving = array_sum(array_column($tips, 'saving')); @endphp

@section('content')
    <h1>Ways to reduce your footprint</h1>

    @if ($tips)
        <p class="lead">Doing all of these could save about
            <strong>{{ number_format($totalSaving) }} kg CO₂e a year</strong>,
            lowering your total from {{ number_format($calc->total_kg / 1000, 2) }} t
            to {{ number_format(max(0, $calc->total_kg - $totalSaving) / 1000, 2) }} t.</p>

        <ol class="tips">
            @foreach ($tips as $tip)
                <li class="card tip">
                    <div>
                        <h3>{{ $tip['title'] }}</h3>
                        <p>{{ $tip['text'] }}</p>
                    </div>
                    <span class="saving">−{{ number_format($tip['saving']) }} kg/yr</span>
                </li>
            @endforeach
        </ol>
    @else
        <p class="lead">Nice work — we couldn't find any easy savings in the categories you chose.
            Try another category to get more tips.</p>
    @endif

    <div class="actions">
        <a href="{{ route('results', $calc) }}" class="btn btn-ghost">Back to results</a>
        <a href="{{ route('history') }}" class="btn btn-ghost">View history</a>
        <a href="{{ route('calculator.choose') }}" class="btn">Start over</a>
    </div>
@endsection
