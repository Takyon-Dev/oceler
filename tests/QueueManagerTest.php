<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Console\Output\ConsoleOutput;
use Faker\Factory as Faker;
use oceler\User;

class QueueManagerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testQueueManager()
    {

        $output = new ConsoleOutput();

        $NUM_USERS = 24;
        $NUM_PLAYERS = [16, 3];
        $NUM_TO_RECRUIT = [18, ''];

        $network = new oceler\Network;
        $network->name = 'Test Network';
        $network->save();

        $faker = Faker::create();

        // Create a bunch of users
        for($i = 0; $i < $NUM_USERS; $i++) {
          $u = new User;
          $u->name = $faker->name;
          $u->mturk_id = $faker->ean13;
          $u->email = $faker->email;
          $u->password = Hash::make('oceler');
          $u->role_id = 3;
          $u->save();
          $trial_type = ($i < $NUM_USERS - 4) ? 1 : 2;
          \DB::table('queues')->insert(['user_id' => $u->id, 'trial_type' => $trial_type,
                                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),]);
        }
        $queued_players = oceler\Queue::get()->count();

        $this->assertTrue($queued_players == $NUM_USERS);
        $trials = [];

        // Create some trials
        for($i = 0; $i < 2; $i++) {
          $trial = new oceler\Trial;
          $trial->name = 'QUEUE MANAGER TEST '.$i;
          $trial->trial_type = $i + 1;
          $trial->num_players = $NUM_PLAYERS[$i] - 2;
          $trial->num_to_recruit = $NUM_TO_RECRUIT[$i];
          $trial->is_active = 1;
          $trial->num_groups = 1;
          $trial->save();

          $trials[] = $trial;

          $group = new \oceler\Group;
          $group->trial_id = $trial->id;
          $group->network_id = $network->id;
          $group->group = 1;
          $group->save();
        }

        // Run the manageQueue method, which should put players in trials
        $this->visit('/manage-queue');

        $recruited = 0;

        // See that the correct number of players were pu in the trials
        foreach($trials as $key => $trial) {
          $num_to_recruit = ($trial->num_to_recruit != '') ? $trial->num_to_recruit : $trial->num_players;
          $users_in_trial = \DB::table('trial_user')->where('trial_id', $trial->id)->count();
          $this->assertTrue($users_in_trial == $num_to_recruit);
          $recruited += $num_to_recruit;
        }

        $queued_players = oceler\Queue::get()->count();
        $this->assertTrue($queued_players == ($NUM_USERS - $recruited));


        foreach ($trials as $key => $trial) {
          // Update last_ping so the players appear to be active
          \DB::table('trial_user')->where('trial_id', $trial->id)->update(['last_ping' => \Carbon\Carbon::now()->toDateTimeString()]);
          $users_in_trial = \DB::table('trial_user')->where('trial_id', $trial->id)->get();
          $num_to_recruit = ($trial->num_to_recruit != '') ? $trial->num_to_recruit : $trial->num_players;
          $numInstructions = 0;

          // Mark the instructions as read in the first trial
          foreach ($users_in_trial as $user) {
            if($numInstructions < $num_to_recruit && $trial->trial_type < 2) {
              \DB::table('trial_user')->where('user_id', $user->user_id)->update(['instructions_read' => true]);
              $numInstructions++;
            }
          }
        }

        // Run manageQueue again - this will test the selection of players for the trial that have read the instructions
        $this->visit('/manage-queue');

        // Test that the instructions-status handler is behaving correctly
        // depending on what each player's instruction status is
        foreach ($trials as $key => $trial) {
          $users_in_trial = \DB::table('trial_user')->where('trial_id', $trial->id)->get();
          $numInstructionsRead =  \DB::table('trial_user')->where('trial_id', $trial->id)->where('instructions_read', true)->count();

          if($numInstructionsRead >= $trial->num_players) {
            $output->writeln('Instructions complete - ready for trial');
            foreach ($users_in_trial as $user) {
              $userObj = oceler\User::find($user->user_id);
              $output->writeln($user->user_id);
              if($user->instructions_read == false) {
                $output->writeln('Instructions not read');
                $this->assertTrue($user->selected_for_removal != true);
                $this->actingAs($userObj)
                     ->get('/player/trial/instructions/status/'.$trial->id)
                     ->seeJson(['status' => 'remove']);
              }

              if($user->selected_for_removal == true) {
                $output->writeln('Selected for removal');
                $this->actingAs($userObj)
                     ->get('/player/trial/instructions/status/'.$trial->id)
                     ->seeJson(['status' => 'remove']);
              }

              if($user->selected_for_removal == false || $user->selected_for_removal == '') {
                $output->writeln('Selected for trial');
                $this->actingAs($userObj)
                     ->get('/player/trial/instructions/status/'.$trial->id)
                     ->seeJson(['status' => 'ready']);
              }
            }
          }

          else {
            $output->writeln('Instructions incomplete - not ready for trial');
            foreach ($users_in_trial as $user) {
              $userObj = oceler\User::find($user->user_id);
              $output->writeln($user->user_id);
              $this->assertTrue($user->selected_for_removal != true);
              $this->actingAs($userObj)
                   ->get('/player/trial/instructions/status/'.$trial->id)
                   ->seeJson(['status' => 'waiting']);
            }
          }

        }
    }
}
