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


	// IS THE BELOW STILL NEEDED SINCE THE ENTIRE form
	// IS BEING SERIALIZED?

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

/**
*	Prepends new messages (or messages with new replies) to
*	the message feed.
*/
function updateMessageWindow(msg)
{

	console.log(msg);

	var div = $('<div>', {id: msg.id, class: 'message'});

	// If the sender == user, we add a class to highlight the header
	header_class = (msg.sender_id == user_id) ? 'header text-danger' : 'header';

	header_str = createHeader(msg.sender, msg.users);

	var header = $('<span>', {class: header_class});
	$(header).html(header_str);

	var msg_body = $('<span>', {class: 'msg-body'});
	$(msg_body).html(msg.message);

	var reply_link = $('<a>', {id: msg.id});
	$(reply_link).html('reply');
	$(reply_link).click(function(){ alert('Clicked reply on ' + this.id) });

	var share_link = $('<a>', {id: msg.id});
	$(share_link).html('share');
	$(share_link).click(function(){ alert('Clicked share on ' + this.id) });

	var links = $('<span>');

	$(links).append('(');
	$(links).append($(reply_link));
	$(links).append('/');
	$(links).append($(share_link));
	$(links).append(')');

	$(msg_body).append(links);

	$(div).append($(header));
	$(div).append(msg_body);

	$.each(msg.replies, function(index, reply){

		var reply_div = $('<div>', {class: 'message reply'});
		reply_header_str = createHeader(reply.replier);
		// If the sender == user, we add a class to highlight the header
		reply_header_class = (msg.sender_id == user_id) ? 'header text-danger' : 'header';
		var reply_header = $('<span>', {class: reply_header_class});
		$(reply_header).html(reply_header_str);

		var reply_body = $('<span>', {class: 'msg-body'});
		$(reply_body).html(reply.message);
		$(reply_div).append($(reply_header));
		$(reply_div).append(reply_body);
		$(div).append($(reply_div));
	});


	$("#msg_feed").append($(div));

}

function createHeader(from, to)
{

	var header;

	// If sender == user header starts with 'You (player_name)' and includes
	// the recipients names
	if(from.sender_id == user_id){

		header = 'You (' + user_name + ') to ';

		$.each(to, function( index, value ) {
			header += value.player_name;
			header += (index != to.length -1) ? ', ' : ':'; // Add colon to last
		});
	}

	else{
		header = from.player_name + ':';
	}

	return header;
}

var Message = function(to, sender, msg, id)
{
	this.to = to;
	this.sender_id = sender.id;
	this.sender_name = sender.player_name;
	this.msg = msg;
	this.id = id;

}

Message.prototype.header = function(){

 	var header;

	// If sender == user header starts with 'You (player_name)' and includes
	// the recipients names
	if(this.sender_id == user_id){

		var len = this.to.length;
		header = 'You (' + user_name + ') to ';

		$.each(this.to, function( index, value ) {
			header += value.player_name;
			header += (index != len -1) ? ', ' : ':'; // Add colon to last
		});
	}

	else{
		header = this.sender_name + ':';
	}

	return header;
}

Message.prototype.toHTML = function(){

	// Create div to hold the message
	var div = $('<div>', {id: 'msg_' + this.id, class: 'message'});

	// Add the header
	// (If the sender == user, we add a class to highlight the header)
	header_class = (this.sender_id == user_id) ? 'header text-danger' : 'header';

	var header_container = $('<span>', {class: header_class});
	$(header_container).append(this.header());

	// Create span to hold message body
	var msg_body = $('<span>', {class: 'msg-body'});
	$(msg_body).append(this.msg);

	// Add the reply and share links
	var reply_link = $('<a>', {id: this.id});
	$(reply_link).html('reply');
	$(reply_link).click(function(){
											$("#msg_" + this.id + " .reply-form").show(400);
											$("#msg_" + this.id + " .reply-form input[name='reply']").focus();
										});

	var share_link = $('<a>', {id: this.id});
	$(share_link).html('share');
	$(share_link).click(function(){ alert('Clicked share on ' + this.id) });

	var links = $('<span>');

	$(links).append('(');
	$(links).append($(reply_link));
	$(links).append('/');
	$(links).append($(share_link));
	$(links).append(')');

	$(msg_body).append(links);

	// Add the reply form
	var reply_form = $('<form>', {class: 'reply-form'});
	var reply_prompt = $('<h3>Reply to ' + this.sender_name + ':</h3>');
	var reply = $('<input>', {type: 'text', name: 'reply'});
	var parent_msg = $('<input>', {type: 'hidden', name: 'parent_msg', value: this.id});
	var reply_send = $('<button type="button" class="btn btn-primary btn-sm pull-right">SEND</button>');
	$(reply_send).click(function(){
												msgData = $(this).parent().serialize();
												// Send the message to the Reply Controller
												$.post(
													'reply', /* *** NEED TO CREATE A NEW ROUTE! *** */
													msgData,
													function (data) {
														$("input[name='reply']").val('');
														$(this).parent().hide(400);
													})
													.fail(function () {
														// Add fail function here
													}
												);
											});

	var reply_cancel = $('<button type="button" class="btn btn-primary btn-sm pull-left">CANCEL</button>');
	$(reply_cancel).click(function(){
													$("input[name='reply']").val('');
													$(this).parent().hide(400);
												});

	$(reply_form).append(reply_prompt);
	$(reply_form).append(reply);
	$(reply_form).append(parent_msg);
	$(reply_form).append(reply_cancel);
	$(reply_form).append(reply_send);
	$(div).append(reply_form);

	$(div).append(header_container);
	$(div).append(msg_body);
	return $(div);
}

Message.prototype.addMessage = function(target){

	$(target).append($(this.toHTML()));
}

/**
 * Reply extends Message
*/
function Reply(to, sender, msg, id)
{
	Message.call(this, to, sender, msg, id);
}

// Must create a Reply prototype that inherits from Message prototype
Reply.prototype = Object.create(Message.prototype);

// Set the "constructor" property to refer to Reply
Reply.prototype.constructor = Reply;

Reply.prototype.header = function(){

 	var header;

	// If sender == user header starts with 'You (player_name)' and includes
	// the recipients names
	if(this.sender_id == user_id){

		header = 'You (' + user_name + '):';

	}

	else{
		header = this.sender_name + ':';
	}

	return header;
}

Reply.prototype.toHTML = function(){

	var reply_div = $('<div>', {class: 'message reply'});

	// If the sender == user, we add a class to highlight the header
	reply_header_class = (this.sender_id == user_id) ? 'header text-danger' : 'header';
	var reply_header = $('<span>', {class: reply_header_class});
	$(reply_header).append(this.header());

	var reply_body = $('<span>', {class: 'msg-body'});
	$(reply_body).append(this.msg);
	$(reply_div).append($(reply_header));
	$(reply_div).append(reply_body);

	return $(reply_div);
}
