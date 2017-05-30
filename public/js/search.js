$(document).ready(function() {


	$("#do_search").click(function(event){
		doSearch( $("#search_term").val(), user_id);
		event.preventDefault();
	});

	$(document).on('click', ".search-result a", function(event){

		$("#msg_form #share_box").html($(this).siblings(".result").html());
		$("#msg_form #share_box").show();
		$("#msg_form #factoid_id").val($(this).attr('id'));

	});

});

function doSearch()
{

	searchData = $("#search_form").serializeArray();

	searchData.push({ name: "wave", value: wave });
	searchData.push({ name: "node", value: user_node });

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

function reloadSearch()
{

	$.ajax({
		type: "GET",
		url: "/search/reload",
		success: function(results)
		{
			if(results){
				$.each(results, function(key, result)
				{
					$("#past_results").prepend(formatSearchResult(result));
				});
			}
		}
	});
}

function displaySearchResult(result)
{

	$("#curr_result").append(formatSearchResult(result));
}

function formatSearchResult(result)
{
	var share_link = (result.success) ? '<a id="' + result.factoid_id + '">share</a>' : '';
	var result_container = $('<div class="search-result"></div>');
	var search_term = $('<span class="search-term text-muted">' + result.search_term + '</span>');
	var results = $('<span class="result">' + result.result + '</span>' + share_link);

	return $(result_container).prepend(search_term, results);

}
