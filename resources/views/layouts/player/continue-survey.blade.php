@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">

      <div class="text-center">
          <h3>Next, you'll take a short survey.</h3>
          <a href="{{ $group->survey_url }}?id={{ $mturk_id }}" role="button" class="btn btn-primary btn-lg">Continue</a>
      </div>
    </div>
  </div>
</div>
@stop
