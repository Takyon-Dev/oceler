$(document).ready(function() {


	$(document).on('click', '#do_search' , function(event) {

		doSearch();
		event.preventDefault();
	});

});

function doSearch()
{
  alert($("#search_form").serialize());
}
