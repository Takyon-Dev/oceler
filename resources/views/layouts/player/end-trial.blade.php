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
          You earned a total of ${{ ($total_earnings['bonus'] + $total_earnings['base_pay']) }}
        </h3>
          @if($assignment_id)
            @include('layouts.includes.mturk-external-submit-form')
          @else
            <h3>This concludes the experiment. Thanks for participating!</h3>
          @endif
      </div>
    </div>
  </div>
</div>
@stop
