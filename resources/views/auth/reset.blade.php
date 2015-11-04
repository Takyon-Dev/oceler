@extends('layouts.master')
	

@section('content')
	<div class="row">
		<div class="col-md-6 col-md-offset-3">

			@if(count($errors) > 0)
				<div class="alert alert-danger">
					There were some problems creating your account:
				    <ul>
				        @foreach ($errors->all() as $error)
				            <li>{{ $error }}</li>
				        @endforeach
				    </ul>
				</div>
			@endif

			{!! Form::open(array('url' => '/password/reset', 'class' => 'form')) !!}
				{!! csrf_field() !!}
   				 <input type="hidden" name="token" value="{{ $token }}">
				<fieldset>
					<div class="form-group">
						{!! Form::label('email', 'Your email:') !!}
						{!! Form::text('email', null, array('class'=>'form-control', 'required', 'placeholder'=>'Email Address')) !!}
					</div>
					<div class="form-group">
						{!! Form::label('password', 'Your password:') !!}
						{!! Form::text('password', null, array('class'=>'form-control', 'required', 'placeholder'=>'Password')) !!}
					</div>
					<div class="form-group">
						{!! Form::label('password_confirmation', 'Confirm password:') !!}
						{!! Form::text('password_confirmation', null, array('class'=>'form-control', 'required')) !!}
					</div>					
					<div class="form-group">
						{!! Form::submit('Reset Password', ['class' => 'btn btn-default'] ) !!}
					</div>
					</fieldset>
			{!! Form::close()  !!}

		</div>
	</div>

@stop
