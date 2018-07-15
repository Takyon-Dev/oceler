<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
        $NUM_USERS = 24;
        $NUM_PLAYERS = [16, 3];
        $NUM_TO_RECRUIT = [18, ''];

        $faker = Faker::create();
        for($i = 0; $i < $NUM_USERS; $i++) {
          $u = new User;
          $u->name = $faker->name;
          $u->mturk_id = $faker->ean13;
          $u->email = $faker->email;
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
          $group->network_id = 2;
          $group->group = 1;
          $group->save();
        }

        $this->visit('/manage-queue');
        $queued_players = oceler\Queue::get()->count();
        $this->assertTrue($queued_players == 5);

        foreach($trials as $key => $trial) {
          $num_to_recruit = ($trial->num_to_recruit != '') ? $trial->num_to_recruit : $trial->num_players;
          $users_in_trial = \DB::table('trial_user')->where('trial_id', $trial->id)->count();
          $this->assertTrue($users_in_trial == $num_to_recruit);
        }

    }
}
