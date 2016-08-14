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

          @if (count($errors) > 0)
            <div class="text-danger">
                <p>There was a problem with your upload...</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>ERROR: {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
          @endif

          {!! Form::open(array('url'=>'/admin/config-files/upload',
                               'method'=>'POST', 'files'=>true)) !!}
            <label class="btn btn-success btn-file">
                Upload Config
                <input type="file" style="display: none;"
                      name="config_file"  onchange="this.form.submit()">
            </label>
          {!! Form::close() !!}
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
