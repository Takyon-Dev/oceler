<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Console\Output\ConsoleOutput;
use Faker\Factory as Faker;
use oceler\User;
use Log;

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
        $this->trial_initilaized = false;
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
          while(!$this->trial_initilaized) {
            $status = $this->instructionStatus($this->trial->id, $this->id);
            if($status == 'ready') {
              $this->trial_initilaized = true;
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

    public function getResult() {
      return 'SIM_RESULT:: '.$this->id.' : '.$this->result;
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

        $NUM_CYCLES = 36; // a cycle is 5 seconds
        $NUM_ACTIVE_TRIALS = [2];
        $NUM_USERS = 5;
        $TRIAL_TEMPLATES = [
          [
            'trial_type' = 1,
            'num_players' = 1,
            'num_to_recruit' = 1,
            'rounds' => [
              'factoidset' => 1,
              'nameset' => 1,
              ] ,
            'groups' => [
              'network_id' => 7
            ],
          ],
        ];

        // Generate some users
        for($i = 0; $i < $NUM_USERS; $i++) {
          $u = new User;
          $u->name = $faker->name;
          $u->mturk_id = $faker->ean13;
          $u->email = $faker->email;
          $u->password = Hash::make('oceler');
          $u->role_id = 3;
          $u->save();
          $config = [
            'can_ping' => (rand(1, 10) > 2) ? true : false,
            'reads_instructions' => (rand(1, 10) > 4) ? true : false,
            'instruction_delay' => rand(1, 30);
            'queue_delay' => rand(0, 60);
            'leaves_queue' => (rand(1, 10) > 3) ? true : false;
            'leaves_queue_after' => rand(1, 30);
            'leaves_trial' => (rand(1, 10) > 2) ? true : false;
            'leaves_trial_after' => rand(1, 30);


          ];
          $users[] = ['user' => $u, 'config' => $config];
        }

        // Generate some trials, groups and rounds
        foreach($NUM_ACTIVE_TRIALS as $key => $numTrials) {
          for($i = 0; $i < $numTrials; $i++) {
            $trial = new oceler\Trial;
            $trial->name = 'SIMULATION_TRIAL_'.$key.'_'.$i;
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
              $r->nameset = $round['nameset'];
              $r->save();
            }
          }
        }

        foreach($users as $u) {
          $threads[] = new AsyncUser($u['user']->id, $u['config']);
        }

        foreach($threads as $t) {
          $t->start();
        }

        while($curr_cycle < $NUM_CYCLES) {
          $curr_cycle++;
          $this->visit('/manage-queue');
          sleep(5);
        }

        foreach($threads as $t) {
          $r = $t->getResult();
          $output->writeln($r);
        }

    }
}
