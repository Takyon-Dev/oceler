@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('js')
  <script type="text/javascript" src="{{ asset('js/listen.js') }}"></script>
  <script>

    $(document).ready(function(){

      setInterval(function(){
        queueListener();
      }, 2000);

      setInterval(function(){
        playerTrialListener();
      }, 2000);

    });

  </script>

@stop


@section('content')
    <div class="container">
      @include('layouts.admin.menu')
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center">Players</h1>
          <h2 class="text-primary">
            Players in trial queue
            <a class="btn btn-primary btn-sm pull-right" role="button"
               href="/manage-queue" target="_blank">
               Manually process queue
            </a>
          </h2>
          <table id="queued_players" class="table table-striped trials">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>IP Address</th>
              <th>User Agent</th>
              <th>Time Entered</th>
              <th>Last Pinged</th>
            </tr>
            <tbody class="players">
              <tr><td colspan="8" class="text-center">Loading player data...</td><tr>
            </tbody>
          </table>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h2 class="text-primary">Players in active trials</h2>
            <table id="trials" class="table table-striped trials">
              <tr>
                <th>Trial ID</th>
                <th>Node</th>
                <th>Name</th>
                <th>Email</th>
                <th>IP</th>
                <th>User Agent</th>
                <th>Time Entered</th>
                <th>Last Pinged</th>
              </tr>
              <tbody class="players">
                <tr><td colspan="7" class="text-center">Loading player data...</td><tr>
              </tbody>
            </table>
        </div>
      </div>
    </div>
@stop
