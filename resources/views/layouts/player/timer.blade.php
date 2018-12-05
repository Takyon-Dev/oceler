<script>

	$(document).ready(function() {
		var secondsRemaining = "{{ $secondsRemaining }}";
		initializeTimer(secondsRemaining);
	});
</script>
<div id="timer_container" class="col-md-2">
	<div class="h4 text-center">
		Time remaining:<br><span id="timer" class="text-primary">00:00</span>
	</div>
</div>
