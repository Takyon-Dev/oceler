@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">

      <h1 class="text-primary text-center">
        {{ $msg }}
      </h1>
      <h3 class="text-center">
        Click the button below to complete this assignment and
        collect your payment of ${{ number_format($total_earnings['bonus'], 2) }}.
      </h3>
      <div class="text-center">
        @include('layouts.includes.mturk-external-submit-form')
      </div>
    </div>
  </div>
</div>
@stop
