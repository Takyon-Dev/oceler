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
          <a href="/admin/trial/create" class="btn btn-success" role="button">
            <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
            New Trial
          </a>
          <br>or<br>
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
                Load from Config
                <input type="file" style="display: none;"
                      name="config_file"  onchange="this.form.submit()">
            </label>
            <button type="button" class="btn btn-danger pull-right"
                    data-toggle="modal" data-target="#warning">
              Stop all trials
            </button>
          {!! Form::close() !!}

          <table class="table table-striped trials">
            <tr>
              <th>ID</th>
              <th>Trial</th>
              <th>Date</th>
              <th></th>
              <th>Players</th>
              <th></th>
              <th></th>
              <th></th>
            </tr>
            @foreach($trials as $trial)
            <tr>
              <td>{{ $trial->id }}</td>
              <td>{{ $trial->name }}</td>
              <td>{{ $trial->created_at }}</td>
              <td>
                @if ($trial->is_active  && count($trial->users) <= 0)

                  <span class="text-success">Active</span>
                  <a href="/admin/trial/toggle/{{ $trial->id }}"
                          class="btn btn-primary btn-xs" role="button">
                    Make Inactive
                  </a>

                @elseif (!$trial->is_active && count($trial->archive) > 0)

                  <span class="text-warning">Completed</span>
                  <a href="/admin/trial/toggle/{{ $trial->id }}"
                          class="btn btn-primary btn-xs" role="button">
                    Make Active
                  </a>

                @elseif (!$trial->is_active)
                  <span class="text-danger">Not Active</span>
                  <a href="/admin/trial/toggle/{{ $trial->id }}"
                          class="btn btn-primary btn-xs" role="button">
                    Make Active
                  </a>
                @endif

              </td>
              <td>{{ count($trial->users) }} / {{ $trial->num_players }}</td>

              <td>
                @if (count($trial->users) > 0)
                  <a href="/admin/trial/{{ $trial->id }}">View</a>
                @endif
              </td>

              <td>
                {!! Form::open(['method' => 'DELETE',
                                'route' => ['trial.delete', $trial->id],
                                'id' => 'form_delete_trials_' . $trial->id]) !!}
                  <a href="" class="data-delete" data-form="trials_{{ $trial->id }}">
                    Delete
                  </a>
                {!! Form::close() !!}
              </td>

              <td>
              @if(count($trial->users) > 0)

                {!! Form::open(['method' => 'POST',
                                'route' => ['trial.stop', $trial->id]]) !!}
                  <button class="btn btn-link">Stop Trial</button>
                {!! Form::close() !!}
              @endif
              </td>

            </tr>
            @endforeach

        </div>
      </div>
    </div>

    <!-- Stop Trials Modal -->
    <div class="modal fade" id="warning" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger" id="exampleModalLabel">Stop All Trials</h5>
          </div>
          <div class="modal-body">
            <strong>Warning:</strong> This will immediately stop and deactivate
            <strong><em>all</em></strong> active trials.<br>
            Any players in a trial will be removed.
          </div>
          <div class="modal-footer">
            <form action="/admin/stop-all-trials" method="post">
              {{ csrf_field() }}
              <button type="button" class="btn btn-secondary mr-lg-2" data-dismiss="modal">Cancel</button>
              <button class="btn btn-danger float-right" type="submit">Stop Trials</button>
            </form>
          </div>
        </div>
      </div>
    </div>
@stop
