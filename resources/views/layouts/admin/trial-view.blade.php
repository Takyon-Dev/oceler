@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('js')
  <script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>
  <script type="text/javascript" src="{{ asset('js/listen.js') }}"></script>

  <script>

    var TimerVars = {};

    $(document).ready(function(){


      var server_time = "{{ $curr_server_time }}";
      TimerVars.curr_round = "{{ $trial->curr_round }}";
      TimerVars.total_rounds = "{{ count($trial->rounds) }}";
      var start_time = "{{ $start_time }}";

      @if($trial->curr_round > 0)
        var timeout = "{{ $trial->rounds[$trial->curr_round -1]->round_timeout }}";
        var time = 0;
        addTimer(server_time, start_time, timeout);
        adminTimerTick();
      @else
        $("#timer").html(start_time);
      @endif

      playerTrialListener({{ $trial->id }})

    });

  </script>
@stop


@section('content')
    <div class="container">
      @include('layouts.admin.menu')
      <div class="row">
        <div class="col-md-12">
          <h2 class="text-primary"><span class="text-muted">Trial:</span> {{ $trial->name }} ::
            <span class="text-muted">Time:</span>
            <span id="timer" class="text-primary"></span>
          </h2>
          <table id="trials" class="table table-striped trials">
            <tr>
              <th>Node</th>
              <th>Name</th>
              <th>Email</th>
              <th>IP</th>
              <th>User Agent</th>
              <th>Time Entered</th>
              <th>Last Ping</th>
            </tr>
            <tbody class="players">
              <tr><td colspan="7" class="text-center">Loading player data...</td><tr>
            </tbody>
          </table>
          </div>
        </div>
    </div>
@stop
