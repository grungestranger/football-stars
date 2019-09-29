@extends('team')

@section('match_content')
    {{--    @if (0 && $isMatch)--}}
    {{--        @if ($actions)--}}
    {{--            <script>--}}
    {{--                var actions = {!! $actions !!};--}}
    {{--            </script>--}}
    {{--        @endif--}}
    {{--        <div id="matchField">--}}
    {{--            @if ($time < 0)--}}
    {{--                <div id="matchLoader"><img src="/img/loader.gif"><span>{{ abs($time) }}</span></div>--}}
    {{--            @endif--}}
    {{--            <span data-id="0"></span>--}}
    {{--            @foreach ($teams as $team)--}}
    {{--                @foreach ($team->players as $player)--}}
    {{--                    <span data-id="{{ $player->id }}">{{ $player->id }}</span>--}}
    {{--                @endforeach--}}
    {{--            @endforeach--}}
    {{--        </div>--}}
    {{--    @endif--}}
@endsection

@section('match_css')

@endsection

@section('match_js')

@endsection
