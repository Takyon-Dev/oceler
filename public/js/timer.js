function addTimer(server_time, start, duration){

	// convert end time from minutes to milliseconds
	var round_time = duration * 60000;

	serverTime = new Date(server_time).getTime();

	var startTime = new Date(start).getTime();

	timeRemaining = (startTime + round_time) - serverTime;

	console.log("Round Time: " + round_time + " Server Time: " + serverTime + " Start Time: " + startTime + " Time Remaining: " + timeRemaining);

	if(timeRemaining < 0) timeRemaining = 0;

	var endTime = timeRemaining;

	time = endTime;

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
			timerTick();
		}, 1000);

	}

	// If no time is left, the timer is set to display trial ended
	// and the timer cookie is deleted
	else {
		if(TimerVars.curr_round == TimerVars.total_rounds){
			timer.innerHTML = 'Trial has ended';
			return;
		}
		else {
			location.reload();
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
