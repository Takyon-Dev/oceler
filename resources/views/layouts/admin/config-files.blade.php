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
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center">Configuration files</h1>

          <div class="secure">Upload form</div>
            {!! Form::open(['url'=>'/admin/config-files/upload','method'=>'POST',
                                  'files'=>true, 'class'=>'form-inline']) !!}

              <div class="form-group">
                {!! Form::file('file', ['class'=>'form-control input-lg']) !!}
                <p class="errors">{!!$errors->first('image')!!}</p>
                @if(Session::has('error'))
                  <p class="errors">{!! Session::get('error') !!}</p>
                @endif
              </div>


          {!! Form::button('UPLOAD', ['class'=>'btn btn-primary btn-large']) !!}
          {!! Form::close() !!}
          <div id="success"> </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3 config-container">
          <h3 class="bg-info">Factoid sets</h3>
        </div>
        <div class="col-md-3 config-container">
          <h3 class="bg-info">Names</h3>
        </div>
        <div class="col-md-3 config-container">
          <h3 class="bg-info">Countries</h3>
        </div>
        <div class="col-md-3 config-container">
          <h3 class="bg-info">Organizations</h3>
        </div>
      </div>
    </div>
@stop
