@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
  <script>

    // Stores the user ID as vars accessible by all other javascript files
    var user_id = "<?php echo Auth::user()->id;?>";
    var user_name = "<?php echo Auth::user()->player_name;?>";

    $(document).ready(function(){

      // Adds csrf token to AJAX headers
      $.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });

      // Prevents ENTER key from submitting a form
      $(window).keydown(function(event){
        if(event.keyCode == 13) {
          event.preventDefault();
          return false;
        }
      });

      solutionListener(last_solution);
      messageListener(last_message_time);
    });

  </script>
	<script type="text/javascript" src="{{ asset('js/solutions.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/messages.js') }}"></script>
  <script type="text/javascript" src="{{ asset('js/search.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/listen.js') }}"></script>
  <script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>

@stop


@section('content')
    <div class="container full-width">
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
