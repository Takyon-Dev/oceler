$(document).ready(function() {


	$(document).on('click', '#msg_send' , function(event) {

		sendMessage();
		event.preventDefault();
	});

});


function sendMessage()
{


	form = $('#msg_form');
	data = form.serialize();

}
