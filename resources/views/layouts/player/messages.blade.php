<div class="col-md-6 messaging">

	<h1>MESSAGING</h1>
	<div id="messaging_container">
		<div class="row">
			<div id="composer">

				<form id="msg_form">
					<fieldset>
						<h2>Compose</h2>
						<p id="share_container"></p>
						<div class="row">
							<div class="col-md-10">
								<div class="form-group">
									<textarea id="message" name="message" rows="6" cols="14"></textarea>
								</div>
							</div>
							<input type="hidden" name="factoid_id" value="">
							<input type="hidden" name="thread_id" value="">
							<input type="hidden" name="reply_to_id" value="">
							<input type="hidden" name="via_id" value="">
							<div class="col-md-2 share-to">
								<h3>share to:</h3>
								<div class="form-group">
									@foreach($players_to AS $player)
										<label for="share_to" class="share-name">{{ $player->player_name }}</label>
									@endforeach
									<br>
									@foreach($players_to AS $player)
										<input name="share_to" type="checkbox" class="share-name" value="{{ $player->id }}">
									@endforeach
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
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12" id="msg_feed">
				<h2>MESSAGE FEED</h2>
				<div id="messages">
				</div>
			</div>
		</div>
	</div>
</div>
