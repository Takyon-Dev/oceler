<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
  protected $fillable = ['user_id', 'trial_id', 'round', 'factoid_id'];

  public function users()
  {
    return $this->belongsToMany('oceler\User')->withTimestamps();
  }

  public function sender()
  {
    return $this->belongsTo('oceler\User', 'user_id');
  }

  public function factoid()
  {
    return $this->belongsTo('oceler\Factoid');
  }

  public function replies()
  {
    $players_from = \Session::get('players_from_ids');


    return $this->hasMany('oceler\Reply')
                ->whereIn('user_id', $players_from)
                ->orderBy('created_at', 'ASC')
                ->with('replier');
  }

  public function sharedReplies()
  {


    return $this->hasMany('oceler\Reply')->with('replier');
  }

  public function sharedFrom()
  {
    return $this->belongsTo('oceler\Message', 'share_id')
                ->with('sender')
                ->with('sharedFrom')
                ->with('sharedReplies')
                ->with('factoid')
                ->with('users');
  }

  public function shared()
  {
    return $this->hasMany('oceler\Message', 'share_id');
  }

  public static function getNewMessages($user_id, $last_message_time)
  {
    $messages = [];
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
      if($msg->user_id == $user_id){
        $messages[] = $msg;
      }
      else {
        foreach($msg->users as $recipient){
          if($recipient->id == $user_id){
            $messages[] = $msg;
          }
        }
      }

    }

    return $messages;
  }

}
