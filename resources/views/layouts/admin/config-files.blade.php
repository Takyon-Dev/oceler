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
                <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
                Upload Config
                <input type="file" style="display: none;"
                      name="config_file"  onchange="this.form.submit()">
            </label>
          {!! Form::close() !!}
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 config-container">
          <h3 class="bg-info text-center">Factoid sets</h3>
          @foreach($factoidsets as $factoidset)
            <li>
              {{ $factoidset->name }}
              <span class="text-muted pull-right">
                {{ $factoidset->created_at }}
              </span>
            </li>
          @endforeach
        </div>
        <div class="col-md-4 config-container">
          <h3 class="bg-info text-center">Networks</h3>
          @foreach($networks as $network)
            <li>
              {{ $network->name }}
              <span class="text-muted pull-right">
                {{ $network->created_at }}
              </span>
            </li>
          @endforeach
        </div>
        <div class="col-md-4 config-container">
          <h3 class="bg-info text-center">Names</h3>
          @foreach($namesets as $nameset)
            <li>
              {{ $nameset->name }}
              <span class="text-muted pull-right">
                {{ $nameset->created_at }}
              </span>
            </li>
          @endforeach
        </div>
      </div>
    </div>
@stop
