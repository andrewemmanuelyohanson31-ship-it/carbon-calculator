@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <section class="hero">
        <p class="eyebrow">Household carbon footprint calculator</p>
        <h1>How much CO₂ does your home produce?</h1>
        <p class="lead">Answer a few questions about your energy use, travel and shopping habits.
            In about two minutes you'll see your yearly emissions and simple ways to cut them.</p>
        <a href="{{ route('calculator.choose') }}" class="btn">Start calculating</a>
    </section>

    <section class="grid-3">
        <div class="card">
            <h3>1. Choose</h3>
            <p>Pick what to measure: household energy, transport, or shopping &amp; waste.</p>
        </div>
        <div class="card">
            <h3>2. Calculate</h3>
            <p>See your yearly footprint, broken down by category and compared to the world average.</p>
        </div>
        <div class="card">
            <h3>3. Improve</h3>
            <p>Get personal recommendations with the CO₂ each change would save.</p>
        </div>
    </section>
@endsection
