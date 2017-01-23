@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('js')
  <script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>
  <script type="text/javascript" src="{{ asset('js/listen.js') }}"></script>

  <script>

    $(document).ready(function(){

      var trial_id = "{{ $trial->id }}";
      var server_time = "{{ $curr_server_time }}";
      var curr_round = "{{ $trial->curr_round }}";
      var start_time = "{{ $trial_start_time }}";
      var timeout = "{{ $timeout }}";
      addAdminTimer(server_time, start_time, timeout, curr_round, trial_id);
      adminTimerTick(trial_id);


      setInterval(function(){
        playerTrialListener({{ $trial->id }});
      }, 5000);

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
