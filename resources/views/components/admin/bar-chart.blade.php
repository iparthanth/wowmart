@props(['points', 'label', 'currency' => '৳'])

@php
    // Geometry in SVG user units; the viewBox scales it to any width.
    $W = 640; $H = 224;
    $padL = 52; $padR = 8; $padT = 26; $padB = 28;
    $plotW = $W - $padL - $padR;
    $plotH = $H - $padT - $padB;
    $base = $padT + $plotH;

    $values = array_column($points, 'total');
    $n = count($values);
    $max = $n ? max($values) : 0;

    // A clean axis: four intervals, each a 1/2/5 x 10^k step.
    if ($max <= 0) {
        $step = 100;
    } else {
        $raw = $max / 4;
        $mag = 10 ** floor(log10($raw));
        $norm = $raw / $mag;
        $step = ($norm <= 1 ? 1 : ($norm <= 2 ? 2 : ($norm <= 5 ? 5 : 10))) * $mag;
    }
    $top = $max > 0 ? ceil($max / $step) * $step : $step * 4;
    $ticks = [];
    for ($t = 0; $t <= $top + 1e-9; $t += $step) { $ticks[] = $t; }

    $slot = $plotW / max($n, 1);
    $bw = min(24, $slot - 2);                              // capped width; the rest of the slot is air
    $peak = $max > 0 ? array_search($max, $values, true) : null;
    $yFor = fn (float $v) => $base - ($v / $top * $plotH);
    $fmt = fn (float $v) => number_format($v, $step < 1 ? 2 : 0);
    $money = fn (float $v) => $currency.number_format($v, 2);

    $summary = $max > 0
        ? sprintf('%s per day. Highest %s on %s.', $label, $money($max), $points[$peak]['date']->format('j M'))
        : sprintf('%s per day. No orders in this period.', $label);
@endphp

<figure class="chart">
    <svg viewBox="0 0 {{ $W }} {{ $H }}" role="img" aria-label="{{ $summary }}">
        <g class="grid" aria-hidden="true">
            @foreach ($ticks as $t)
                @php $ty = round($yFor($t), 2); @endphp
                <line x1="{{ $padL }}" x2="{{ $W - $padR }}" y1="{{ $ty }}" y2="{{ $ty }}"/>
                <text class="tick" x="{{ $padL - 8 }}" y="{{ $ty + 4 }}" text-anchor="end">{{ $fmt($t) }}</text>
            @endforeach
        </g>

        @foreach ($points as $i => $p)
            @php
                $x0 = round($padL + $i * $slot + ($slot - $bw) / 2, 2);
                $h = $p['total'] / $top * $plotH;
            @endphp
            <g class="day">
                <title>{{ $p['date']->format('D j M') }}: {{ $money($p['total']) }}</title>
                {{-- Hit target spans the whole column, bigger than the bar itself. --}}
                <rect class="hit" x="{{ round($padL + $i * $slot, 2) }}" y="{{ $padT }}" width="{{ round($slot, 2) }}" height="{{ $plotH }}"/>
                @if ($h > 0)
                    @php
                        $yT = round($base - $h, 2);
                        $r = round(min(4, $h, $bw / 2), 2);           // rounded data-end, square at the baseline
                        $x1 = round($x0 + $bw, 2);
                    @endphp
                    <path class="bar" d="M{{ $x0 }},{{ $base }}V{{ $yT + $r }}Q{{ $x0 }},{{ $yT }} {{ $x0 + $r }},{{ $yT }}H{{ $x1 - $r }}Q{{ $x1 }},{{ $yT }} {{ $x1 }},{{ $yT + $r }}V{{ $base }}Z"/>
                @endif
                @if ($i === $peak)
                    <text class="peak" x="{{ round($x0 + $bw / 2, 2) }}" y="{{ round($base - $h - 8, 2) }}" text-anchor="middle">{{ $fmt($p['total']) }}</text>
                @endif
                @if ($i % 2 === ($n - 1) % 2)
                    <text class="tick" x="{{ round($x0 + $bw / 2, 2) }}" y="{{ $H - 8 }}" text-anchor="middle">{{ $p['date']->format('j M') }}</text>
                @endif
            </g>
        @endforeach
    </svg>

    <details class="chart-table">
        <summary>View as table</summary>
        <table class="table">
            <thead><tr><th>Day</th><th class="right">{{ $label }}</th></tr></thead>
            <tbody>
            @foreach (array_reverse($points) as $p)
                <tr><td>{{ $p['date']->format('D j M') }}</td><td class="right num">{{ $money($p['total']) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </details>
</figure>
