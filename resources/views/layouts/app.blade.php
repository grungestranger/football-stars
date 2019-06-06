<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- JWT -->
    <meta name="jwt" content="{{ JWTAuth::fromUser(auth()->user()) }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ url('//' . Request::server('HTTP_HOST') . ':' . env('SOCKET_IO_PORT') . '/socket.io/socket.io.js') }}"></script>
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('js')

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @yield('css')
</head>
<body>
    <a href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>

    {{ Auth::user()->name }}
    <form id="logout-form" action="{{ route('logout') }}" method="POST">
        @csrf
        <input type="submit" value="{{ __('Logout') }}">
    </form>

    <div id="fromChallenges">
    </div>
    <div id="toChallenges">
    </div>

    @yield('content')

    <div id="stdElements">
        <div data-id="" class="user">
            <span class="name"></span>
            <span class="status"></span>
            <a class="play" href="#">Играть</a>
            <a class="create-challenge" href="#">Предложить матч</a>
            <a class="remove-challenge" href="#">Удалить</a>
        </div>
    </div>
</body>
</html>
