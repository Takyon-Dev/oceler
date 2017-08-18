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
          <table class="table">
            @foreach($stats as $s)
              <tr class="success">
                <th>Trial Name</th>
                <th>Factoidset</th>
                <th>Base Pay</th>
                <th>Num Players</th>
                <th>Start Time</th>
                <th>Trial Time</th>
                <th></th>
              </tr>
              <tr>
                <td>{{ $s['trial']['name'] }}</td>
                <td>
                  @foreach($s['trial']['factoidset'] as $fact)
                    {{ $fact }}
                  @endforeach
                </td>
                <td>{{ $s['trial']['base_pay'] }}</td>
                <td>{{ $s['trial']['num_players'] }}</td>
                <td>{{ $s['trial']['start_time'] }}</td>
                <td>{{ $s['trial']['total_time'] }} mins.</td>
                <td></td>
              </tr>
              @if(array_key_exists('users', $s))
                <tr>
                  <th colspan="7">Player data for this trial:</th>
                </tr>
                <tr class="warning">
                  <th>Worker ID</th>
                  <th>User Agent</th>
                  <th>IP</th>
                  <th>Last Ping</th>
                  <th>Time</th>
                  <th>Num Correct</th>
                  <th>Bonus</th>
                </tr>
                @foreach($s['users'] as $player)
                  <tr class="table-striped">
                    <td>{{ $player['worker_id'] }}</td>
                    <td>{{ $player['user_agent'] }}</td>
                    <td>{{ $player['ip_address'] }}</td>
                    <td>{{ $player['last_ping'] }}</td>
                    <td>{{ $player['player_time'] }} mins.</td>
                    <td>{{ $player['num_correct'] }} / {{ $player['tot_categories'] }}</td>
                    <td>{{ $player['bonus'] }}</td>
                  </tr>
                @endforeach
              @endif
            @endforeach
          </table>
        </div>
      </div>
    </div>
@stop
