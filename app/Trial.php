<?php

namespace oceler;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SoftDeletingTrait;
use DB;

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
      return $this->belongsToMany('oceler\User')
                  ->withPivot('group_id', 'instructions_read', 'last_ping')
                  ->withTimestamps();
    }

    public function solutions() {
      return $this->hasMany('oceler\Solution');
    }

    public function groups() {
      return $this->hasMany('\oceler\Group');
    }

    public function archive() {
      return $this->belongsToMany('oceler\User', 'trial_user_archive', 'trial_id', 'user_id');
    }

    public function storeTrialConfig($request)
    {

      $this->name = $request->name;
      $this->trial_type = $request->trial_type;
      $this->passing_score = $request->passing_score;
      $this->instructions = $request->instructions;
      $this->distribution_interval = $request->distribution_interval;
      $this->num_waves = $request->num_waves;
      $this->num_players = $request->num_players;
      $this->unique_factoids = $request->unique_factoids || 0;
      $this->pay_correct = $request->pay_correct || 0;
      $this->pay_time_factor = $request->pay_time_factor || 0;
      $this->payment_per_solution = $request->payment_per_solution;
      $this->payment_base = $request->payment_base;
      $this->num_rounds = $request->num_rounds;
      $this->num_groups = $request->num_groups;
      $this->is_active = false;

      try{
        $this->save(); // Saves the trial to the trial table
      }

      catch(\Exception $e){
        throw $e;
      }

      if($request->hasFile('instructions_image')){

        $img = $request->file('instructions_image');
        $img_name = $img->getClientOriginalName();
        $img_storage_path = "/uploads/trial-images/".$this->id;
        $img->move(public_path().$img_storage_path, $img_name);
        $this->instr_img_path = $img_storage_path."/".$img_name;
        $this->save();
      }

      /*
       * For each trial round (set in the config), the trial timeout,
       * factoidsets, countrysets, and namesets (selected in the config)
       * are stored in the rounds table.
       */
      for($i = 0; $i < $this->num_rounds; $i++){

        if(!is_numeric($request->factoidset_id[$i])){
          $factoidset_id = DB::table('factoidsets')
                              ->where('name', '=', $request->factoidset_id[$i])
                              ->orderBy('id', 'DESC')
                              ->pluck('id');

        }

        else $factoidset_id = $request->factoidset_id[$i];

        dump($factoidset_id);

        if(!is_numeric($request->nameset_id[$i])){
          $nameset_id = DB::table('namesets')
                          ->where('name', '=', $request->nameset_id[$i])
                          ->orderBy('id', 'DESC')
                          ->pluck('id');
        }

        else $nameset_id = $request->nameset_id[$i];

        DB::table('rounds')->insert([
            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'trial_id' => $this->id,
            'round' => ($i + 1),
            'round_timeout' => $request->round_timeout[$i],
            'factoidset_id' => $factoidset_id,
            'nameset_id' => $nameset_id,
            ]);
      }

      /*
       *	For each group (set in the config) store the group's
       *	network and end-of-experiment survey URL
       */
      for($i = 0; $i < $this->num_groups; $i++){

        if(!is_numeric($request->network_id[$i])){
          $network_id = DB::table('networks')
                                          ->where('name', '=', $request->network_id[$i])
                                          ->orderBy('id', 'DESC')
                                          ->pluck('id');
        }

        else $network_id = $request->network_id[$i];

        DB::table('groups')->insert([
          'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
          'group' => $i + 1,
          'trial_id' => $this->id,
          'network_id' => $network_id,
          'survey_url' => $request->survey_url[$i],
        ]);

      }
    }

    /*
     * Adds a new trial based on a config file.
     */
    public static function addTrialFromConfig($config)
    {

      foreach ($config['trials'] as $trial_config) {

        $trial = new Trial();

        /* Create a new request and merge the
        config values to it. This way we can
        use the storeTrialConfig function to process
        trials created via config file or webpage form. */
        $request = new Request();
        $request->merge($trial_config);

        try{
          $trial->storeTrialConfig($request);
          $trial->logConfig();
        }
        catch(\Exception $e){
          throw $e;
        }
      }


    }

    public function stopTrial() {

      $this->is_active = 0;
      $this->save();

      $this_users = \DB::table('trial_user')
                      ->where('trial_id', $this->id)
                      ->get();

      foreach ($this_users as $this_user) {
          Trial::removePlayerFromTrial($this_user->user_id, false, false);
      }
    }

    public function removePlayerFromTrial($user_id, $completed_trial, $passed_trial)
    {
      $this_user = \DB::table('trial_user')
                      ->where('user_id', $user_id)
                      ->first();

      \DB::table('trial_user_archive')->insert([

        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
        'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
        'trial_id' => $this->id,
        'user_id' => $user_id,
        'trial_type' => $this->trial_type,
        'group_id' => $this_user->group_id,
        'last_ping' => $this_user->last_ping,
        'completed_trial' => $completed_trial,
        'trial_passed' => $passed_trial
      ]);

      \DB::table('trial_user')->where('id', $this_user->id)->delete();

      if(\DB::table('trial_user')
            ->where('trial_id', $this->id)
            ->count() == 0){
        $this->is_active = 0;
        $this->save();
      }
    }

    public function logConfig()
    {


      $config = "\n================================================\nTrial Config:\n";
      $config .= "Name: " . $this->name . "\n";
      $config .= "Type: " . $this->trial_type . "\n";
      $config .= "Dist. Interval: " .$this->distribution_interval . "\n";
      $config .= "Num Waves: " .$this->num_waves. "\n";
      $config .= "Num Players: " .$this->num_players. "\n";
      $config .= "Unique Factoids: " .$this->unique_factoids. "\n";
      $config .= "Pay Correct Answers: " .$this->pay_correct. "\n";
      $config .= "Pay Time Factor: " .$this->pay_time_factor. "\n";
      $config .= "Pay Per Solution: " .$this->payment_per_solution. "\n";
      $config .= "Base Payment: " .$this->payment_base. "\n";
      $config .= "Num Rounds: " .$this->num_rounds. "\n";

      $rounds = \oceler\Round::where('trial_id', $this->id)
                              ->orderBy('round', 'ASC')
                              ->get();
      dump($rounds);
      exit;
      foreach ($rounds as $round) {

        $factoidset = \oceler\Factoidset::where('id', $round->factoidset_id)->first();
        $nameset = \oceler\Nameset::where('id', $round->nameset_id)->first();
        $config .= "Round ".$round->round." :\n";
        $config .= "Factoid set: ".$factoidset->name."\n";
        $config .+ "Name set: ".$nameset->name."\n";
      }

      $config .= "Num Groups: " .$this->num_groups. "\n";
      $config .= "Networks:\n\n";

      $groups = \oceler\Group::where('trial_id', $this->id)
                              ->orderBy('group', 'ASC')
                              ->with('network')
                              ->get();

      foreach ($groups as $group) {

        $network = \DB::table('networks')->where('id', '=', $group->network_id)->first();
        $config .= "Group " .$group->group.", Network ".$network->name.":\n";
        $config .= \oceler\Network::getAdjacencyMatrix($group->network_id);
        $config .= "\n";
      }

      $config .= "\n================================================\n";

      \oceler\Log::trialLog($this->id, $config);

    }

}
