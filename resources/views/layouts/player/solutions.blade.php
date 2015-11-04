
<div class="col-md-12 solutions">

	@foreach($users AS $user)
		{{ $user->name }}<br>
	@endforeach	

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
	            <td>You (Harley) - {{ $id }}</td>

	            @foreach($solution_categories AS $cat)
	            	<td>
	            		<button type="button" class="btn btn-primary btn-sm pull-right">
  							<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Edit
						</button>
						{{-- This span holds the most recent solution for this user in this solution category --}}
						<span class="solution_container" id="sol_{{ $id }}_{{ $cat->id }}"></span>
						{{-- This span holds the most recent solution's confidence level for this user in this solution category --}}
						<span class="solution_container" id="conf_{{ $id }}_{{ $cat->id }}"></span>						
	            	</td>
	            @endforeach	   

	        </tr>
	        <tr>
	            <td>Casey</td>
	            <td></td>
	            <td></td>
	            <td></td>
	            <td></td>
	            <td></td>	            
	        </tr>
	        <tr>
	            <td>Dakota</td>
	            <td></td>
	            <td></td>
	            <td></td>
	            <td></td>
	            <td></td>	            
	        </tr>
	        <tr>
	            <td>Jordan</td>
	            <td></td>
	            <td></td>
	            <td></td>
	            <td></td>
	            <td></td>	            
	        </tr>
	       	 <tr>
	            <td>Riley</td>
	            <td></td>
	            <td></td>
	            <td></td>
	            <td></td>
	            <td></td>	            
	        </tr>
	    </tbody>
	</table>
</div>

