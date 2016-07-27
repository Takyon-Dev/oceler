var last_solution = 0;
var last_message_time = 0;

function solutionListener()
{

	$.ajax({
		type: "GET",
		url: "listen/solution/"+last_solution,
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
		url: "listen/message/"+last_message_time,
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
