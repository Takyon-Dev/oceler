function addTimer(min, loc){

		// convert start time from minutes to milliseconds
		var time = min * 60000;

		var currentTime = new Date();

		var startTime = currentTime.getTime();
		var endTime = new Date(currentTime.getTime() + time);

    if(!readCookie('OcelerTime')){
      createCookie('OcelerTime', endTime);
			createCookie('OcelerRedirect', loc);
    }

}

function timerTick(){

	// Subtracts the current time from the initial time value stored in the cookie
	time_now = new Date();
	time_num = time_now.getTime();

	the_end = readCookie('OcelerTime');
	the_end = new Date(the_end);
	ending = the_end.getTime();
	remaining = ending - time_num;

	timer = document.getElementById('timer');

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
	else {

		timer.innerHTML = '00:00';

    redirect = readCookie('OcelerRedirect');
		if(redirect !== 'undefined') window.location.href = redirect;
		return;
	}

}

function pause_timer(){

	time_now = new Date();
	time_num = time_now.getTime();

	the_end = readCookie('qualtrics_timer');
	the_end = new Date(the_end);
	ending = the_end.getTime();
	remaining = ending - time_num;

	createCookie('qualtrics_pause_time', remaining);

}

function restart_timer(){

		var remaining = parseInt(readCookie('qualtrics_pause_time'));


		var currentTime = new Date();

		var endTime = new Date(currentTime.getTime() + remaining);

		createCookie('qualtrics_timer', endTime);


}


function createCookie(name, value) {

		var date = new Date();
		date.setTime(date.getTime()+ (2*60*60*1000) ); // expires in 2 hours
		var expires = "; expires="+date.toGMTString();


	document.cookie = name+"="+value+expires+"; path=/";

}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return false;
}

function deleteCookie(name) {
  document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}


function display_time(timer){

	var theTime = new Date(timer);

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
