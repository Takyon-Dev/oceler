@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <img src="/img/intro_01.png" class="img-responsive" alt="Intro image">
    </div>
  </div>
</div>
@stop
