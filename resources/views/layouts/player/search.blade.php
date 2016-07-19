
		<div class="col-md-6 search">
			<h1>SEARCH</h1>
			<div id="search_container">
				<form id="search_form">
					<fieldset class="form-group">
						<input type="text" id="search_term" name="search_term">
						<input type="hidden" id="_token" name="_token" value="<?php echo csrf_token(); ?>">
						<button type="button" id="do_search" class="btn btn-primary pull-right">SEARCH</button>
					</fieldset>
				</form>
				<div id="curr_result"></div>
				<div id="past_results"></div>
			</div>
		</div>
