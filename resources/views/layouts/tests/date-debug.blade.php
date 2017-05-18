@extends('layouts.master')

@section('js')
	<script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>
@stop
@section('content')
<script>

	$(document).ready(function() {
		var server_time = "{{ $server_time }}";
		var start_time = "{{ $start_time }}";
		var round_timeout = 1;
		var time = 0;

		var initial = "var server_time: " + server_time + "<br>";
		initial += "var start_time: " + start_time + "<br>";
		initial += "var round_timeout: " + round_timeout + "<br>";
		$("#output").append(initial);

		dateDebug(server_time, start_time, round_timeout);
	});
</script>
<div id="timer_container" class="col-md-12">
	<div class="h4">
		Time remaining: <span id="timer" class="text-primary"></span>
	</div>
	<div id="output"></div>
</div>
@stop
