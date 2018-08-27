<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Console\Output\ConsoleOutput;
use Faker\Factory as Faker;
use oceler\User;

class SimulatedUser {

  public function __construct($ocelerUser, $config) {
      $this->id = $ocelerUser->id;
      $this->ocelerUser = $ocelerUser;
      $this->config = $config;
      $this->queueInstance = null;
      $this->in_trial = false;
      $this->queue_cycles = ($config['leaves_queue']) ? $config['leaves_queue_after'] : 120;
      $this->trial_cycles = $config['leaves_trial_after'];
      $this->trial = null;
      $this->instructions_read = false;
      $this->instructions_cycles = $config['instructions_delay'];
      $this->trial_initialized = false;
      $this->completedSimulation = false;
      $this->result = null;
  }

  public function getResult() {
    return 'SIM_RESULT:: '.$this->id.' : '.$this->result;
  }

  public function instructionStatus($trial_id) {

    $trial = oceler\Trial::with('users')->find($trial_id);

    $num_read = 0;

    $INACTIVE_PING_TIME = 20;
    $dt = \Carbon\Carbon::now();

    $hasReadInstructions = false;

    foreach ($trial->users as $user) {
      if($user->id == $this->id) {
        $hasReadInstructions = ($user->pivot->instructions_read == true) ? true : false;
        if($user->pivot->selected_for_removal == 1) {
          return 'remove';
        }
      }
      if(($user->pivot->instructions_read == true) &&
         (!$user->pivot->selected_for_removal) &&
         ($user->pivot->last_ping > $dt->subSeconds($INACTIVE_PING_TIME))) {
          $num_read++;
        }

    }


    if($num_read >= $trial->num_players) {
      if($hasReadInstructions) {
        return 'ready';
      }
      else {
        return 'remove';
      }
    }
    else {
      return 'num_completed: '.$num_read.' num_needed: '.$trial->num_players;
    }
  }
}
/*
class AsyncUser extends Thread {

    public function __construct($id, $config) {
        $this->id = $id;
        $this->config = $config;
        $this->queueInstance = null;
        $this->in_trial = false;
        $this->queue_cycles = $config['leaves_queue'] ? $config['leaves_queue_after'] : 60;
        $this->trial_cycles = $config['leaves_trial_after'];
        $this->trial = null;
        $this->instructions_read = false;
        $this->instructions_cycles = $config['instructions_delay'];
        $this->trial_initialized = false;
        $this->result = null;
    }

    public function run() {

        sleep($this->config['queue_delay']);
        $this->queueInstance = new oceler\Queue;
        $this->queueInstance->user_id = $this->id;
        $this->queueInstance->trial_type = 1;
        $this->queueInstance->created_at = \Carbon\Carbon::now()->toDateTimeString();
        $this->queueInstance->updated_at = \Carbon\Carbon::now()->toDateTimeString();
        $this->queueInstance->save();
        Log::info('SIM:: USER ID '.$id.' entered the queue');

        while(!$this->in_trial){
          $this->queue_cycles--;

          if($this->queue_cycles <= 0){
            Log::info('SIM:: USER ID '.$id.' left the queue');
            if(!$this->config['leaves_queue']) {
              Log::info('SIM:: USER ID '.$id.' timed out of the queue');
              $this->result = 'Timed out of queue';
            }
            else {
              $this->result = 'left queue voluntarily';
            }
            return;
          }

          $trial = \DB::table('trial_user')
                      ->where('user_id', $this->id)
                      ->value('trial_id');

          if($trial) {
            $this->in_trial = true;
            $this->trial = oceler\Trial::find($trial)->with('users');
            Log::info('SIM:: USER ID '.$id.' is in trial '.$trial);
          }
          else {
            if($this->config['can_ping']){
              $this->queueInstance->updated_at = \Carbon\Carbon::now()->toDateTimeString();
              $this->queueInstance->save();
            }
            sleep(2);
          }
        }

        if($this->trial) {
          while(!$this->instructions_read) {
            $this->instructions_cycles--;
            if($this->config['reads_instructions']) {
              if($this->instructions_cycles > 0) {
                if($this->config['can_ping']) {
                  \DB::update('update trial_user set last_ping = ? where user_id = ?', [\Carbon\Carbon::now()->toDateTimeString(), $this->id]);
                }
              }
              else {
                \DB::update('update trial_user set instructions_read = 1 where user_id = ?', [$this->id]);
                Log::info("SIM:: USER ID ". $this->id ." marked instructions as read");
                $this->instructions_read = true;
              }
            }
            else {
              if($this->instructions_cycles <= 0) {
                $this->result = 'Left the instructions page';
                return;
              }
            }
            sleep(2);
          }
          while(!$this->trial_initialized) {
            $status = $this->instructionStatus($this->trial->id, $this->id);
            if($status == 'ready') {
              $this->trial_initialized = true;
              $this->result = 'Initialized trial';
            }
            else if($status == 'remove') {
              Log::info("SIM:: USER ID ". $this->id ." selected for removal");
              $this->result = 'Selected for removal';
              return;
            }
            else {
              Log::info("SIM:: USER ID ". $this->id ." instruction status ".$status);
            }
          }
          sleep(2);
        }
    }

    private function instructionStatus($trial_id, $user_id) {
      $trial = Trial::with('users')->find($trial_id);

      $num_read = 0;

      $INACTIVE_PING_TIME = 20;
      $dt = \Carbon\Carbon::now();

      $hasReadInstructions = false;

      foreach ($trial->users as $user) {
        if($user->id == $user_id) {
          $hasReadInstructions = ($user->pivot->instructions_read == true) ? true : false;
          if($user->pivot->selected_for_removal == 1) {
            return 'remove';
          }
        }
        if(($user->pivot->instructions_read == true) &&
           (!$user->pivot->selected_for_removal) &&
           ($user->pivot->last_ping > $dt->subSeconds($INACTIVE_PING_TIME))) {
            $num_read++;
          }

      }


      if($num_read >= $trial->num_players) {
        if($hasReadInstructions) {
          return 'ready';
        }
        else {
          return 'remove';
        }
      }
      else {
        return 'num_completed: '.$num_read.' num_needed: '.$trial->num_players;
      }
    }
}
*/

class OverrecruitTest extends TestCase
{
    /**
     * Simulates placing users many into several open trials using the
     * over-recruit feature.
     *
     * @return void
     */
    public function testSimulateOverrecruitment()
    {
        $output = new ConsoleOutput();
        $faker = Faker::create();

        $users = [];
        $trials = [];
        $threads = [];

        $curr_cycle = 0;

        $NUM_CYCLES = 300; // a cycle is 1 second
        $NUM_ACTIVE_TRIALS = [10];
        $NUM_USERS = 20;
        $TRIAL_TEMPLATES = [
          [
            'trial_type' => 1,
            'num_players' => 1,
            'num_to_recruit' => 1,
            'rounds' => [
              ['factoidset' => 1,
              'nameset' => 1,],
              ] ,
            'groups' => [
              ['network_id' => 7,],
            ],
          ],
        ];

        // Generate some users
        for($i = 0; $i < $NUM_USERS; $i++) {
          $u = new User;
          $u->name = 'SIM USER';
          $u->mturk_id = $faker->ean13;
          $u->email = $faker->email;
          $u->password = Hash::make('oceler');
          $u->role_id = 3;
          $u->save();
          $config = [
            'can_ping' => (rand(1, 10) > 2) ? true : false,
            'reads_instructions' => (rand(1, 10) > 4) ? true : false,
            'instructions_delay' => rand(1, 30),
            'queue_delay' => rand(0, 60),
            'leaves_queue' => (rand(1, 10) > 3) ? true : false,
            'leaves_queue_after' => rand(1, 30),
            'leaves_trial' => (rand(1, 10) > 2) ? true : false,
            'leaves_trial_after' => rand(1, 30),
          ];
          $users[] = ['user' => $u, 'config' => $config];
        }

        // Generate some trials, groups and rounds
        foreach($NUM_ACTIVE_TRIALS as $key => $numTrials) {
          for($i = 0; $i < $numTrials; $i++) {
            $trial = new oceler\Trial;
            $trial->name = 'SIM_TRIAL';
            $trial->trial_type = $TRIAL_TEMPLATES[$key]['trial_type'];
            $trial->num_players = $TRIAL_TEMPLATES[$key]['num_players'];
            $trial->num_to_recruit = $TRIAL_TEMPLATES[$key]['num_to_recruit'];
            $trial->is_active = 1;
            $trial->num_groups = count($TRIAL_TEMPLATES[$key]['groups']);
            $trial->save();

            $trials[] = $trial;

            foreach($TRIAL_TEMPLATES[$key]['groups'] as $gKey => $group){

              $g = new \oceler\Group;
              $g->trial_id = $trial->id;
              $g->network_id = $group['network_id'];
              $g->group = $gKey + 1;
              $g->save();
            }
            foreach($TRIAL_TEMPLATES[$key]['rounds'] as $rKey => $round){
              $r = new \oceler\Round;
              $r->trial_id = $trial->id;
              $r->round = $rKey + 1;
              $r->round_timeout = 1;
              $r->factoidset_id = $round['factoidset'];
              $r->nameset_id = $round['nameset'];
              $r->save();
            }
          }
        }



        foreach($users as $u) {
          $simUsers[] = new SimulatedUser($u['user'], $u['config']);
        }


        foreach($simUsers as $s) {
          Log::info('SIMUSER: ' . var_export($s, true));
        }

        while($curr_cycle < $NUM_CYCLES) {
          if($curr_cycle % 5 == 0) $this->visit('/manage-queue');
          $this->userSimulationTick($simUsers, $curr_cycle);
          $curr_cycle++;
          sleep(1);
          flush();
        }

        foreach($simUsers as $sim) {
          $r = $sim->getResult();
          $output->writeln($r);
          Log::info($r);
        }

        Log::info('Simulation complete. Cleaning up.');

        \DB::table('users')->where('name', 'SIM USER')->delete();
        \DB::table('trials')->where('name', 'SIM_TRIAL')->delete();

        Log::info('Done');


    }

    private function userSimulationTick($users, $curr_cycle) {
      foreach($users as $u) {
        // If this user's simulation has ended, skip them
        if($u->completedSimulation) continue;
        // If this user is not in the queue yet, and the queue delay has passed
        if(!$u->in_trial && !$u->queueInstance && $curr_cycle > $u->config['queue_delay']) {

          $u->queueInstance = new oceler\Queue;
          $u->queueInstance->user_id = $u->id;
          $u->queueInstance->trial_type = 1;
          $u->queueInstance->created_at = \Carbon\Carbon::now()->toDateTimeString();
          $u->queueInstance->updated_at = \Carbon\Carbon::now()->toDateTimeString();
          $u->queueInstance->save();
          Log::info('SIM:: USER ID '.$u->id.' entered the queue');

        }

        if($u->queueInstance && !$u->in_trial){
          if($u->queue_cycles <= 0){
            Log::info('SIM:: USER ID '.$u->id.' left the queue');
            if(!$u->config['leaves_queue']) {
              Log::info('SIM:: USER ID '.$u->id.' timed out of the queue');
              $u->result = 'Timed out of queue';
            }
            else {
              $u->result = 'left queue voluntarily';
            }
            $u->completedSimulation = true;
            continue;
          }

          $trial = \DB::table('trial_user')
                      ->where('user_id', $u->id)
                      ->pluck('trial_id');

          if($trial) {
            $u->in_trial = true;
            $u->trial = oceler\Trial::with('users')->find($trial);
            Log::info('SIM:: USER ID '.$u->id.' is in trial '.$trial);
            continue;
          }
          else {
            if($u->queueInstance && $u->config['can_ping']){
              $u->queueInstance->updated_at = \Carbon\Carbon::now()->toDateTimeString();
              $u->queueInstance->save();
            }
          }
          $u->queue_cycles--;
        }

        if($u->trial) {
          if(!$u->instructions_read) {
            if($u->config['reads_instructions']) {
              if($u->instructions_cycles > 0) {
                if($u->config['can_ping']) {
                  \DB::update('update trial_user set last_ping = ? where user_id = ?', [\Carbon\Carbon::now()->toDateTimeString(), $u->id]);
                }
              }
              else {
                \DB::update('update trial_user set instructions_read = 1 where user_id = ?', [$u->id]);
                Log::info("SIM:: USER ID ". $u->id ." marked instructions as read");
                $u->instructions_read = true;
              }
            }
            else {
              if($u->instructions_cycles <= 0) {
                $u->result = 'Left the instructions page for trial '.$u->trial->id;
                $u->completedSimulation = true;
                continue;
              }
            }
            $u->instructions_cycles--;
            continue;
          }
          if(!$u->trial_initialized) {
            if($u->config['can_ping']) {
              \DB::update('update trial_user set last_ping = ? where user_id = ?', [\Carbon\Carbon::now()->toDateTimeString(), $u->id]);
            }

            $status = $u->instructionStatus($u->trial->id);
            if($status == 'ready') {
              $u->trial_initialized = true;
              $u->result = 'Initialized trial '.$u->trial->id;
              Log::info("SIM:: USER ID ". $u->id ." initialized trial ".$u->trial->id);
            }
            else if($status == 'remove') {
              Log::info("SIM:: USER ID ". $u->id ." selected for removal");
              $u->result = 'Selected for removal';
              $u->completedSimulation = true;
              continue ;
            }
          }
        }
      }
    }
}
