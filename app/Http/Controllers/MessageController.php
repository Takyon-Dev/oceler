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


    //$this->validate($request, ['message' => 'required_unless:factoid_id']);

		$user = Auth::user();

		$msg = new Message;
		$msg->thread_id = $request->thread_id;
		$msg->factoid_id = $request->factoid_id;
		$msg->message = $request->message;
		$msg->sender_id = $user->id;

		$msg->save();

		foreach ($request->share_to as $player) {
			// Add each player to message_user
			$msg->users()->attach($player);
		}
	}


}
