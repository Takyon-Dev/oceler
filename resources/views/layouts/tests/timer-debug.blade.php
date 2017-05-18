@extends('layouts.master')

@section('js')
	<script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>
@stop
@section('content')
<script>

	$(document).ready(function() {
		var server_time = "{{ $server_time }}";
		var start_time = "{{ $trial->rounds[(Session::get('curr_round') - 1)]->updated_at }}";
		var round_timeout = "{{ $trial->rounds[(Session::get('curr_round') - 1)]->round_timeout }}";
		var time = 0;

		var initial = "var server_time: " + server_time + "<br>";
		initial += "var start_time: " + start_time + "<br>";
		initial += "var round_timeout: " + round_timeout + "<br>";
		$("#output").append(initial);

		addDebugTimer(server_time, start_time, round_timeout);
		debugTimerTick();
	});
</script>
<div id="timer_container" class="col-md-12">
	<div class="h4">
		Time remaining: <span id="timer" class="text-primary"></span>
	</div>
	<div id="output"></div>
</div>
@stop
