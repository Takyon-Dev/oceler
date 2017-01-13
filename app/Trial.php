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

    public function logConfig()
    {

      $config = "\n================================================\nTrial Config:\n";
      $config .= "Name: " . $this->name . "\n";
      $config .= "Dist. Interval: " .$this->distribution_interval . "\n";
      $config .= "Num Waves: " .$this->num_waves. "\n";
      $config .= "Num Players: " .$this->num_players. "\n";
      $config .= "Unique Factoids: " .$this->unique_factoids. "\n";
      $config .= "Pay Correct Answers: " .$this->pay_correct. "\n";
      $config .= "Pay Time Factor: " .$this->pay_time_factor. "\n";
      $config .= "Pay Per Solution: " .$this->payment_per_solution. "\n";
      $config .= "Base Payment: " .$this->payment_base. "\n";
      $config .= "Num Rounds: " .$this->num_rounds. "\n";
      $config .= "Num Groups: " .$this->num_groups. "\n";
      $config .= "Networks:\n\n";

      $groups = \oceler\Group::where('trial_id', $this->id)
                              ->orderBy('group', 'ASC')
                              ->get();

      foreach ($groups as $group) {

        $config .= "Group " .$group->group.":\n";
        $config .= \oceler\Network::getAdjacencyMatrix($group->network_id);
        $config .= "\n";
      }

      $config .= "\n================================================\n";

      \oceler\Log::trialLog($this->id, $config);

    }

}
