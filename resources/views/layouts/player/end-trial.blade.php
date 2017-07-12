@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">

      <div class="text-center">
        <form action="{{$submitTo}}//mturk/externalSubmit" method="POST">
          <input type="text" name="assignmentId" id="assignmentId" value="{{$assignmentId}}">
          <input type="submit" value="Submit">
        </form>
        @if($group->survey_url)
          <h3>Next, you'll take a short survey.</h3>
          <a href="{{ $group->survey_url }}" role="button" class="btn btn-primary btn-lg">Continue</a>
        @else
            <h3>This is the end of the experiment. Thank you for your participation!</h3>
            <h3>
              You earned ${{ $total_earnings['bonus'] }} for your performance
              in addition to your base payment of ${{ $total_earnings['base_pay'] }}
              for a total of ${{ ($total_earnings['bonus'] + $total_earnings['base_pay']) }}

        @endif
      </div>
    </div>
  </div>
</div>
@stop
