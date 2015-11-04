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

			{!! Form::open(array('url' => '/auth/register', 'class' => 'form')) !!}
				<fieldset>
					<div class="form-group">
						{!! Form::label('name', 'Your name:') !!}
						{!! Form::text('name', null, array('class'=>'form-control', 'required', 'placeholder'=>'Name')) !!}
					</div>
					<div class="form-group">
						{!! Form::label('mturk_id', 'Your MTurk ID:') !!}
						{!! Form::text('mturk_id', null, array('class'=>'form-control', 'required', 'placeholder'=>'MTurk ID')) !!}
					</div>
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
						{!! Form::submit('Create Account', ['class' => 'btn btn-primary'] ) !!}
					</div>
					</fieldset>
			{!! Form::close()  !!}

		</div>
	</div>

@stop
