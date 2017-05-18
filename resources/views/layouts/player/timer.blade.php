<script>

	$(document).ready(function() {
		var server_time = "{{ $server_time }}";
		var start_time = "{{ $start_time }}";
		var round_timeout = "{{ $trial->rounds[(Session::get('curr_round') - 1)]->round_timeout }}";
		var time = 0;
		addTimer(server_time, start_time, round_timeout);
		timerTick();
	});
</script>
<div id="timer_container" class="col-md-12">
	<div class="h4">
		Time remaining: <span id="timer" class="text-primary"></span>
	</div>
</div>
