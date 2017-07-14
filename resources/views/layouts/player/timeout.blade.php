@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
  <script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>
  <script>
    deleteCookie('OcelerTime');
  </script>
@stop

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">

      <h1 class="text-primary text-center">
        There are no trials available at this time.
      </h1>
      <h3 class="text-center">
        Click the button below to end this task and
        collect your payment of ${{ number_format($earnings, 2) }}.
      </h3>
      <div class="text-center">
        @include('layouts.includes.mturk-external-submit-form.blade')
      </div>
    </div>
  </div>
</div>
@stop
