@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
  <script>

    // Stores the user ID as vars accessible by all other javascript files
    var user_id = "<?php echo Auth::user()->id;?>";

    $(document).ready(function(){

      countdown(5);


      // Adds csrf token to AJAX headers
      $.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });

    });

    function countdown(i)
    {
      $("#countdown").html(i);
      if(i == 0) window.location.replace('/player/trial');
      else{
        i--;
        setTimeout(function(){
          countdown(i);
        }, 1000);
      }
    }

  </script>
  <script type="text/javascript" src="{{ asset('js/queue.js') }}"></script>

@stop

@section('content')
<div class="container">
  @include('layouts.player.menu')
  <div class="row">
    <div class="col-md-12">
      <div class="text-center">
        <h1>Game starting in</h1>
        <h1 id="countdown" class="text-primary huge"></h1>
      </div>
    </div>
  </div>
</div>
@stop
