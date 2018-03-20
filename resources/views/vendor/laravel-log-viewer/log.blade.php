@extends('layouts.master')

@section('css')

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
    <style>
      body {
        font-size: 14px!important;
      /*padding: 25px;*/
      }

      h1 {
      /*  font-size: 1.5em; */
        margin-top: 0;
      }

      #table-log {
        /*  font-size: 0.85rem; */
      }

      .sidebar {
        /*  font-size: 0.85rem; */
          line-height: 1;
      }

      .btn {
          font-size: 0.7rem;
      }


      .stack {
        font-size: 0.85em;
      }

      .date {
        min-width: 75px;
      }

      .text {
        word-break: break-all;
      }

      a.llv-active {
        z-index: 2;
        background-color: #f5f5f5;
        border-color: #777;
      }

      .list-group-item {
        word-wrap: break-word;
      }

      .navbar-nav {
        flex-direction: row!important;
      }
    </style>
    <link rel="stylesheet" href="{{ URL::asset('css/admin_style.css') }}">
@stop

@section('content')
    <div class="container">
      @include('layouts.admin.menu')
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center">Server Log</h1>
          @if ($logs === null)
            <div>
              Log file >50M, please download it.
            </div>
          @else
            <table id="table-log" class="table table-striped">
              <thead>
              <tr>
                <th>Level</th>
                <th>Context</th>
                <th>Date</th>
                <th>Content</th>
              </tr>
              </thead>
              <tbody>

              @foreach($logs as $key => $log)
                <tr data-display="stack{{{$key}}}">
                  <td class="text-{{{$log['level_class']}}}"><span class="fa fa-{{{$log['level_img']}}}"
                                                                   aria-hidden="true"></span> &nbsp;{{$log['level']}}</td>
                  <td class="text">{{$log['context']}}</td>
                  <td class="date">{{{$log['date']}}}</td>
                  <td class="text">

                    {{{$log['text']}}}
                    @if (isset($log['in_file'])) <br/>{{{$log['in_file']}}}@endif
                    @if ($log['stack'])
                      <div class="stack" id="stack{{{$key}}}"
                           style="display: none; white-space: pre-wrap;">{{{ trim($log['stack']) }}}
                      </div>@endif
                  </td>
                </tr>
              @endforeach

              </tbody>
            </table>
          @endif
        </div>
      </div>
      <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
      <!-- FontAwesome -->
      <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
      <!-- Datatables -->
      <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
      <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
      <script>
        $(document).ready(function () {
          $('.table-container tr').on('click', function () {
            $('#' + $(this).data('display')).toggle();
          });
          $('#table-log').DataTable({
            "order": [1, 'desc'],
            "stateSave": true,
            "stateSaveCallback": function (settings, data) {
              window.localStorage.setItem("datatable", JSON.stringify(data));
            },
            "stateLoadCallback": function (settings) {
              var data = JSON.parse(window.localStorage.getItem("datatable"));
              if (data) data.start = 0;
              return data;
            }
          });
          $('#delete-log, #delete-all-log').click(function () {
            return confirm('Are you sure?');
          });
        });
      </script>
@stop
