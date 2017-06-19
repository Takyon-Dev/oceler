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
    var num_players_to = Object.keys(players_to).length;
    var players_from = <?php echo json_encode($players_from); ?>;
    var distribution_interval = "{{ $trial->distribution_interval }}";
    var factoidset_id = "{{ $trial->rounds[(Session::get('curr_round') - 1)]->factoidset_id}}"
    var system_msg_name = "{{ $system_msg_name }}" || "System";

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

      // Prevent any disabled links (e.g. share links) from being clicked on
      $('body').on('click', 'a.disabled', function(event) {
        event.preventDefault();
      });

      // Initiates the factoid distribution listener
      distributionListener({{ $nodes[Auth::user()->id]}}, distribution_interval,
                              factoidset_id);

      // Pulls any previous searches for this round
      // (in case the page is reloaded)
      reloadSearch();

      // Initiates the ping function in listen.js
      setTimeout(function(){
        ping();
      }, 1000)


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
        <div class="row top-frame">
            @include('layouts.player.solutions')
            @include('layouts.player.timer')
        </div>
        <div class="row">
            @include('layouts.player.messages')
            @include('layouts.player.search')
        </div>
    </div>
@stop
