@extends('layouts.master')

@section('js')
	<script type="text/javascript" src="{{ asset('js/deltaTimer.js') }}"></script>
@stop

@section('content')
	<script>

		$(document).ready(function() {
			var serverTime = "{{ $server_time }}";
			var startTime = "{{ $start_time }}";
			var roundTimeout = "{{ $round_timeout }}";
			console.log(roundTimeout);
			initializeTimer(serverTime, startTime, roundTimeout);
		});
	</script>
	<div id="timer_container" class="col-md-12">
		<div class="h4">
			Time remaining (test): <span id="timer" class="text-primary"></span>
		</div>
	</div>
@stop
