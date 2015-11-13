<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;
use Auth;
use oceler\Message;

class MessageController extends Controller
{


    /**
    * Stores a new message to the messages table in the DB
    *
    */
	public function  postMessage(Request $request)
	{


      $this->validate($request, ['message' => 'required_unless:factoid_id']);

		$user = Auth::user();

		$msg = new Message;
		$msg->thread_id = $request->thread_id;
		$msg->via_id = $request->via_id;
		$msg->reply_to_id = $request->reply_to_id;
		$msg->factoid_id = $request->factoid_id;
		$msg->message = $request->message;
		$msg->sender_id = $user->id;
		
		$msg->save();

		$share_to = $request->share_to;

		foreach ($share_to as $player) {
			// Add each player to message recipients table (should be called messages_user?)
		}
	}


}
