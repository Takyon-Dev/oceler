@extends('layouts.master')


@section('content')
	<div class="row">
		<div class="col-md-6 col-md-offset-3">

			@if(count($errors) > 0)
				<div class="alert alert-danger">
					There were some problems signing into your account:
				    <ul>
				        @foreach ($errors->all() as $error)
				            <li>{{ $error }}</li>
				        @endforeach
				    </ul>
				</div>
			@endif

			{!! Form::open(array('url' => '/login', 'class' => 'form')) !!}
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
						<label>
							{!! Form::checkbox('remember', 'remember') !!} Remember Me
						</label>
					</div>
					<div class="form-group">
						{!! Form::submit('Sign In', ['class' => 'btn btn-primary'] ) !!}
					</div>
					<a href ="/password/email"> Forgot Your Password? </a >
					</fieldset>
			{!! Form::close()  !!}

		</div>
	</div>

@stop
