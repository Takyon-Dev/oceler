@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">

      <h1 class="text-primary text-center">
        The task is now over. Thank you for your participation.
      </h1>
      <h3 class="text-center">
        Your payment of [BASE_PAYMENT] will be transferred to your
        Amazon MTurk account.
      </h3>
    </div>
  </div>
</div>
@stop
