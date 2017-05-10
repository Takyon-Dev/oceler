@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-12">

      <h1 class="text-primary text-center">
        [ CONTENT GOES HERE ]
      </h1>
      <h3 class="text-center">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque
        congue faucibus tortor, at iaculis ex convallis quis. Morbi ex libero,
        efficitur vitae ipsum elementum, mattis fermentum tortor.
      </h3>
    </div>
  </div>
</div>
@stop
