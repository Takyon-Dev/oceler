@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
  <script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>
@stop

@section('content')
<div class="container">
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
        <script>
          countdown(10);

          function countdown(i)
          {
            $("#countdown").html(i);
            if(i == 0) window.location.replace('/player/trial/new-round');
            else{
              i--;
              setTimeout(function(){
                countdown(i);
              }, 1000);
            }
          }
        </script>

        <h1>Next round starting in</h1>
        <h1 id="countdown" class="text-primary huge"></h1>
      @else
        <a href="/player/trial/end" role="button" class="btn btn-primary btn-lg">Continue</a>
      @endif
      </div>
    </div>
  </div>
</div>
@stop
