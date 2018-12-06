function initializeTimer(secondsRemaining){

	var startTime = Date.now();
	var endTime = Date.now() + (secondsRemaining * 1000);

	if(endTime - startTime <= 0) {
		return;
	};

	var timerDisplay = document.getElementById('timer');

	timer = setInterval(function() {
	  var delta = Date.now() - startTime; // milliseconds elapsed since start
	  var timeRemaining = endTime - startTime - delta;

		if(timeRemaining <= 0) {
			clearInterval(timer);
			timerDisplay.innerHTML = '00:00';
			window.location.href = '/player/trial/end-round';
		}

		display = displayTime(timeRemaining);
		timerDisplay.innerHTML = display;

	}, 1000); // update about every second
}

function displayTime(time){
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
