$(document).ready(function() {

	$(document).on('click', '.sol-edit' , function(event) {

		showSolutionForm(this);
		event.preventDefault();
	}); 

	$(document).on('click', '.sol-cancel' , function(event) {

		cancelSolutionForm(this);
		event.preventDefault();
	});


	$(document).on('click', '.sol-save' , function(event) {

		saveSolutionForm(this);
		event.preventDefault();
	});

});

/**
* Displays the solution form and populates it with
* the current solution (if any)
*
*/
function showSolutionForm(self)
{
	// Targets the solution form for this category
	form = $(self).siblings('.solution-form');

	form.show( 400, function() {

		// Populates solution form with the current solution and confidence
		curr_sol = $(self).siblings('.sol').html(); // Gets the current solution
		// Gets the current confidence (trimming off the % symbol)
		curr_conf = $(self).siblings('.conf').html().slice(0,-1);

		form.find('input[name="solution"]').val(curr_sol);
		if(curr_conf){
			form.find('select[name="confidence"]').val(curr_conf);
		}
	});

}

function saveSolutionForm(self)
{

	data = $(self).parent().serialize();

	$.ajax({
		type: "POST",
		url: "/solution",
		data: data,
		success: function()
		{

			hideSolutionForm(self);
		}
	});
}

function cancelSolutionForm(self)
{

	hideSolutionForm(self);

}

function hideSolutionForm(self)
{
	$(self).parent().hide();
}

function addNewSolution(sol)
{

	key = sol.user_id+'_'+sol.category_id;

	$('#sol_'+key).html(sol.solution);
	$('#conf_'+key).html(sol.confidence + '%');
	$('#sol_'+key).parent().css('background-color', '#FFC92C'); // Highlight the cell
	$('#sol_'+key).parent().animate({'backgroundColor': '#FFF' }, 30000); // and then slowly fade it out
}
