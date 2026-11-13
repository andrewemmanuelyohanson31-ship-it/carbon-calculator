@props(['kg' => null]) {{-- kg per person per year; null = scale only, no marker --}}

@php
    $ratings = \App\Services\CarbonCalculator::RATINGS;
    $gaugeMax = \App\Services\CarbonCalculator::GAUGE_MAX;
    $start = 0;
@endphp

<div class="gauge" role="img"
     aria-label="Rating scale from Low (under 2 t) to Very high (over 10 t){{ $kg !== null ? ', your result is '.number_format($kg / 1000, 2).' t per person' : '' }}">
    @foreach ($ratings as $r)
        @php $end = min($r['max'], $gaugeMax); @endphp
        <div class="gauge-seg level-{{ $r['level'] }}" style="width: {{ ($end - $start) / $gaugeMax * 100 }}%"></div>
        @php $start = $end; @endphp
    @endforeach
    @if ($kg !== null)
        <div class="gauge-marker" style="left: {{ min($kg / $gaugeMax, 1) * 100 }}%"><span>You</span></div>
    @endif
</div>

<ul class="gauge-legend">
    @php $start = 0; @endphp
    @foreach ($ratings as $r)
        <li>
            <i class="level-{{ $r['level'] }}"></i>
            <strong>{{ $r['label'] }}</strong>
            @if ($r['max'] >= $gaugeMax) over {{ $start / 1000 }} t
            @else {{ $start / 1000 }}–{{ $r['max'] / 1000 }} t
            @endif
        </li>
        @php $start = $r['max']; @endphp
    @endforeach
</ul>
