var last_solution = 0;
var last_message_time = 0;

function solutionListener()
{

	$.ajax({
		type: "GET",
		url: "/listen/solution/"+last_solution,
		success: function(solutions)
		{

			if(solutions == -1){
				window.location = '/player/trial/end';
			}

			$.each(solutions, function(key, sol)
			{
				addNewSolution(sol);
				last_solution = sol.id;
			});

		}
	});

	setTimeout(solutionListener, 2000);

}

function messageListener()
{
	setTimeout(messageListener, 2000);

	// If the player is typing a reply, just return
	if($(".reply-form").is(":visible")) return;

	$.ajax({
		type: "GET",
		url: "/listen/message/"+last_message_time,
		success: function(messages)
		{

			$.each(messages, function(key, msg)
			{
				console.log('MESSAGE:::');
				console.log(msg);
				console.log(':::MESSAGE');
				var m = new Message(msg.users, msg.sender, msg.message, msg.factoid, msg.share_id, msg.id);

				m.addMessage($("#messages"));

				$.each(msg.replies, function(key, reply){
					console.log('REPLY:::');
					console.log(reply);
					console.log(':::REPLY');
					var r = new Reply(msg.users, reply.replier, reply.message, msg.id);
					r.addMessage($("#msg_" + msg.id));
				});

				if(msg.shared_from){
					console.log('SHARED:::');
					console.log(msg.shared_from);
					console.log(':::SHARED');
					var shared = new Reply(msg.shared_from.users, msg.shared_from.sender,
																	msg.shared_from.message, msg.shared_from.factoid,
																	msg.shared_from.share_id, msg.shared_from.id);
					shared.addMessage($("#msg_" + msg.id));

					$.each(msg.shared_from.shared_replies, function(key, reply){
						console.log('REPLY:::');
						console.log(reply);
						console.log(':::REPLY');
						var shared_reply = new Reply(msg.shared_from.users, reply.replier, reply.message, msg.shared_from.id);
						shared_reply.addMessage($("#msg_" + msg.id));
					});
				}

				last_message_time = msg.updated_at;
			});


		}
	});
}

function queueListener()
{

  $.ajax({
    type: "GET",
    url: "/admin/listen/queue/",
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

function distributionListener(node, distribution_interval, factoidset_id)
{
	// Increment wave (global) by one
	wave++;

	var delay = distribution_interval * 60000; // Converted from minutes to milliseconds
	$.ajax({
		type: "GET",
		url: "/listen/system-message/",
		data: {"node" : node, "wave" : wave, "factoidset_id" : factoidset_id}
	});

	setTimeout(function(){
		distributionListener(node, wave, distribution_interval, factoidset_id);
	}, delay)

}
