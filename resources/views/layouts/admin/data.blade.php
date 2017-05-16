@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('content')
    <div class="container">
      @include('layouts.admin.menu')
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center">Data</h1>
          <table class="table table-striped trials">
            @foreach($stats as $s)
              <tr>
                <th>Trial: {{ $s['trial']['name'] }}</th>
                <th>Factoidset: {{ $s['trial']['factoidset'] }}</th>
                <th>Num players: {{ $s['trial']['num_players'] }}</th>
                <th>Start time: {{ $s['trial']['start_time'] }}</th>
                <th>Trial time: {{ $s['trial']['total_time'] }} mins.</th>
              </tr>
              <tr>
                <th>Player data for this trial:</th>
              </tr>
              <tr>
                <table>
                  <tr>
                    <th>Worker ID</th>
                    <th>User Agent</th>
                    <th>IP</th>
                    <th>Last Ping</th>
                    <th>Time</th>
                    <th>Earnings<th>
                  </tr>
                  @foreach($s['users'] as $player)
                    <td>{{ $player['worker_id'] }}</td>
                    <td>{{ $player['user_agent'] }}</td>
                    <td>{{ $player['ip_address'] }}</td>
                    <td>{{ $player['last_ping'] }}</td>
                </table>
              </tr>
            @endforeach
          </table>
        </div>
      </div>
    </div>
@stop
