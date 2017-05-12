@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('content')
    <div class="container">
      @include('layouts.admin.menu')
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center">Data</h1>
          <table class="table table-striped trials">
            $foreach($stats as $s)
            <tr>
              <th>Trial :: {{ $s['trial']['trial_name'] }}</th>
              
            </tr>
            @foreach($trials as $trial)
            <tr>
              <td>{{ $trial->name }}</td>
              <td>{{ $trial->created_at }}</td>
              <td>
                @if ($trial->is_active  && count($trial->users) <= 0)

                  <span class="text-success">Active</span>
                  <a href="/admin/trial/toggle/{{ $trial->id }}"
                          class="btn btn-primary btn-xs" role="button">
                    Make Inactive
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
@stop
