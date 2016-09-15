<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SoftDeletingTrait;

class Trial extends Model
{
    use SoftDeletes;
    protected $table = 'trials';
    public $timestamps = true;
    protected $dates = ['deleted_at'];

    public function rounds() {
      return $this->hasMany('oceler\Round');
    }

    public function users() {
      return $this->belongsToMany('oceler\User')->withPivot('group_id')->withTimestamps();
    }

    public function solutions() {
      return $this->hasMany('oceler\Solution');
    }

    public function groups() {
      return $this->hasMany('\oceler\Group');
    }

    public function stopTrial() {

      $trial_users = \DB::table('trial_user')
                      ->where('trial_id', $this->id)
                      ->get();

      foreach ($trial_users as $trial_user) {

          \DB::table('trial_user_archive')->insert([

            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'trial_id' => $trial_user->trial_id,
            'user_id' => $trial_user->user_id,
            'group_id' => $trial_user->group_id,
          ]);

          \DB::table('trial_user')->where('id', $trial_user->id)->delete();
      }
    }

    public static function removePlayerFromTrial($id)
    {
      $trial_user = \DB::table('trial_user')
                      ->where('user_id', $id)
                      ->first();

      \DB::table('trial_user_archive')->insert([

        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
        'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
        'trial_id' => $trial_user->trial_id,
        'user_id' => \Auth::id(),
        'group_id' => $trial_user->group_id,
      ]);

      \DB::table('trial_user')->where('id', $trial_user->id)->delete();
    }

}
