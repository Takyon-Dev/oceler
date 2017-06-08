var last_solution = 0;
var last_message_time = 0;
var PING_INTERVAL = 2000; // Time between pings, in ms

/*
	Pings the server at a set interval for messages and solutions
 */
function ping()
{
	$.ajax({
		type: "GET",
		url: "/player/ping/solution/"+ last_solution +"/message/" + last_message_time,
		success: function(response)
		{
			if(response == -1){
				window.location = '/player/trial/trial-stopped';
			}

			if(response.solutions){
				solutionHandler(response.solutions);
			}
			if(response.messages.length > 0){
				messageHandler(response.messages);
			}
		}
	});

	setTimeout(ping, PING_INTERVAL);

}

function solutionHandler(solutions)
{

	$.each(solutions, function(key, sol)
	{
		addNewSolution(sol);
		last_solution = sol.id;
	});
}

function messageHandler(messages)
{
	// If the player is typing a reply, just return.
	// This prevents the message frame (including the reply field)from reloading.
	if($(".reply-form").is(":visible")) return;

	var fresh_reply;
	var fresh_msg;

	$.each(messages, function(key, msg){

		show_alert = false;
		user_reply = false;

			if(msg.sender.id != user_id){

				$.each(msg.replies, function(key, reply){
					if(reply.replier == user_id){
						user_reply = true;
					}
				});

			if(!user_reply) show_alert = true;
		};
	});

	if(show_alert) newMessageAlert();

	$.each(messages, function(key, msg)
	{
		var m = new Message(msg.users, msg.sender, msg.message, msg.factoid, msg.share_id, msg.id);

		m.addMessage($("#messages"));

		traverseMessageThread(msg.shared_from, msg);

		$.each(msg.replies, function(key, reply){
			var r = new Reply(msg.users, reply.replier, reply.message, msg.id);
			r.addMessage($("#msg_" + msg.id));
		});

		last_message_time = msg.updated_at;
	});

}

function newMessageAlert()
{
	if(!$(".msg-alert").is(":visible")){
		$(".msg-alert").fadeIn();
		setTimeout(function(){
			$(".msg-alert").fadeOut();
		}, 10000);
	}
}

function queueListener()
{

  $.ajax({
    type: "GET",
    url: "/admin/listen/queue",
    success: function(queued_players)
    {

      $("#queued_players>tbody.players").html('');

      $.each(queued_players, function(key, queue){

        var row = $('<tr>');
        var name = $('<td>' + queue.users.name + '</td>');
        var email = $('<td>' + queue.users.email + '</td>');
        var ip = $('<td>' + queue.users.ip_address + '</td>');
        var user_agent = $('<td>' + queue.users.user_agent + '</td>');
        var created = $('<td>' + queue.created_at  + '</td>');
        var updated = ('<td>' + queue.updated_at + '</td>');

        $(row).append(name, email, ip, user_agent, created, updated);
        $("#queued_players>tbody.players").append(row);

      });

    }
  });
}

function playerTrialListener(trial_id)
{
	url =  "/admin/listen/trial";

	if(trial_id) url += "/" + trial_id;

  $.ajax({
    type: "GET",
    url: url,
    success: function(trials)
    {

      $("#trials>tbody.players").html('');

			if(trial_id){

				$.each(trials.users, function(i, user){

					row = buildUserDataRow(user);
					solutions = (user.solutions.length) ? addSolutionsRow(user.solutions) : '';

					$("#trials>tbody.players").append(row, solutions);
				});
			}

			else {
	      $.each(trials, function(i, trial){

					$.each(trial.users, function(j, user){
						row = buildUserDataRow(user);
						solutions = (user.solutions.length) ? addSolutionsRow(user.solutions) : '';

						$("#trials>tbody.players").append(row, solutions);
					});
	      });

			}
    }
  });
}

function buildUserDataRow(user)
{
	var row = $('<tr>');
	var node = $('<td>' + user.node + '</td>');
	var name = $('<td>' + user.player_name + '</td>');
	var email = $('<td>' + user.email + '</td>');
	var ip = $('<td>' + user.ip_address + '</td>');
	var user_agent = $('<td>' + user.user_agent + '</td>');
	var created = $('<td>' + user.pivot.created_at  + '</td>');
	var updated = $('<td>' + user.pivot.updated_at + '</td>');

	$(row).append(node, name, email, ip, user_agent, created, updated);

	return row;
}

function addSolutionsRow(solutions)
{
	var s = '';
	$.each(solutions, function(k, sol){
		s += '<span class="text-muted">' + sol.name + ': </span>';
		s += '<span class="text-info">' + sol.solution + '</span>';
		s += '<span class="text-info"> ' + sol.confidence + '%</span> ';
	});

	var solutions_row = $('<tr></tr><tr class="solutions"><td></td><td><strong>Solutions:</strong></td><td colspan="5">' + s + '</td></tr>');

	return solutions_row;
}

function distributionListener(node, distribution_interval, fset_id)
{

	// Increment wave (global) by one
	wave = wave + 1;
	console.log("F: " + fset_id + "N: " + node + "W: " + wave);
	var delay = distribution_interval * 60000; // Converted from minutes to milliseconds
	$.ajax({
		type: "GET",
		url: "/listen/system-message",
		data: {"node" : node, "wave" : wave, "factoidset_id" : fset_id}
	});

	setTimeout(function(){
		distributionListener(node, distribution_interval, fset_id);
	}, delay)

}
