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

class ReplyController extends Controller
{

  public function  postReply(Request $request)
	{


    $this->validate($request, ['message' => 'required']);

		$user = Auth::user();


		$reply = new Reply;
		$reply->message = $request->message;
    $reply->message_id = $request->message_id;
		$reply->sender_id = $user->id;

		$reply->save();

    $parent_msg = Message::find($reply->message_id);
    $parent_msg->updated_at = date('Y-m-d G:i:s');
    $parent_msg->save();

	}

}
