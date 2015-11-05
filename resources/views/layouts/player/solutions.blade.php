
<div class="col-md-12 solutions">
	<table class="table solutions">
	    <thead>
	        <tr>
	            <th></th>
	            @foreach($solution_categories AS $cat)
	            	<th>{{ $cat->name }}</th>
	            @endforeach
	        </tr>
	    </thead>
	    <tbody>
	        <tr>

	            <td>You ({{ $user->player_name }})</td>

	            @foreach($solution_categories AS $cat)
	            	<td>
	            		<button type="button" class="btn btn-primary btn-sm pull-right">
  							<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Edit
						</button>
						{{-- This span holds the most recent solution for this user in this solution category --}}
						<span class="solution_container" id="sol_{{ $user->id }}_{{ $cat->id }}"></span>
						{{-- This span holds the most recent solution's confidence level for this user in this solution category --}}
						<span class="solution_container" id="conf_{{ $user->id }}_{{ $cat->id }}"></span>						
	            	</td>
	            @endforeach	   

	        </tr>

	        @foreach($players_from AS $player)

		        <tr>
		            <td>{{ $player->player_name }}</td>
		            @foreach($solution_categories AS $cat)
		            	<td>  
		            		{{-- This span holds the most recent solution for this user in this solution category --}}
							<span class="solution_container" id="sol_{{ $player->id }}_{{ $cat->id }}"></span>
							{{-- This span holds the most recent solution's confidence level for this user in this solution category --}}
							<span class="solution_container" id="conf_{{ $player->id }}_{{ $cat->id }}"></span> 
						</td>
					@endforeach		         
		        </tr>
		    @endforeach    

	    </tbody>
	</table>
</div>

