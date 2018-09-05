@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('js')
  <script type="text/javascript" src="{{ asset('js/listen.js') }}"></script>
  <script>

    $(document).ready(function(){

      queueListener();
      playerTrialListener();

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
            <button type="button" class="btn btn-sm btn-danger pull-right"
                    style="margin-left: 8px;"
                    data-toggle="modal" data-target="#warning">
              Stop all trials
            </button>
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
                <tr><td colspan="8" class="text-center">Loading player data...</td><tr>
              </tbody>
            </table>
        </div>
      </div>
    </div>

    <!-- Stop Trials Modal -->
    <div class="modal fade" id="warning" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger" id="exampleModalLabel">Stop All Trials</h5>
          </div>
          <div class="modal-body">
            <strong>Warning:</strong> This will immediately stop and deactivate
            <strong><em>all</em></strong> active trials.<br>
            Any players in a trial will be removed.
          </div>
          <div class="modal-footer">
            <form action="/admin/stop-all-trials" method="post">
              {{ csrf_field() }}
              <button type="button" class="btn btn-secondary mr-lg-2" data-dismiss="modal">Cancel</button>
              <button class="btn btn-danger float-right" type="submit">Stop Trials</button>
            </form>
          </div>
        </div>
      </div>
    </div>
@stop
