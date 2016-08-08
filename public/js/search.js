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
			clearCurrentSearch();
			displaySearchResult(result);
			console.log(result);

		})
		.fail(function () {

		}
	);
}

function clearSearchForm()
{
	$("#search_term").val('');
}

function clearCurrentSearch()
{
	$("#past_results").prepend($("#curr_result").html());
	$("#curr_result").empty();
}

function displaySearchResult(result)
{
	var result_container = $('<div class="search-result"></div>');
	var search_term = $('<span class="search-term">' + result.search_term + '</span>');
	var results = $('<span class="result">' + result.result + '</span>');

	$(result_container).append(search_term, results);
	$("#curr_result").append(result_container);
}
