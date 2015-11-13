var last_solution = 0;


$(document).ready(function() {

	solutionListener(last_solution);

});


function solutionListener()
{

	$.ajax({
		type: "GET",
		url: "listen/solution/"+last_solution,
		success: function(solutions)
		{	

			console.log(JSON.stringify(solutions));

			$.each(solutions, function(key, sol) 
			{
				console.log(sol.solution);

				key = sol.user_id+'_'+sol.category_id;

				$('#sol_'+key).html(sol.solution);
				$('#conf_'+key).html(sol.confidence + '%');
				$('#sol_'+key).parent().css('background-color', '#FFC92C'); // Highlight the cell
				$('#sol_'+key).parent().animate({'backgroundColor': '#FFF' }, 30000); // and then slowly fade it out
				last_solution = sol.id;
			});
			
		}
	});

	setTimeout(solutionListener, 2000);

}
