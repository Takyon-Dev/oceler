@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
  <script>

    // Stores the user ID as vars accessible by all other javascript files
    var user_id = "<?php echo Auth::user()->id;?>";

    $(document).ready(function(){

      // Adds csrf token to AJAX headers
      $.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });

      queue();
    });

  </script>
  <script type="text/javascript" src="{{ asset('js/queue.js') }}"></script>

@stop

@section('content')
<div class="container">
  @include('layouts.player.menu')
  <div class="row">
    <div class="col-md-12">
      <div id="queue_content" class="text-center">
        <h1 class="text-primary">Please wait for all players to join...</h1>
        <h2 class="text-muted collapse" id="players_needed">Waiting for <span class="text-primary"></span> more players</h2>
        <img src="/img/waiting_1.gif" class="waiting-sm">
      </div>
    </div>
  </div>
</div>
@stop
