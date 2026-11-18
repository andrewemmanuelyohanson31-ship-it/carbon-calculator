@extends('layouts.app')

@section('title', 'History')

@section('content')
    <h1>Calculation history</h1>
    <p class="lead">Your past results are saved in this browser only — nobody else can see them.</p>

    @if ($calculations->isEmpty())
        <div class="card empty">
            <p>No calculations yet.</p>
            <a href="{{ route('calculator.choose') }}" class="btn">Start your first one</a>
        </div>
    @else
        <div class="card table-wrap history-table">
            <table>
                <thead>
                    <tr><th>Date</th><th>Categories</th><th>Total</th><th>Per person</th><th>Rating</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($calculations as $calc)
                        <tr>
                            <td data-label="Date">{{ $calc->created_at->format('d M Y, H:i') }}</td>
                            <td data-label="Categories">{{ collect(array_keys($calc->breakdown))->map(fn ($c) => $labels[$c])->join(', ') }}</td>
                            <td data-label="Total">{{ number_format($calc->total_kg / 1000, 2) }} t</td>
                            <td data-label="Per person">{{ number_format($calc->total_kg / $calc->household_size / 1000, 2) }} t</td>
                            @php $rating = $calculator->rating($calc->total_kg / $calc->household_size); @endphp
                            <td data-label="Rating"><span class="badge level-{{ $rating['level'] }}">{{ $rating['label'] }}</span></td>
                            <td><a href="{{ route('results', $calc) }}">View result</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $calculations->links('pagination::simple-default') }}
        <div class="actions">
            <a href="{{ route('calculator.choose') }}" class="btn">New calculation</a>
        </div>
    @endif
@endsection
