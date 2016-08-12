<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;
use Auth;
use DB;
use Response;
use oceler\Message;
use oceler\Reply;

class MessageController extends Controller
{


    /**
    * Stores a new message to the messages table in the DB
    *
    */
	public function  postMessage(Request $request)
	{


    //$this->validate($request, ['message' => 'required_unless:factoid_id']);

		$user = Auth::user();

		$msg = new Message;
    $msg->trial_id = \Session::get('trial_id');
    $msg->round = \Session::get('curr_round');
		$msg->factoid_id = $request->factoid_id ?: null;
    $msg->share_id = $request->share_id ?: null;
		$msg->message = $request->message;
		$msg->user_id = $user->id;

		$msg->save();

    $log = "MESSAGE-- ID: " .$msg->id. " FROM: ". $user->id . "(". $user->player_name .") ";

		foreach ($request->share_to as $player) {
			// Add each recipient player to message_user
			$msg->users()->attach($player);
      $player = \oceler\User::find($player);
      $log .= 'TO: ' .$player->id . "(". $player->player_name .") ";

		}

    $log .= $msg->message;
    \oceler\Log::trialLog($msg->trial_id, $log);

	}

	/**
	 * Gets the most recent messages sent to the player
	 * @param  Request $request
	 */
	public function getListenMessage($last_message_time)
	{
		$user = Auth::user();

		$messages = array();

		// Get all new messages updated (or inserted)
		// ** THIS QUERY SHOULD BE MADE MORE EFFICIENT
		// SO THAT THERE IS NO NEED FOR THE FOREACH BELOW -
		// e.g. get new messages where sender or a recipient is the user

		$new_messages = Message::with('users')
										->with('sender')
										->with('replies')
                    ->with('factoid')
                    ->with('sharedFrom')
										->where('updated_at', '>', $last_message_time)
                    ->where('trial_id', \Session::get('trial_id'))
                    ->where('round', \Session::get('curr_round'))
										->get();

		foreach($new_messages as $key=>$msg){
			if($msg->user_id == $user->id){
				$messages[] = $msg;
			}
			else {
				foreach($msg->users as $recipient){
					if($recipient->id == $user->id){
						$messages[] = $msg;
					}
				}
			}

		}


		return Response::json($messages);

	}

	/*
	public function updateTime()
	{
		$dt = new DateTime;
		$this->updated_at = $dt->format('m-d-y H:i:s');
		$this->save();
	}
	*/

}
