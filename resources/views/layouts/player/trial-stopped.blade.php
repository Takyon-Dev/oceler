@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
  <script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>
  <script>
    deleteCookie('OcelerTime');
  </script>
@stop

@section('content')
<div class="container">
  @include('layouts.player.menu')
  <div class="row">
    <div class="col-md-12">

      <h1 class="text-primary text-center">
        Oops... something went wrong!
      </h1>
      <h3 class="text-center">
        Click "Try Again" to join a new trial or click "End Task" to finish
        this task and process your payment.
      </h3>
      <div class="text-center">
        <a href="/player/trial/queue" role="button" class="btn btn-primary btn-lg">Try Again</a>
        <a href="/player/end-task" role="button" class="btn btn-primary btn-lg">End Task</a>
      </div>
    </div>
  </div>
</div>
@stop
