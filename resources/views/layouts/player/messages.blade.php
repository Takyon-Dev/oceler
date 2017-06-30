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
							<div class="col-md-6">
								<div class="form-group">
									<textarea id="message" name="message" rows="6" cols="14"></textarea>
								</div>
							</div>
							<input type="hidden" id="factoid_id" name="factoid_id" value="">
							<input type="hidden" id="share_id" name="share_id" value="">
							<input type="hidden" id="_token" name="_token" value="<?php echo csrf_token(); ?>">
							<div class="col-md-6 center-block share-to">
									<h3>share to:</h3>
									<p class="text-center">
										<small>
											@if(count($players_to) == 0)
												No players available
											@else
												<a href="" id="share_check_all">
												check all
												</a>
											@endif
										</small>
									</p>
									<div class="form-group">
										@foreach($players_to AS $player)
											<div>
												<input name="share_to[]" type="checkbox" class="share-name" value="{{ $player->id }}">
												<label for="share_to" class="share-name">{{ $player->player_name }}</label>
											</div>
										@endforeach
									</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-9">
								<div id="share_box" class="bg-info"></div>
							</div>
							<div class="col-md-3 pull-right">
								<button type="button" id="msg_send"
									class="btn btn-primary pull-right
										{{ (count($players_to) == 0) ? ' disabled' : ''  }}
									">
									SEND
								</button>
								<button type="button" id="msg_cancel" class="btn btn-primary pull-right">CANCEL</button>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12" id="msg_feed">
				<h2>
					MESSAGE FEED
					<span class="msg-alert collapse">
						New Message!
						<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>
					</span>
				</h2>
				<div id="messages" class="pre-scrollable">

				</div>
			</div>
		</div>
	</div>
</div>
