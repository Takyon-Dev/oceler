@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
  <script>
    // Stores the user ID as vars accessible by all other javascript files
    var user_id = "<?php echo Auth::user()->id;?>";
    var user_name = "<?php echo Auth::user()->player_name;?>";

    // Adds csrf token to AJAX headers
    $.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });
  </script>
	<script type="text/javascript" src="{{ asset('js/solutions.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/messages.js') }}"></script>
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
