@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">

      <div class="text-center">
        <h3>This is the end of the experiment. Thank you for your participation!</h3>
        <h3>
          You earned ${{ $total_earnings['bonus'] }} for your performance
          in addition to your base payment of ${{ $total_earnings['base_pay'] }}
          for a total of ${{ ($total_earnings['bonus'] + $total_earnings['base_pay']) }}
        </h3>
          @include('layouts.includes.mturk-external-submit-form')
      </div>
    </div>
  </div>
</div>
@stop
