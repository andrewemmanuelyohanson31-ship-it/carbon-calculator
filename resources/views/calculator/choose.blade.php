@extends('layouts.app', ['step' => 1])

@section('title', 'Choose categories')

@section('content')
    <h1>What do you want to measure?</h1>
    <p class="lead">Choose one or more categories. You can always come back and do the others.</p>

    <form method="GET" action="{{ route('calculator.form') }}">
        @error('categories') <p class="error">{{ $message }}</p> @enderror

        <div class="grid-3">
            @foreach ($categories as $key => $label)
                <label class="card choice">
                    <input type="checkbox" name="categories[]" value="{{ $key }}" @checked(in_array($key, old('categories', [])))>
                    <strong>{{ $label }}</strong>
                    <span>{{ ['household' => 'Electricity, gas and LPG', 'transport' => 'Car, motorbike, public transport, flights', 'others' => 'Clothes, electronics, recycling'][$key] }}</span>
                </label>
            @endforeach
        </div>

        <button type="submit" class="btn">Continue</button>
    </form>
@endsection
