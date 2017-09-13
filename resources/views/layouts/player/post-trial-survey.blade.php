@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  <div class="row">
    @if($trial_type == 1)
      @include('layouts.player.initial-survey-form')
    @else
      @include('layouts.player.post-trial-survey-form')
    @endif
  </div>
</div>
@stop
