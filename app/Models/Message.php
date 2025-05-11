<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

  protected $fillable = ['user_id', 'trial_id', 'round', 'factoid_id'];

  public function users(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->belongsToMany('App\Models\User')->withTimestamps();
  }

  public function sender(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->belongsTo('App\Models\User', 'user_id');
  }

  public function factoid(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->belongsTo('App\Models\Factoid');
  }

  public function replies(): \Illuminate\Database\Eloquent\Relations$1 {
    $players_from = \Session::get('players_from_ids');


    return $this->hasMany('App\Models\Reply')
                ->whereIn('user_id', $players_from)
                ->orderBy('created_at', 'ASC')
                ->with('replier');
  }

  public function sharedReplies(): \Illuminate\Database\Eloquent\Relations$1 {


    return $this->hasMany('App\Models\Reply')->with('replier');
  }

  public function sharedFrom(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->belongsTo('App\Models\Message', 'share_id')
                ->with('sender')
                ->with('sharedFrom')
                ->with('sharedReplies')
                ->with('factoid')
                ->with('users');
  }

  public function shared(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->hasMany('App\Models\Message', 'share_id');
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
