@extends('layouts.master')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('css/player_style.css') }}">
@stop

@section('js')
  <script>

    // Stores the user ID as vars accessible by all other javascript files
    var user_id = "{{ Auth::user()->id }}";
    var user_name = "{{ Auth::user()->player_name }}";
    var players_to = <?php echo json_encode($players_to); ?>;
    var players_from = <?php echo json_encode($players_from); ?>;
    var distribution_interval = "{{ $trial->distribution_interval }}";

    // used for search
    var user_node = "{{ $user_node }}";
    var wave = 0;


    $(document).ready(function(){

      // Adds csrf token to AJAX headers
      $.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });

      // Prevents ENTER key from submitting a form
      $(window).keydown(function(event){
        if(event.keyCode == 13) {
          event.preventDefault();
          return false;
        }
      });

      solutionListener(last_solution);
      messageListener(last_message_time);
      distributionListener({{ $nodes[Auth::user()->id]}}, distribution_interval,
                         {{ $trial->rounds[(Session::get('curr_round') - 1)]
                                  ->factoidset_id}});

    });

  </script>
	<script type="text/javascript" src="{{ asset('js/solutions.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/messages.js') }}"></script>
  <script type="text/javascript" src="{{ asset('js/search.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/listen.js') }}"></script>
  <script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>

@stop


@section('content')
    <div class="container full-width">
        <div class="row">
            @include('layouts.player.timer')
        </div>
        <div class="row">
            @include('layouts.player.solutions')
        </div>
        <div class="row">
            @include('layouts.player.messages')
            @include('layouts.player.search')
        </div>
    </div>
@stop
