$(document).ready(function() {


	$("#do_search").click(function(event){
		doSearch( $("#search_term").val(), user_id);
		event.preventDefault();
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
	$("#curr_result > div").prependTo($("#past_results"));
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

	// Add share link if search returned a result
	if(result.success){
		var share_link = $('<a>', {id: result.factoid_id});
		$(share_link).html('share');

		// If there are players available to share to
		// add the click functionality
		if(num_players_to != 0){
			$(share_link).bind('click', function(){
					$("#msg_form #share_box").html($(this).siblings(".result").html());
					$("#msg_form #share_box").show();
					$("#msg_form #factoid_id").val($(this).attr('id'));
				});
		}
		// Otherwise, disable the link
		else {
			$(share_link).attr('class', 'disabled');
		}
	}

	else {
		var share_link = '';
	}

	var result_container = $('<div class="search-result"></div>');
	var search_term = $('<span class="search-term text-muted">' + result.search_term + '</span>');
	var results = $('<span class="result">' + result.result + '</span>');


	return $(result_container).prepend(search_term, results).append($(share_link));

}
