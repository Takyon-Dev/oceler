@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  @include('layouts.player.menu')
  <div class="row">
    <div class="col-md-12">
      <h1 class="text-muted text-center">[ Instructions / description placeholder ]</h1>
      <div class="text-center">
        <a href="/player/trial/queue" role="button" class="btn btn-primary btn-lg">Join Game</a>
    </div>
    </div>
  </div>
</div>
@stop
