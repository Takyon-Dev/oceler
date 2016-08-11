@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  @include('layouts.player.menu')
  <div class="row">
    <div class="col-md-12">

      <h1 class="text-primary">
        You've reached the end of this portion of the experiment.
      </h1>
      <h2 class="text-center">
        You answered {{ $num_correct }} items correctly, so you earned ${{ number_format($amt_earned, 2) }}.
      </h2>
      @if($curr_round < count($trial->rounds))
        <div class="text-center">
          <a href="/player/trial/new-round" role="button" class="btn btn-primary btn-lg">Continue</a>
        </div>
      @endif
    </div>
  </div>
</div>
@stop
