function initializeTimer(serverTime, startTime, duration){
	console.log('serverTime = ' + serverTime);
	console.log('startTime = ' + startTime);
	var perfData = window.performance.timing;
	var pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;

	var roundTime = 3 * 60000;
	var endTime = Date.now() + roundTime;
	var startTime = Date.now() - pageLoadTime;
	console.log('now = ' + new Date(startTime).toUTCString());
	console.log('endTime = ' + new Date(endTime).toUTCString());

	setInterval(function() {
	  var delta = Date.now() - startTime; // milliseconds elapsed since start
		console.log('delta = ' + delta);
	  var timeRemaining = endTime - startTime - delta;
	  console.log('t = ' + timeRemaining);
	  console.log(displayTime(timeRemaining));
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
