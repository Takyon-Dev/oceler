@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('js')
  <script>

    $(document).ready(function(){

      $('.data-delete').on('click', function (e) {
        if (!confirm('Are you sure you want to delete this trial?')) return;
        e.preventDefault();
        $('#form_delete_' + $(this).data('form')).submit();
      });

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

          <a href="/admin/trial/create" class="btn btn-primary" role="button">
            <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
            New Trial
          </a>
          <table class="table table-striped trials">
            <tr>
              <th>Trial</th>
              <th>Date</th>
              <th>Active</th>
              <th>Players</th>
              <th></th>
              <th></th>
            </tr>
            @foreach($trials as $trial)
            <tr>
              <td>{{ $trial->id }}</td>
              <td>{{ $trial->created_at }}</td>
              <td>
                @if ($trial->is_active)

                  <span class="text-success">Active</span>
                  <a href="/admin/trial/toggle/{{ $trial->id }}"
                          class="btn btn-primary btn-xs" role="button">
                    Make Inactive
                  </a>

                @else

                  <span class="text-danger">Not Active</span>
                  <a href="/admin/trial/toggle/{{ $trial->id }}"
                          class="btn btn-primary btn-xs" role="button">
                    Make Active
                  </a>

                @endif
              </td>
              <td>0/{{ $trial->num_players }}</td>
              <td><a href="/admin/trial/{{ $trial->id }}">View</a></td>

              <td>
                {!! Form::open(['method' => 'DELETE',
                                'route' => ['trial.delete', $trial->id],
                                'id' => 'form_delete_trials_' . $trial->id]) !!}
                  <a href="" class="data-delete" data-form="trials_{{ $trial->id }}">
                    Delete
                  </a>
                {!! Form::close() !!}
              </td>
            </tr>
            @endforeach

        </div>
      </div>
    </div>
@stop
