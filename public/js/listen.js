var last_solution = 0;
var last_message_time = 0;

function solutionListener()
{

	$.ajax({
		type: "GET",
		url: "/listen/solution/"+last_solution,
		success: function(solutions)
		{

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
	$.ajax({
		type: "GET",
		url: "/listen/message/"+last_message_time,
		success: function(messages)
		{

			$.each(messages, function(key, msg)
			{

				var m = new Message(msg.users, msg.sender, msg.message, msg.id);
				m.addMessage($("#messages"));

				$.each(msg.replies, function(key, reply){
					var r = new Reply(msg.users, reply.replier, reply.message, msg.id);
					r.addMessage($("#msg_" + msg.id));
				});

				last_message_time = msg.updated_at;
			});


		}
	});
	setTimeout(messageListener, 2000);
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
