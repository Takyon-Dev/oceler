function addTimer(server_time, start, duration){

	// convert end time from minutes to milliseconds
	var round_time = duration * 60000;

	var serverTime = (server_time * 1000);

	var startTime = (start * 1000);

	timeRemaining = (startTime + round_time) - serverTime;

	if(timeRemaining < 0) timeRemaining = 0;

	var endTime = timeRemaining;

	time = endTime;

}

function dateDebug(server_time, start, duration)
{

	var out = "Called dateDebug()<br>";

	out += "Parameters :: server_time : " + server_time
								+ " start : " + start + " duration : " + duration + "<br>";

	// convert end time from minutes to milliseconds
	var round_time = duration * 60000;

	out += "round_time : " + round_time + "<br>";

	//serverTime = new Date(server_time).getTime();
	serverTime = (server_time * 1000);

	out += "serverTimeDate : " + serverTime + "<br>";

	//var startTime = new Date(start).getTime();

	startTime = (start * 1000);

	out += "startTimeDate : " + startTime + "<br>";

	timeRemaining = (startTime + round_time) - serverTime;

	if(timeRemaining < 0) timeRemaining = 0;

	out += "timeRemaining : " + timeRemaining + "<br>";

	var endTime = timeRemaining;

	time = endTime;

	out += "Setting :: endTime  : " + endTime +  " time : " + time + "<br>";
	$("#output").append(out);

}

function addDebugTimer(server_time, start, duration){
	var out = "Called addDebugTimer()<br>";

	out += "Parameters :: server_time : " + server_time
								+ " start : " + start + " duration : " + duration + "<br>";

	// convert end time from minutes to milliseconds
	var round_time = duration * 60000;

	out += "round_time : " + round_time + "<br>";

	var serverTime = (server_time * 1000);

	out += "serverTimeDate : " + serverTime + "<br>";

	var startTime = (start * 1000);

	out += "startTimeDate : " + startTime + "<br>";

	timeRemaining = (startTime + round_time) - serverTime;

	if(timeRemaining < 0) timeRemaining = 0;

	out += "timeRemaining : " + timeRemaining + "<br>";

	var endTime = timeRemaining;

	time = endTime;

  out += "Setting :: endTime  : " + endTime +  " time : " + time + "<br>";
	$("#output").append(out);
}

function addAdminTimer(server_time, start, duration, trial_id)
{

	// convert end time from minutes to milliseconds
	var round_time = duration * 60000;

	localTime = new Date().getTime();

	serverTime = new Date(server_time).getTime();

	var startTime = new Date(start).getTime();

	timeRemaining = (startTime + time) - serverTime;

	if(timeRemaining < 0) timeRemaining = 0;

	// Add the duration time to the current time
	var endTime = timeRemaining;

	if(!readCookie('OcelerTime_T' + trial_id )){
		createCookie('OcelerTime_T' + trial_id, endTime);
	}

}

function debugTimerTick()
{

	// Subtracts one second (1000 ms) from the time
	var ending = time;
	var remaining = ending - 1000;
	time = remaining;

	var out = "debugTimerTick() :: remaining: " + remaining + "<br>";
	$("#output").append(out);
	var timer = document.getElementById('timer');

	// If there is any tme remaining, it displays it
	if(remaining > 0){

		if(timer){
				display = display_time(remaining);
				timer.innerHTML = display;
		}
		out = "Recursively calling debugTimerTick()<br>";
		$("#output").append(out);
		setTimeout(function() {
			debugTimerTick();
		}, 1000);

	}

	// If no time is left, the timer is set to display zero
	// and the player is redirected
	else {
		out = "Timer has expired - redirect occurring";
		$("#output").append(out);
		timer.innerHTML = '00:00';
		//window.location.href = '/player/trial/end-round';
		return;
	}

}

function timerTick()
{

	// Subtracts one second (1000 ms) from the time
	var ending = time;
	var remaining = ending - 1000;
	time = remaining;

	var timer = document.getElementById('timer');

	// If there is any tme remaining, it displays it
	if(remaining > 0){

		if(timer){
				display = display_time(remaining);
				timer.innerHTML = display;
		}

		setTimeout(function() {
			timerTick();
		}, 1000);

	}

	// If no time is left, the timer is set to display zero
	// and the player is redirected
	else {

		timer.innerHTML = '00:00';
		window.location.href = '/player/trial/end-round';
		return;
	}

}

function adminTimerTick(trial_id){

	// Subtracts one second (1000 ms) from the time
	var ending = time;
	var remaining = ending - 1000;
	time = remaining;

	var timer = document.getElementById('timer');

	// If there is any tme remaining, it displays it
	if(remaining > 0){

		if(timer){
				display = display_time(remaining);
				timer.innerHTML = display;
		}

		setTimeout(function() {
			adminTimerTick();
		}, 1000);

	}

	/* If no time is left, if all rounds have completed
	   display trial ended message, otherwise reload the page
	   to start a new timer for the next round */
	else {

		if(TimerVars.curr_round == TimerVars.total_rounds){
			timer.innerHTML = 'Trial has ended';
			return;
		}
		else {
			setTimeout(function() {
				location.reload()
			}, 3000);
			;
		}
	}

}

function display_time(time){

	var theTime = new Date(time);

	var minutesDisplay = pad(theTime.getMinutes(), 2);
	var secondsDisplay = pad(theTime.getSeconds(), 2);

	return minutesDisplay+':'+secondsDisplay;

}


function pad(number, length) {

	var str = '' + number;
	while (str.length < length) {
		str = '0' + str;
	}

	return str;

}
