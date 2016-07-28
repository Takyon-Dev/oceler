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
          <h2 class="text-primary">Trial {{ $trial->id }} :: Trial Time: 00:00</h2>
          <table class="table table-striped trials">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>IP Address</th>
              <th>Time Entered</th>
              <th>Last Ping</th>
              <th>Solutions</th>
            </tr>
            @foreach($players as $player)
            <tr>
              <td>{{ $player->name }}</td>
              <td>{{ $player->email }}</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            @endforeach
          </table>
          </div>
        </div>
    </div>
@stop
