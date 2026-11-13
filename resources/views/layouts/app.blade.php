<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Household Carbon Calculator') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <a href="{{ route('home') }}" class="logo">Carbon<span>Check</span></a>
            <nav>
                <a href="{{ route('calculator.choose') }}" @class(['active' => request()->routeIs('calculator.*')])>Calculator</a>
                <a href="{{ route('history') }}" @class(['active' => request()->routeIs('history')])>History</a>
            </nav>
        </div>
    </header>

    <main class="container">
        @isset($step)
            <ol class="steps" aria-label="Progress">
                @foreach (['Choose', 'Details', 'Results', 'Tips'] as $i => $name)
                    <li @class(['done' => $i + 1 < $step, 'current' => $i + 1 === $step])>{{ $name }}</li>
                @endforeach
            </ol>
        @endisset

        @yield('content')
    </main>

    <footer class="container footer">
        Estimates use average emission factors and are for learning purposes only.
    </footer>
</body>
</html>
