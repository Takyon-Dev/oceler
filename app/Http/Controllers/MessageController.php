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

    if($factoid = \oceler\Factoid::find($request->factoid_id)){
      $log .= ' WITH FACTOID-- '.$factoid->factoid;
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

  public function getListenSystemMessage(Request $request)
	{
    // Get all factoids set for distribution to the player's
    // assigned network node during this wave
    $factoids = \DB::table('factoid_distributions')
                    ->join('factoids', 'factoid_distributions.factoid_id', '=', 'factoids.id')
                    ->where('factoid_distributions.factoidset_id', \Input::get('factoidset_id'))
                    ->where('node', \Input::get('node'))
                    ->where('wave', \Input::get('wave'))
                    ->get();

    foreach ($factoids as $factoid) {

      // Create a new message containing the factoid
      // and set it's sender to be the 'System' account
      $sys_msg = new Message();
      $sys_msg->user_id = \oceler\User::where('player_name', 'System')
                              ->value('id');
      $sys_msg->trial_id = \Session::get('trial_id');
      $sys_msg->round = \Session::get('curr_round');
      $sys_msg->factoid_id = $factoid->factoid_id;
      $sys_msg->save();

      // Add the user to as a recipient
      $sys_msg->users()->attach(Auth::user()->id);

      // And log it
      $log = 'DISTRIBUTED FACTOID TO: ' .Auth::user()->id . "(". Auth::user()->player_name .") ";
      $log .= ' FACTOID-- '.$factoid->factoid;
      \oceler\Log::trialLog(\Session::get('trial_id'), $log);
    }

  }
}
