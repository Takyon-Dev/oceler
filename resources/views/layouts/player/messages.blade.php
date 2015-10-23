<div class="col-md-6 messaging">
	
	<h1>MESSAGING</h1>
	<div id="messaging_container">
		<div class="row">
			<div id="composer">
				
				{!! Form::open(array('class' => 'form')) !!}
					<fieldset>
						<h2>Compose</h2>
						<p id="share_container"></p>
						<div class="row">
							<div class="col-md-10">
								<div class="form-group">
									{!! Form::textarea('msg_composer', null, ['size' => '14x8']) !!}
								</div>
							</div>
							<div class="col-md-2 share-to">
								<h3>share to:</h3>
								<div class="form-group">
									{!! Form::label('share_to_1', 'Harley', ['class' => 'share-name']) !!}
									{!! Form::label('share_to_2', 'Casey', ['class' => 'share-name']) !!}									
									{!! Form::label('share_to_3', 'Dakota', ['class' => 'share-name']) !!}									
									{!! Form::label('share_to_4', 'Jordan', ['class' => 'share-name']) !!}
									<br>
									{!! Form::checkbox('share_to_1', 1, null, ['class' => 'share-name']) !!}
									{!! Form::checkbox('share_to_2', 2, null, ['class' => 'share-name']) !!}
									{!! Form::checkbox('share_to_3', 3, null, ['class' => 'share-name']) !!}
									{!! Form::checkbox('share_to_4', 4, null, ['class' => 'share-name']) !!}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 pull-right">
								<button type="button" id="msg_send" class="btn btn-primary pull-right">SEND</button>
								<button type="button" id="msg_cancel" class="btn btn-primary pull-right">CANCEL</button>
							</div>
						</div>	
					</fieldset>
				{!! Form::close()  !!}	
			</div>	
		</div>
	</div>
</div>

