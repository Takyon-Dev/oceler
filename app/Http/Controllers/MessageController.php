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

		/* REPLIES:
		// If this isn't a new message, but is in fact a reply to a previous message
		if($request->reply_to != 0){
			$reply = new oceler\Reply;
		}
		*/

		$msg = new Message;
		$msg->factoid_id = $request->factoid_id;
		$msg->message = $request->message;
		$msg->sender_id = $user->id;

		$msg->save();

		foreach ($request->share_to as $player) {
			// Add each player to message_user
			$msg->users()->attach($player);
		}

		// SHOULD ALSO INSERT OR UPDATE THE TIME IN THE THREADS TABLE
		// WHICH WILL ALLOW US TO GET ALL THREADS BY TIME MODIFIED
		// AND THEN ALL MESSAGES PER THREAD (BY TIME)
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
										->where('updated_at', '>', $last_message_time)
										->get();

		foreach($new_messages as $key=>$msg){
			if($msg->sender_id == $user->id){
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
