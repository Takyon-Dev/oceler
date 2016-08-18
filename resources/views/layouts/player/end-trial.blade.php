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
        [ OPTIONAL -- END OF EXPERIMENT MESSAGE HERE ]<br>
        The experiment is now over. Thank you for your participation.
      </h1>

    </div>
  </div>
</div>
@stop
