@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
	<script type="text/javascript" src="{{ asset('js/solutions.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/listen.js') }}"></script>
@stop


@section('content')
    <div class="container">
        <div class="row">
            @include('layouts.player.timer')
        </div>    
        <div class="row">
            @include('layouts.player.solutions')
        </div>
        <div class="row">
            @include('layouts.player.messages')
            @include('layouts.player.search')
        </div>
    </div>
@stop