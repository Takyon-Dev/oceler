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
        You've reached the end of this portion of the experiment.
      </h1>
      <h2 class="text-center">
        You answered {{ $num_correct }} items correctly, so you earned ${{ number_format($amt_earned, 2) }}.
      </h2>
      <div class="text-center">
      @if($curr_round < count($trial->rounds))
        <a href="/player/trial/new-round" role="button" class="btn btn-primary btn-lg">Continue</a>
      @elseif($group->survey_url)
        <a href="{{ $group->survey_url }}" role="button" class="btn btn-primary btn-lg">Continue</a>
      @else
        <a href="/player/trial/end" role="button" class="btn btn-primary btn-lg">Continue</a>
      @endif
      </div>
    </div>
  </div>
</div>
@stop
