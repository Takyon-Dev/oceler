@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
  <script src="{{ URL::asset('js/genericTimer.js') }}"></script>
  <script type="text/javascript" src="{{ asset('js/queue.js') }}"></script>

@stop

@section('content')

<script>

  // Stores the user ID as vars accessible by all other javascript files
  var user_id = "<?php echo Auth::user()->id;?>";
  var trial_id = "<?php echo $trial->id;?>";

  $(document).ready(function(){

    initializeTimer(240, function() {
      window.location.replace("/player/end-task/timeout");
    });

    waitForInstructions(trial_id);

    // Adds csrf token to AJAX headers
    $.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });

    $("#instr_button").click(function(){

      $("#waiting").show();
      $("#instructions").hide();
      markAsRead(user_id);
      $("#timer-prompt").hide();
      //waitForInstructions(trial_id);

    });

    $("#instructions").show();
    $("#waiting").hide();

  });

</script>

<div class="container">
  <div class="row">
    <div class="col-md-12 text-center">
      <h2 class="text-primary">
        <span id="timer-prompt">Please press start before time elapses</span>
        <div class="ml-md-4 text-danger" id="timer"></div>
      </h2>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div id="instructions">
        <div id="instructions_content" class="text-center">
          @if($trial->instr_img_path != null)
            <img class="img-responsive" src="{{$trial->instr_img_path}}">
          @endif
          @if($trial->instructions != null)
            {!! $trial->instructions !!}
          @endif
        </div>
        <div class="text-center">
          <a class="btn btn-lg btn-danger" id="instr_button" href="#" role="button">Start</a>
        </div>
      </div>
      <div id="waiting" class="text-center">
        <h1 class="text-primary">Please wait for all players to finish reading the instructions...</h1>
        <img src="/img/waiting_1.gif" class="waiting-sm">
      </div>
    </div>
  </div>
</div>
@stop
