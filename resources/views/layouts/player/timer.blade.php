<script>

	$(document).ready(function() {
		var round_timeout = "{{ $trial->rounds[(Session::get('curr_round') - 1)]->round_timeout }}";
		addTimer(round_timeout, '/player/trial/end-round');
		timerTick();
	});
</script>
<div id="timer_container" class="col-md-12">
	<div class="h4">
		Time remaining: <span id="timer" class="text-primary"></span>
	</div>
</div>
