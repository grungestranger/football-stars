@extends('layouts.app')

@section('content')
    <div id="users"></div>
@endsection

@section('css')
    <link href="{{ mix('css/main.css') }}" rel="stylesheet">
@endsection

@section('js')
    <script src="{{ mix('js/main.js') }}"></script>
@endsection
