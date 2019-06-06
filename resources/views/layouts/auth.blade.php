<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/bootstrap.js') }}"></script>

    <!-- Styles -->
    <link href="{{ mix('css/auth.css') }}" rel="stylesheet">
</head>
<body>
    <a href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>

    @guest
        @if (Route::has('register'))
            <a href="{{ route('register') }}">{{ __('Register') }}</a>
        @endif
    @else
        {{ Auth::user()->name }}
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <input type="submit" value="{{ __('Logout') }}">
        </form>
    @endguest

    @yield('content')
</body>
</html>
