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

				$('#sol_'+sol.user_id+'_'+sol.category_id).html(sol.solution);
				$('#conf_'+sol.user_id+'_'+sol.category_id).html(sol.confidence + '%');
				last_solution = sol.id;
			});
			
		}
	});

	setTimeout(solutionListener, 2000);

}
