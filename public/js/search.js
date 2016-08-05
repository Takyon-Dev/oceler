$(document).ready(function() {


	$("#do_search").click(function(event){
		doSearch( $("#search_term").val(), user_id);
		event.preventDefault();
	});

});

function doSearch()
{

	searchData = $("#search_form").serialize();

	$.ajaxPrefilter(function(options, originalOptions, xhr) {
		var token = $('#_token').val();

		if(token){
			return xhr.setRequestHeader('X-CSRF-TOKEN', token);
		}
	});

	$.post(
		'/search',
		searchData,
		function (result) {
			clearSearchForm(); // On success, reset the form
			console.log(result);
		})
		.fail(function () {
			// Add fail function here
		}
	);
}

function clearSearchForm()
{
	$("#search_term").val('');
}
