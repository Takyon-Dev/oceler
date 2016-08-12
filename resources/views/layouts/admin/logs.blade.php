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

          @foreach($logs as $log)
            <h2>
              <a href="/trial-logs/{{ $log }}">
                {{$log}}
              </a>
            </h2>

          @endforeach
        </div>
      </div>
@stop
