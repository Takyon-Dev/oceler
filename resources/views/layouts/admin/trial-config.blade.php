@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('js')
  <script>

    $(document).ready(function(){

      // Adds csrf token to AJAX headers
      $.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });
    });

  </script>

@stop


@section('content')
    <div class="container">
      @include('layouts.admin.menu')
      {!! Form::open(['url'=>'/admin/trial','method'=>'POST', 'id'=>'trial_config']) !!}
        <div class="row">
          <div class="col-md-12">
            <h1 class="text-center">New Trial</h1>
                <div class="col-md-3">
                  <div class="form-group">
                    {!! Form::label('distribution_interval', 'Distribution interval') !!}<br>

                    {!! Form::label('num_waves', 'Number of waves') !!}<br>

                    {!! Form::label('num_players', 'Number of players') !!}<br>

                    {!! Form::label('mult_factoid', 'Multiple factoid selection') !!}<br>

                    {!! Form::label('pay_correct', 'Payment for correct answers') !!}<br>

                    {!! Form::label('num_rounds', 'Number of rounds') !!}<br>

                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">

                    {!! Form::input('number', 'distribution_interval', 0, ['class'=>'num-input']) !!} min.<br>

                    {!! Form::input('number', 'num_waves', 1, ['class'=>'num-input']) !!}<br>

                    {!! Form::input('number', 'num_players', 4, ['class'=>'num-input']) !!}<br>

                    {!! Form::checkbox('mult_factoid', '1') !!}<br>

                    {!! Form::checkbox('pay_correct', '1') !!}<br>

                    {!! Form::input('number', 'num_rounds', 4, ['class'=>'num-input']) !!}<br>
                  </div>
                </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4 round-container">
            <h3>&nbsp;</h3>
            <div class="form-group">
              {!! Form::label('round_timeout', 'Round timeout') !!}<br>

              {!! Form::label('factoidset_id', 'Factoid set') !!}<br>

              {!! Form::label('countryset_id', 'Countries') !!}<br>

              {!! Form::label('nameset_id', 'Names') !!}<br>
            </div>
          </div>

          @for($i = 1; $i <= 4; $i++)
            <div class="col-md-2 round-container">
              <h3 class="bg-info">Round #{{$i}}</h3>
              <div class="form-group">

                {!! Form::input('number', 'round_timeout[]', 20, ['class'=>'num-input']) !!} min.<br>

                {!! Form::select('factoidset_id[]', ['1'=>'factoidset1ha1-17.txt', '2'=>'factoidset1ha2-17.txt']) !!}<br>

                {!! Form::select('countryset_id[]', ['1'=>'countries1.txt']) !!}<br>

                {!! Form::select('nameset_id[]', ['1'=>'names17.txt', '2'=>'names20.txt']) !!}<br>

              </div>
            </div>
          @endfor

        </div>
        <div class="row">

          <div class="col-md-2">
            <h3>&nbsp;</h3>
            <div class="form-group">
              {!! Form::label('organization', 'Organizations') !!}<br>

              {!! Form::label('survey_url', 'Survey URL') !!}<br>

            </div>
          </div>

          <div class="col-md-5">
            <h3 class="bg-info">Group 1</h3>
            <div class="form-group">

              {!! Form::select('organization[]', ['1' => 'organizationTest1.txt']) !!}<br>

              {!! Form::text('survey_url[]', null, ['class'=>'form-control']) !!}<br>

            </div>
          </div>
          <div class="col-md-5">
            <h3 class="bg-info">Group 2</h3>
            <div class="form-group">

              {!! Form::select('organization[]', ['1' => 'organizationTest1.txt']) !!}<br>

              {!! Form::text('survey_url[]', null, ['class'=>'form-control']) !!}<br>

            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            {!! Form::submit('SAVE', ['class'=>'btn btn-primary btn-large pull-right']) !!}
          </div>
        </div>
      {!! Form::close() !!}
    </div>
@stop
