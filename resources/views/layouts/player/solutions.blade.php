
<div class="col-md-12 solutions">
	<table class="table solutions">
	    <thead>
	        <tr>
	            <th></th>
	            @foreach($solution_categories AS $cat)
	            	<th>{{ $cat['name'] }}</th>
	            @endforeach
	        </tr>
	    </thead>
	    <tbody>
	        <tr>

	            <td>You ({{ $user->player_name }})</td>

	            @foreach($solution_categories AS $cat)
	            	<td>
	            		<button type="button" class="sol-edit btn btn-primary btn-sm pull-right">
  							<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Edit
						</button>
						{{-- This span holds the most recent solution for this user in this solution category --}}
						<span class="solution-container sol" id="sol_{{ $user->id }}_{{ $cat['id'] }}"></span>
						{{-- This span holds the most recent solution's confidence level for this user in this solution category --}}
						<span class="solution-container conf pull-right" id="conf_{{ $user->id }}_{{ $cat['id'] }}"></span>
						<form class="solution-form">
							<label for="solution">{{ $cat['name'] }}:</label>

							@if(strtolower($cat['name']) == 'when')
								<br>
								{!! Form::select('month', $months) !!}
								{!! Form::selectRange('day', 1, 31) !!}
								<br>
								{!! Form::selectRange('hour', 1, 12) !!} :
								{!! Form::select('min', $minutes) !!}
								{!! Form::select('ampm', array('AM'=>'AM', 'PM'=>'PM')) !!}
								<br>
							@else
								<input type="text" name="solution" value=""><br>
							@endif
							<label for="confidence">Confidence:</label>
							<select name="confidence">
								<option value="">----</option>
								@for($i=10; $i <= 100; $i += 10)
									<option value="{{$i}}">{{$i}}%</option>;
								@endfor
							</select><br>
							<span class="err_msg"></span>
							<input type="hidden" name="category_id" value="{{ $cat['id'] }}">
							<input type="hidden" name="_token" value="{{ csrf_token() }}">
							{{--<input type="hidden" name="u_id" value="{{ $user->id }}">--}}
							<input class="sol-save btn btn-primary btn-sm pull-right" type="submit" name="solve_save" value="SAVE">
							<input class="sol-cancel btn btn-primary btn-sm " type="submit" name="solve_cancel" value="CANCEL">
						</form>
	            	</td>
	            @endforeach

	        </tr>

	        @foreach($players_from AS $player)

		        <tr>
		            <td>{{ $player->player_name }}</td>
		            @foreach($solution_categories AS $cat)
		            	<td>
		            		{{-- This span holds the most recent solution for this user in this solution category --}}
							<span class="solution-container" id="sol_{{ $player->id }}_{{ $cat->id }}"></span>
							{{-- This span holds the most recent solution's confidence level for this user in this solution category --}}
							<span class="solution-container" id="conf_{{ $player->id }}_{{ $cat->id }}"></span>
						</td>
					@endforeach
		        </tr>
		    @endforeach

	    </tbody>
	</table>
</div>
