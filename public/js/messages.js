$(document).ready(function() {


	$(document).on('click', '#msg_send' , function(event) {

		sendMessage();
		event.preventDefault();
	});

});

/**
 * Serializes the message form data and sends it via
 * POST to the MesageController. On success, calls the
 * clearMessageForm function to reset the form.
 *
 * @return none
 */
function sendMessage()
{

	// Serialize the form data
	msgData = $("#msg_form").serialize();

	// Laravel requires a CSRF token for POST, so we add it
	$.ajaxPrefilter(function(options, originalOptions, xhr) {
	        var token = $('#_token').val();

	        if(token){
	          return xhr.setRequestHeader('X-CSRF-TOKEN', token);
	        }
	});

	// Send the message to the Message Controller
	$.post(
		'message',
		msgData,
		function (data) {
    	clearMessageForm(); // On success, reset the form
		})
		.fail(function () {
  		// Add fail function here
		}
	);
}

/**
 * Resets the message form.
 *
 * @return none
 */
function clearMessageForm()
{
	document.getElementById("msg_form").reset();
}
