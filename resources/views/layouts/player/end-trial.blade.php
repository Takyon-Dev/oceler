@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">

      <div class="text-center">
        <!--
        <form action="{{$submitTo}}/mturk/externalSubmit" method="POST">
          <input type="text" name="assignmentId" id="assignmentId" value="{{$assignmentId}}">
          <input type="hidden" name="foo" value="bar">
          <input type="submit" value="Submit">
        </form>
        -->
        <h3>This is the end of the experiment. Thank you for your participation!</h3>
        <h3>
          You earned ${{ $total_earnings['bonus'] }} for your performance
          in addition to your base payment of ${{ $total_earnings['base_pay'] }}
          for a total of ${{ ($total_earnings['bonus'] + $total_earnings['base_pay']) }}
        <form action="{{$submitTo}}/mturk/externalSubmit" method="POST">
          <input type="text" name="assignmentId" id="assignmentId" value="{{$assignmentId}}">
          <input type="hidden" name="passed_trial" value="{{ $passed_trial }}">
          <input type="hidden" name="bonus" value="{{ $total_earnings['bonus'] }}">
          <input type="hidden" name="completed_trial" value="true">
          <input class="btn btn-primary btn-lg" type="submit" value="Submit">
        </form>
      </div>
    </div>
  </div>
</div>
@stop
