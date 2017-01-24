<script>

	$(document).ready(function() {
		var server_time = "{{ $server_time }}";
		var start_time = "{{ $trial->rounds[(Session::get('curr_round') - 1)]->updated_at }}";
		var round_timeout = "{{ $trial->rounds[(Session::get('curr_round') - 1)]->round_timeout }}";
		addTimer(server_time, start_time, round_timeout, '/player/trial/end-round');
		timerTick(0);
	});
</script>
<div id="timer_container" class="col-md-12">
	<div class="h4">
		Time remaining: <span id="timer" class="text-primary"></span>
	</div>
</div>
