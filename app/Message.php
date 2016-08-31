<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
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
    $players_from = \Session::get('players_from');
    $players_from[] = \Auth::user();

    return $this->hasMany('oceler\Reply')
                ->whereIn('user_id', $players_from)
                ->with('replier');
  }

  public function sharedFrom()
  {
    return $this->belongsTo('oceler\Message', 'share_id')
                ->with('sender')
                ->with('replies')
                ->with('factoid');
  }

  public function shared()
  {
    return $this->hasMany('oceler\Message', 'share_id');
  }

}
