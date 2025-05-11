<?php

namespace App\Services;

use App\Models\Trial;
use App\Models\Queue;
use App\Models\Factoidset;
use App\Models\Network;
use App\Models\Nameset;
use App\Models\User;
use App\Models\Solution;
use App\Models\MturkHit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TrialService
{
    /**
     * Get recent trials within the specified days.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentTrials()
    {
        $cutoff_date = Carbon::now()->subDays(env('TRIALS_WITHIN_DAYS', ''))->toDateString();
        return Trial::orderBy('id', 'desc')
            ->where('created_at', '>', $cutoff_date)
            ->with('users')
            ->with('archive')
            ->get();
    }

    /**
     * Create a new trial from the given configuration.
     *
     * @param array $config
     * @return \App\Models\Trial
     * @throws \InvalidArgumentException
     */
    public function createTrial(array $config): Trial
    {
        try {
            return DB::transaction(function () use ($config) {
                $trial = new Trial([
                    'name' => $config['name'] ?? 'New Trial',
                    'description' => $config['description'] ?? '',
                    'network_id' => $config['network_id'] ?? null,
                    'factoidset_id' => $config['factoidset_id'] ?? null,
                    'nameset_id' => $config['nameset_id'] ?? null,
                    'max_rounds' => $config['max_rounds'] ?? 1,
                    'round_duration' => $config['round_duration'] ?? 300,
                    'distribution_interval' => $config['distribution_interval'] ?? 0,
                    'num_players' => $config['num_players'] ?? 0,
                    'mult_factoid' => $config['mult_factoid'] ?? 0,
                    'pay_correct' => $config['pay_correct'] ?? 0,
                    'pay_time_factor' => $config['pay_time_factor'] ?? 0,
                    'payment_per_solution' => $config['payment_per_solution'] ?? 0,
                    'is_active' => true
                ]);

                $trial->save();
                $trial->storeTrialConfig($config);
                $trial->logConfig();

                // Create groups if specified
                if (isset($config['groups'])) {
                    $this->createGroups($trial, $config['groups']);
                }

                Log::info('Trial created', [
                    'trial_id' => $trial->id,
                    'name' => $trial->name
                ]);

                return $trial;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create trial', [
                'error' => $e->getMessage(),
                'config' => $config
            ]);

            throw new \InvalidArgumentException('Failed to create trial: ' . $e->getMessage());
        }
    }

    /**
     * Delete a trial and its associated data.
     *
     * @param int $id
     * @return void
     */
    public function deleteTrial(int $id): void
    {
        $trial = Trial::findOrFail($id);
        $trial->stopTrial();
        $trial->delete();
    }

    /**
     * Toggle the active status of a trial.
     *
     * @param int $id
     * @return \App\Models\Trial
     */
    public function toggleTrialStatus(int $id): Trial
    {
        $trial = Trial::findOrFail($id);
        $trial->is_active = !$trial->is_active;
        $trial->save();
        return $trial;
    }

    /**
     * Stop a specific trial.
     *
     * @param int $id
     * @return void
     */
    public function stopTrial(int $id): void
    {
        $trial = Trial::findOrFail($id);
        $trial->stopTrial();
    }

    /**
     * Stop all active trials.
     *
     * @return void
     */
    public function stopAllTrials(): void
    {
        $trials = Trial::where('is_active', true)->get();
        foreach ($trials as $trial) {
            $trial->stopTrial();
        }
    }

    /**
     * Handle a user entering the queue.
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function enterQueue(User $user): array
    {
        if ($user->trials->isNotEmpty()) {
            return ['redirect' => true];
        }

        $queue = Queue::firstOrNew(['user_id' => $user->id]);
        $queue->trial_type = $user->lastTrialType() + 1;
        $queue->updated_at = Carbon::now();
        $queue->save();

        Log::info("USER ID: {$user->id} entered the queue");

        return ['redirect' => false];
    }

    /**
     * Get detailed information about a trial.
     *
     * @param int $id
     * @return array
     */
    public function getTrialDetails(int $id): array
    {
        $trial = Trial::with('users')->findOrFail($id);
        $curr_round = $trial->curr_round;

        $server_time = time();
        $start_time = $curr_round > 0 
            ? strtotime($trial->rounds[$curr_round - 1]->updated_at)
            : 'Trial has not begun yet';

        return compact('trial', 'server_time', 'start_time');
    }

    /**
     * Get trial data for editing.
     *
     * @param int $id
     * @return array
     */
    public function getTrialForEdit(int $id): array
    {
        $trial = Trial::where('id', $id)
            ->with('rounds')
            ->with('groups')
            ->firstOrFail();

        $factoidsets = Factoidset::pluck('name', 'id');
        $networks = Network::pluck('name', 'id');
        $namesets = Nameset::pluck('name', 'id');

        $in_progress = DB::table('trial_user')
            ->where('trial_id', $id)
            ->exists();

        return compact('trial', 'factoidsets', 'networks', 'namesets', 'in_progress');
    }

    /**
     * Update a trial with new configuration.
     *
     * @param int $id
     * @param array $config
     * @return void
     */
    public function updateTrial(int $id, array $config): void
    {
        $trial = Trial::findOrFail($id);
        $trial->update([
            'distribution_interval' => $config['distribution_interval'],
            'num_players' => $config['num_players'],
            'mult_factoid' => $config['mult_factoid'] ?? 0,
            'pay_correct' => $config['pay_correct'] ?? 0,
            'pay_time_factor' => $config['pay_time_factor'] ?? 0,
            'payment_per_solution' => $config['payment_per_solution'],
        ]);
    }

    /**
     * Get all active trials with their players and solutions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveTrialsWithPlayers()
    {
        $trials = Trial::with('users')
            ->where('is_active', true)
            ->get();

        foreach ($trials as $trial) {
            foreach ($trial->users as $user) {
                $solutions = Solution::getCurrentSolutions(
                    $user->id,
                    $trial->id,
                    $trial->curr_round
                );
                $user->solutions = $solutions;

                $group = DB::table('groups')
                    ->where('id', $user->pivot->group_id)
                    ->first();

                $network = DB::table('networks')
                    ->where('id', $group->network_id)
                    ->value('id');

                $u_node_id = DB::table('user_nodes')
                    ->where('user_id', $user->id)
                    ->where('group_id', $group->id)
                    ->value('node_id');

                $u_node = DB::table('network_nodes')
                    ->where('id', $u_node_id)
                    ->value('node');
                
                $user->node = $u_node;
            }
        }

        return $trials;
    }

    /**
     * Get a specific trial with its players and solutions.
     *
     * @param int $id
     * @return \App\Models\Trial
     */
    public function getTrialWithPlayers(int $id): Trial
    {
        $trial = Trial::with('users')->findOrFail($id);

        foreach ($trial->users as $user) {
            $solutions = Solution::getCurrentSolutions(
                $user->id,
                $trial->id,
                $trial->curr_round
            );
            $user->solutions = $solutions;

            $group = DB::table('groups')
                ->where('id', $user->pivot->group_id)
                ->first();

            $network = DB::table('networks')
                ->where('id', $group->network_id)
                ->value('id');

            $u_node_id = DB::table('user_nodes')
                ->where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->value('node_id');

            $u_node = DB::table('network_nodes')
                ->where('id', $u_node_id)
                ->value('node');
            
            $user->node = $u_node;
        }

        return $trial;
    }

    /**
     * Manage the queue of players waiting to join trials.
     *
     * @return int
     */
    public function manageQueue(): int
    {
        $user = Auth::user();
        $last_trial_type = $user->lastTrialType();
        $now = Carbon::now();

        if (DB::table('trial_user')->where('user_id', $user->id)->exists()) {
            return 0;
        }

        $queue = Queue::firstOrNew(['user_id' => $user->id]);
        $queue->trial_type = $last_trial_type + 1;
        $queue->updated_at = $now;
        $queue->save();

        $this->deleteInactiveQueueUsers();

        $filled_trials = DB::table('trial_user')
            ->pluck('trial_id')
            ->toArray();

        $trial = Trial::where('is_active', true)
            ->where('trial_type', $queue->trial_type)
            ->whereNotIn('id', $filled_trials)
            ->orderBy('created_at')
            ->first();

        if (!$trial) {
            return -1;
        }

        $queued_players = Queue::where('trial_type', $queue->trial_type)
            ->count();

        if ($queued_players >= $trial->num_players) {
            $this->assignPlayersToTrial($trial, $queued_players);
            return 0;
        }

        return $trial->num_players - $queued_players;
    }

    /**
     * Handle when a trial is stopped for a user.
     *
     * @param int $user_id
     * @return void
     */
    public function handleTrialStopped(int $user_id): void
    {
        $trial_user = DB::table('trial_user')
            ->where('user_id', $user_id)
            ->orderByDesc('updated_at')
            ->get();

        foreach ($trial_user as $trial) {
            Trial::find($trial->trial_id)->stopTrial();
        }
    }

    /**
     * Get the status of instructions for a trial.
     *
     * @param int $trial_id
     * @param int $user_id
     * @return array
     */
    public function getInstructionsStatus(int $trial_id, int $user_id): array
    {
        $trial = Trial::with('users')->findOrFail($trial_id);
        $num_read = 0;
        $inactive_ping_time = 20;
        $now = Carbon::now();
        $has_read_instructions = false;

        foreach ($trial->users as $user) {
            if ($user->id === $user_id) {
                $has_read_instructions = $user->pivot->instructions_read;
                if ($user->pivot->selected_for_removal) {
                    return ['status' => 'remove'];
                }
                $trial->users()->updateExistingPivot($user->id, ['last_ping' => $now]);
            }

            if ($user->pivot->instructions_read 
                && !$user->pivot->selected_for_removal 
                && $user->pivot->last_ping > $now->subSeconds($inactive_ping_time)
            ) {
                $num_read++;
            }
        }

        if ($num_read >= $trial->num_players) {
            return [
                'status' => $has_read_instructions ? 'ready' : 'remove'
            ];
        }

        return [
            'status' => 'waiting',
            'num_completed' => $num_read,
            'num_needed' => $trial->num_players
        ];
    }

    /**
     * Mark instructions as read for a user.
     *
     * @param int $user_id
     * @return void
     */
    public function markInstructionsAsRead(int $user_id): void
    {
        DB::table('trial_user')
            ->where('user_id', $user_id)
            ->update(['instructions_read' => true]);
    }

    /**
     * Handle when a user is not selected for a trial.
     *
     * @param int $trial_id
     * @param int $user_id
     * @return void
     */
    public function handleNotSelected(int $trial_id, int $user_id): void
    {
        // Implementation for handling not selected users
    }

    /**
     * Delete inactive users from the queue.
     *
     * @return void
     */
    private function deleteInactiveQueueUsers(): void
    {
        $inactive_queue_time = 6;
        $now = Carbon::now();
        
        $to_delete = Queue::where('updated_at', '<', $now->subSeconds($inactive_queue_time))
            ->pluck('user_id')
            ->toArray();

        if (count($to_delete) > 0) {
            Log::info("Deleting from the Queue due to inactivity: " . implode(',', $to_delete));
            Queue::whereIn('user_id', $to_delete)->delete();
        }
    }

    /**
     * Assign players to a trial.
     *
     * @param \App\Models\Trial $trial
     * @param int $queued_players
     * @return void
     */
    private function assignPlayersToTrial(Trial $trial, int $queued_players): void
    {
        $selected = Queue::where('trial_type', $trial->trial_type)
            ->orderBy('created_at')
            ->take($trial->num_players)
            ->get()
            ->shuffle();

        $group = 1;
        $count = 0;
        $now = Carbon::now();

        foreach ($selected as $player) {
            DB::table('trial_user')->insert([
                'created_at' => $now,
                'updated_at' => $now,
                'user_id' => $player->user_id,
                'trial_id' => $trial->id,
                'group_id' => DB::table('groups')
                    ->where('trial_id', $trial->id)
                    ->where('group', $group)
                    ->value('id')
            ]);

            $count++;
            if ($count >= $trial->num_players / $trial->num_groups) {
                $group++;
            }

            Queue::where('user_id', $player->user_id)->delete();
        }
    }

    /**
     * Start a trial.
     *
     * @param int $trial_id
     * @return \App\Models\Trial
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function startTrial(int $trial_id): Trial
    {
        $trial = Trial::findOrFail($trial_id);

        DB::transaction(function () use ($trial) {
            $trial->update([
                'is_active' => true,
                'started_at' => Carbon::now()
            ]);

            // Create first round
            $this->createRound($trial);
        });

        Log::info('Trial started', ['trial_id' => $trial_id]);

        return $trial;
    }

    /**
     * End a trial.
     *
     * @param int $trial_id
     * @return \App\Models\Trial
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function endTrial(int $trial_id): Trial
    {
        $trial = Trial::findOrFail($trial_id);

        DB::transaction(function () use ($trial) {
            $trial->update([
                'is_active' => false,
                'ended_at' => Carbon::now()
            ]);

            // End all active rounds
            $trial->rounds()
                ->where('is_active', true)
                ->update(['is_active' => false]);
        });

        Log::info('Trial ended', ['trial_id' => $trial_id]);

        return $trial;
    }

    /**
     * Add a user to a trial.
     *
     * @param int $trial_id
     * @param int $user_id
     * @param int|null $group_id
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function addUserToTrial(int $trial_id, int $user_id, ?int $group_id = null): void
    {
        $trial = Trial::findOrFail($trial_id);
        $user = User::findOrFail($user_id);

        DB::transaction(function () use ($trial, $user, $group_id) {
            $trial->users()->attach($user->id, [
                'joined_at' => Carbon::now(),
                'status' => 'active'
            ]);

            if ($group_id) {
                $group = Group::findOrFail($group_id);
                $group->users()->attach($user->id);
            }
        });

        Log::info('User added to trial', [
            'trial_id' => $trial_id,
            'user_id' => $user_id,
            'group_id' => $group_id
        ]);
    }

    /**
     * Remove a user from a trial.
     *
     * @param int $trial_id
     * @param int $user_id
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function removeUserFromTrial(int $trial_id, int $user_id): void
    {
        $trial = Trial::findOrFail($trial_id);

        DB::transaction(function () use ($trial, $user_id) {
            $trial->users()->detach($user_id);

            // Remove from any groups in the trial
            $trial->groups()->each(function ($group) use ($user_id) {
                $group->users()->detach($user_id);
            });
        });

        Log::info('User removed from trial', [
            'trial_id' => $trial_id,
            'user_id' => $user_id
        ]);
    }

    /**
     * Create a new round for a trial.
     *
     * @param \App\Models\Trial $trial
     * @return \App\Models\Round
     */
    public function createRound(Trial $trial): Round
    {
        $round = new Round([
            'trial_id' => $trial->id,
            'round' => $trial->rounds()->count() + 1,
            'started_at' => Carbon::now(),
            'is_active' => true
        ]);

        $round->save();

        Log::info('Round created', [
            'trial_id' => $trial->id,
            'round_id' => $round->id
        ]);

        return $round;
    }

    /**
     * End the current round of a trial.
     *
     * @param int $trial_id
     * @return \App\Models\Round
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function endCurrentRound(int $trial_id): Round
    {
        $trial = Trial::findOrFail($trial_id);
        $round = $trial->rounds()
            ->where('is_active', true)
            ->firstOrFail();

        $round->update([
            'is_active' => false,
            'ended_at' => Carbon::now()
        ]);

        Log::info('Round ended', [
            'trial_id' => $trial_id,
            'round_id' => $round->id
        ]);

        return $round;
    }

    /**
     * Get trial statistics.
     *
     * @param int $trial_id
     * @return array
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getTrialStats(int $trial_id): array
    {
        $trial = Trial::findOrFail($trial_id);

        $total_users = $trial->users()->count();
        $active_users = $trial->users()
            ->wherePivot('status', 'active')
            ->count();
        $completed_rounds = $trial->rounds()
            ->where('is_active', false)
            ->count();
        $total_solutions = Solution::where('trial_id', $trial_id)->count();
        $successful_solutions = Solution::where('trial_id', $trial_id)
            ->where('success', true)
            ->count();

        return [
            'total_users' => $total_users,
            'active_users' => $active_users,
            'completed_rounds' => $completed_rounds,
            'total_solutions' => $total_solutions,
            'successful_solutions' => $successful_solutions,
            'success_rate' => $total_solutions > 0 
                ? round(($successful_solutions / $total_solutions) * 100, 2)
                : 0
        ];
    }

    /**
     * Create groups for a trial.
     *
     * @param \App\Models\Trial $trial
     * @param array $groups
     * @return void
     */
    private function createGroups(Trial $trial, array $groups): void
    {
        foreach ($groups as $groupData) {
            $group = new Group([
                'trial_id' => $trial->id,
                'name' => $groupData['name'],
                'description' => $groupData['description'] ?? null
            ]);

            $group->save();

            if (isset($groupData['users'])) {
                $group->users()->attach($groupData['users']);
            }
        }
    }

    /**
     * Check if a trial can be started.
     *
     * @param int $trial_id
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function canStartTrial(int $trial_id): bool
    {
        $trial = Trial::findOrFail($trial_id);

        return $trial->users()->count() > 0 
            && $trial->network_id 
            && $trial->factoidset_id 
            && $trial->nameset_id;
    }

    /**
     * Get active trials.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveTrials(): Collection
    {
        return Trial::where('is_active', true)
            ->with(['network', 'factoidset', 'nameset'])
            ->get();
    }

    /**
     * Get completed trials.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCompletedTrials(): Collection
    {
        return Trial::where('is_active', false)
            ->whereNotNull('ended_at')
            ->with(['network', 'factoidset', 'nameset'])
            ->get();
    }
} 