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
          <h1 class="text-center">Trials</h1>

          <a href="/admin/trials/new" class="btn btn-primary" role="button">
            New Trial
          </a>
          <table class="table table-striped trials">
            <tr>
              <th>Trial</th>
              <th>Date</th>
              <th></th>
              <th></th>
            </tr>
            <tr>
              <td>1</td>
              <td>7-22-2016 12:27:36 PM</td>
              <td><a href="admin/trials/1">view</a></td>
              <td><a href="admin/trials/1">delete</a></td>
            </tr>
        </div>
      </div>
    </div>
@stop
