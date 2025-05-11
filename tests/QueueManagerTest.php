<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Console\Output\ConsoleOutput;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Trial;
use App\Models\Queue;
use App\Models\Network;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Group;

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

        $network = new Network;
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
            DB::table('queues')->insert([
                'user_id' => $u->id,
                'trial_type' => $trial_type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $queued_players = Queue::count();

        $this->assertEquals($NUM_USERS, $queued_players);
        $trials = [];

        // Create some trials
        for($i = 0; $i < 2; $i++) {
            $trial = new Trial;
            $trial->name = 'QUEUE MANAGER TEST '.$i;
            $trial->trial_type = $i + 1;
            $trial->num_players = $NUM_PLAYERS[$i] - 2;
            $trial->num_to_recruit = $NUM_TO_RECRUIT[$i];
            $trial->is_active = 1;
            $trial->num_groups = 1;
            $trial->save();

            $trials[] = $trial;

            $group = new Group;
            $group->trial_id = $trial->id;
            $group->network_id = $network->id;
            $group->group = 1;
            $group->save();
        }

        // Run the manageQueue method, which should put players in trials
        $this->get('/manage-queue');

        $recruited = 0;

        // See that the correct number of players were put in the trials
        foreach($trials as $key => $trial) {
            $num_to_recruit = ($trial->num_to_recruit != '') ? $trial->num_to_recruit : $trial->num_players;
            $users_in_trial = DB::table('trial_user')->where('trial_id', $trial->id)->count();
            $this->assertEquals($num_to_recruit, $users_in_trial);
            $recruited += $num_to_recruit;
        }

        $queued_players = Queue::count();
        $this->assertEquals($NUM_USERS - $recruited, $queued_players);

        foreach ($trials as $key => $trial) {
            // Update last_ping so the players appear to be active
            DB::table('trial_user')
                ->where('trial_id', $trial->id)
                ->update(['last_ping' => now()]);
            
            $users_in_trial = DB::table('trial_user')
                ->where('trial_id', $trial->id)
                ->get();
            
            $num_to_recruit = ($trial->num_to_recruit != '') ? $trial->num_to_recruit : $trial->num_players;
            $numInstructions = 0;

            // Mark the instructions as read in the first trial
            foreach ($users_in_trial as $user) {
                if($numInstructions < $num_to_recruit && $trial->trial_type < 2) {
                    DB::table('trial_user')
                        ->where('user_id', $user->user_id)
                        ->update(['instructions_read' => true]);
                    $numInstructions++;
                }
            }
        }

        // Run manageQueue again - this will test the selection of players for the trial that have read the instructions
        $this->get('/manage-queue');

        // Test that the instructions-status handler is behaving correctly
        foreach ($trials as $key => $trial) {
            $users_in_trial = DB::table('trial_user')
                ->where('trial_id', $trial->id)
                ->get();
            
            $numInstructionsRead = DB::table('trial_user')
                ->where('trial_id', $trial->id)
                ->where('instructions_read', true)
                ->count();

            if($numInstructionsRead >= $trial->num_players) {
                $output->writeln('Instructions complete - ready for trial');
                foreach ($users_in_trial as $user) {
                    $userObj = User::find($user->user_id);
                    $output->writeln($user->user_id);
                    
                    if($user->instructions_read == false) {
                        $output->writeln('Instructions not read');
                        $this->assertFalse($user->selected_for_removal);
                        $this->actingAs($userObj)
                            ->get('/player/trial/instructions/status/'.$trial->id)
                            ->assertJson(['status' => 'remove']);
                    }

                    if($user->selected_for_removal == true) {
                        $output->writeln('Selected for removal');
                        $this->actingAs($userObj)
                            ->get('/player/trial/instructions/status/'.$trial->id)
                            ->assertJson(['status' => 'remove']);
                    }

                    if($user->selected_for_removal == false || $user->selected_for_removal == '') {
                        $output->writeln('Selected for trial');
                        $this->actingAs($userObj)
                            ->get('/player/trial/instructions/status/'.$trial->id)
                            ->assertJson(['status' => 'ready']);
                    }
                }
            } else {
                $output->writeln('Instructions incomplete - not ready for trial');
                foreach ($users_in_trial as $user) {
                    $userObj = User::find($user->user_id);
                    $output->writeln($user->user_id);
                    $this->assertFalse($user->selected_for_removal);
                    $this->actingAs($userObj)
                        ->get('/player/trial/instructions/status/'.$trial->id)
                        ->assertJson(['status' => 'waiting']);
                }
            }
        }
    }
}
