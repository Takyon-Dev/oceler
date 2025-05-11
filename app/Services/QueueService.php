<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\User;
use App\Models\Trial;
use App\Models\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QueueService
{
    /**
     * Add a user to the queue.
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function addToQueue(User $user): array
    {
        if ($user->trials()->where('is_active', true)->exists()) {
            return [
                'success' => false,
                'message' => 'User is already in an active trial'
            ];
        }

        try {
            DB::transaction(function () use ($user) {
                $queue = Queue::firstOrNew(['user_id' => $user->id]);
                $queue->trial_type = $user->lastTrialType() + 1;
                $queue->updated_at = Carbon::now();
                $queue->save();

                Log::info('User added to queue', [
                    'user_id' => $user->id,
                    'trial_type' => $queue->trial_type
                ]);
            });

            return [
                'success' => true,
                'message' => 'Added to queue successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to add user to queue', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to add to queue'
            ];
        }
    }

    /**
     * Remove a user from the queue.
     *
     * @param int $user_id
     * @return void
     */
    public function removeFromQueue(int $user_id): void
    {
        Queue::where('user_id', $user_id)->delete();
        Log::info('User removed from queue', ['user_id' => $user_id]);
    }

    /**
     * Get queue position for a user.
     *
     * @param int $user_id
     * @return int|null
     */
    public function getQueuePosition(int $user_id): ?int
    {
        $queue = Queue::where('user_id', $user_id)->first();
        return $queue ? $queue->position : null;
    }

    /**
     * Clean up inactive users from the queue.
     *
     * @return void
     */
    public function cleanupInactiveUsers(): void
    {
        $inactive_threshold = Carbon::now()->subMinutes(5);
        
        $inactive_users = Queue::where('updated_at', '<', $inactive_threshold)
            ->pluck('user_id')
            ->toArray();

        if (!empty($inactive_users)) {
            Queue::whereIn('user_id', $inactive_users)->delete();
            Log::info('Cleaned up inactive users from queue', ['user_ids' => $inactive_users]);
        }
    }

    /**
     * Assign players to available trials.
     *
     * @return void
     */
    public function assignPlayersToTrials(): void
    {
        $this->cleanupInactiveUsers();

        $trials = Trial::where('is_active', true)
            ->where('started_at', null)
            ->get();

        foreach ($trials as $trial) {
            $this->assignPlayersToTrial($trial);
        }
    }

    /**
     * Assign players to a specific trial.
     *
     * @param \App\Models\Trial $trial
     * @return void
     */
    private function assignPlayersToTrial(Trial $trial): void
    {
        $queued_players = Queue::where('trial_type', $trial->trial_type)
            ->orderBy('created_at')
            ->take($trial->num_players)
            ->get();

        if ($queued_players->count() < $trial->num_players) {
            return;
        }

        try {
            DB::transaction(function () use ($trial, $queued_players) {
                $groups = $trial->groups()->get();
                $players_per_group = ceil($trial->num_players / $trial->num_groups);
                $current_group = 0;

                foreach ($queued_players as $index => $queued_player) {
                    if ($index % $players_per_group === 0 && $current_group < $trial->num_groups) {
                        $current_group++;
                    }

                    $group = $groups[$current_group - 1];
                    
                    $trial->users()->attach($queued_player->user_id, [
                        'group_id' => $group->id,
                        'joined_at' => Carbon::now(),
                        'status' => 'active'
                    ]);

                    $group->users()->attach($queued_player->user_id);
                    $this->removeFromQueue($queued_player->user_id);
                }

                $trial->update(['started_at' => Carbon::now()]);
            });

            Log::info('Players assigned to trial', [
                'trial_id' => $trial->id,
                'num_players' => $queued_players->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to assign players to trial', [
                'trial_id' => $trial->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get queue statistics.
     *
     * @return array
     */
    public function getQueueStats(): array
    {
        $total_queued = Queue::count();
        $by_trial_type = Queue::select('trial_type', DB::raw('count(*) as count'))
            ->groupBy('trial_type')
            ->get()
            ->pluck('count', 'trial_type')
            ->toArray();

        return [
            'total_queued' => $total_queued,
            'by_trial_type' => $by_trial_type
        ];
    }

    /**
     * Check if a user is in the queue.
     *
     * @param int $user_id
     * @return bool
     */
    public function isUserInQueue(int $user_id): bool
    {
        return Queue::where('user_id', $user_id)->exists();
    }

    /**
     * Get all queued users.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getQueuedUsers(): Collection
    {
        return Queue::with('user')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get queued users by trial type.
     *
     * @param int $trial_type
     * @return \Illuminate\Support\Collection
     */
    public function getQueuedUsersByType(int $trial_type): Collection
    {
        return Queue::with('user')
            ->where('trial_type', $trial_type)
            ->orderBy('created_at')
            ->get();
    }
} 