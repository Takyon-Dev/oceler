@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('content')
    <div class="container">
      @include('layouts.admin.menu')
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center">Log files</h1>
          <table class="table table-striped">
            <tr>
              <th>ID</th>
              <th>Trial Name</th>
              <th>Date</th>
              <th></th>
              <th></th>
            </tr>
            @foreach($logs as $log)
              <tr>
                <td>{{ $log['id'] }}</td>
                <td>{{ $log['name'] }}</td>
                <td>{{ $log['date'] }}</td>
                <td>
                  <a href="/admin/log/{{$log['id']}}" target="_blank">view</a>
                </td>
                <td>
                  <a href="/admin/log/download/{{$log['id']}}">download</a>
                </td>
              </tr>
            @endforeach
          </table>
        </div>
      </div>
@stop
