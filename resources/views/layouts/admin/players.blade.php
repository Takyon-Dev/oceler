@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('js')
  <script>

    $(document).ready(function(){

      // Adds csrf token to AJAX headers
      $.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });
    });

  </script>

@stop


@section('content')
    <div class="container">
      @include('layouts.admin.menu')
      <div class="row">
        <div class="col-md-12">
          <h3>[ This page will track in real time the users waiting to join a trial
            as well as the users that are in trials in progress.
            Currently, you will need to reload the page to see any changes.]</h3>
          <h1 class="text-center">Players</h1>
          <h2 class="text-primary">Players in trial queue</h2>
          <table class="table table-striped trials">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>IP Address</th>
              <th>Time Entered</th>
              <th>Last Pinged</th>
            </tr>
            @foreach($queued_players as $queue)
            <tr>
              <td>{{ $queue->users->name }}</td>
              <td>{{ $queue->users->email }}</td>
              <td></td>
              <td>{{ $queue->created_at }}</td>
              <td>{{ $queue->updated_at }}</td>
            </tr>
            @endforeach
          </table>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <h2 class="text-primary">Players in active trials</h2>
            <table class="table table-striped trials">
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>IP Address</th>
                <th>Time Entered</th>
                <th>Solutions</th>
              </tr>
              <tr><td colspan="5">[Needs to be added]</td></tr>
            </table>  
        </div>
      </div>
    </div>
@stop
