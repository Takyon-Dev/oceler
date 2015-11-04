@extends('layouts.master')
	

@section('content')
	<div class="row">
		<div class="col-md-6 col-md-offset-3">

			@if(count($errors) > 0)
				<div class="alert alert-danger">
				    <ul>
				        @foreach ($errors->all() as $error)
				            <li>{{ $error }}</li>
				        @endforeach
				    </ul>
				</div>
			@endif

			{!! Form::open(array('url' => '/password/email', 'class' => 'form')) !!}
				<fieldset>
					<div class="form-group">
						{!! Form::label('email', 'Your email:') !!}
						{!! Form::text('email', null, array('class'=>'form-control', 'required', 'placeholder'=>'Email Address')) !!}
					</div>					
					<div class="form-group">
						{!! Form::submit('Send Password Reset Link', ['class' => 'btn btn-primary'] ) !!}
					</div>
					</fieldset>
			{!! Form::close()  !!}

		</div>
	</div>

@stop
