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
		curr_conf = $(self).siblings('.conf').html().slice(0,-1) // Gets the current confidence (trimming off the % symbol);

		form.find('input[name="solution"]').val(curr_sol); // Fills input with current solution
		form.find('select[name="confidence"]').val(curr_conf); // Fills input with current confidence
	});		

}

function saveSolutionForm(self)
{

	data = $(self).parent().serialize();

	$.ajax({
		type: "POST",
		url: "solution",
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
