@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('js')
  <script type="text/javascript" src="{{ asset('js/trial-config.js') }}"></script>
  <script>

    $(document).ready(function(){

      $("#num_rounds").change(function(){
        showConfigBoxes('round', $(this).val());
      }).change();

      $("#num_groups").change(function(){
        showConfigBoxes('group', $(this).val());
      }).change();

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
                <div class="col-md-6">
                  <div class="form-group">
                    {!! Form::label('distribution_interval', 'Distribution interval') !!}

                    {!! Form::input('number', 'distribution_interval', 0, ['class'=>'num-input', 'min'=>'0']) !!} min.<br>

                    {!! Form::label('num_players', 'Number of players') !!}

                    {!! Form::input('number', 'num_players', 4, ['class'=>'num-input', 'id'=>'num_players', 'min'=>'1']) !!}<br>

                    {!! Form::label('num_groups', 'Number of groups') !!}

                    {!! Form::input('number', 'num_groups', 1, ['class'=>'num-input', 'id'=>'num_groups', 'min'=>'1']) !!}<br>

                    {!! Form::label('mult_factoid', 'Multiple factoid selection') !!}

                    {!! Form::checkbox('mult_factoid', '1') !!}<br>

                    {!! Form::label('pay_correct', 'Payment for correct answers') !!}

                    {!! Form::checkbox('pay_correct', '1') !!}<br>

                    {!! Form::label('pay_time_factor', 'Payment per minutes correct') !!}

                    {!! Form::checkbox('pay_time_factor', '1') !!}<br>

                    {!! Form::label('payment_per_solution', 'Payment per solution') !!}

                    {!! Form::input('number', 'payment_per_solution', .05, ['class'=>'num-input', 'step'=>'.01', 'min'=>'0']) !!}<br>

                    {!! Form::label('payment_base', 'Base pay') !!}

                    {!! Form::input('number', 'payment_base', 5, ['class'=>'num-input', 'step'=>'.01', 'min'=>'0']) !!}<br>

                    {!! Form::label('num_rounds', 'Number of rounds') !!}

                    {!! Form::input('number', 'num_rounds', 1, ['class'=>'num-input', 'id'=>'num_rounds']) !!}<br>

                  </div>
                </div>
          </div>
        </div>
        <div class="row" id="rounds">
          <h2 class="bg-info text-center">Rounds</h2>

          <div class="col-md-3 round-container">
            <h3 class="bg-info">Round #<span>1</span></h3>
            <div class="form-group bg-muted">
              {!! Form::label('round_timeout', 'Time:') !!}
              {!! Form::input('number', 'round_timeout[]', 20, ['class'=>'num-input']) !!} min.<br>

              {!! Form::label('factoidset_id', 'Factoids:') !!}
              {!! Form::select('factoidset_id[]', $factoidsets) !!}<br>


              {!! Form::label('nameset_id', 'Names:') !!}
              {!! Form::select('nameset_id[]', $namesets) !!}<br>

            </div>
          </div>


        </div>
        <div class="row">
          <div id="groups">
            <h2 class="bg-info text-center">Groups</h2>
            <div class="col-md-6 group-container">
              <h3 class="bg-info">Group #<span>1</span></h3>
              <div class="form-group">
                {!! Form::label('network', 'Network:') !!}<br>
                {!! Form::select('network[]', $networks) !!}<br>

                {!! Form::label('survey_url', 'Survey URL:') !!}<br>
                {!! Form::text('survey_url[]', null, ['class'=>'form-control']) !!}<br>

              </div>
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
