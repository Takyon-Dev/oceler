<?php

namespace App\Services;

use App\Models\Trial;
use App\Models\User;
use App\Models\Solution;
use App\Models\Round;
use App\Models\Group;
use App\Models\Network;
use App\Models\Queue;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFacade;
use Carbon\Carbon;

class PlayerService
{
    /**
     * Get the player's dashboard data.
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function getPlayerDashboardData(User $user): array
    {
        $active_trial = $user->trials()
            ->where('is_active', true)
            ->first();

        $queue_position = Queue::where('user_id', $user->id)
            ->value('position');

        $recent_trials = $user->trials()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return [
            'active_trial' => $active_trial,
            'queue_position' => $queue_position,
            'recent_trials' => $recent_trials
        ];
    }

    /**
     * Get trial instructions.
     *
     * @param int $trial_id
     * @return \App\Models\Trial
     */
    public function getTrialInstructions(int $trial_id): Trial
    {
        return Trial::with('factoidset', 'network', 'nameset')
            ->findOrFail($trial_id);
    }

    /**
     * Submit a solution for a trial.
     *
     * @param int $user_id
     * @param int $trial_id
     * @param int $round_id
     * @param string $solution
     * @return array
     */
    public function submitSolution(int $user_id, int $trial_id, int $round_id, string $solution): array
    {
        $trial = Trial::findOrFail($trial_id);
        $round = Round::findOrFail($round_id);

        $result = $this->validateSolution($solution, $round->factoid_id);
        $success = $result['success'];

        $solution = new Solution([
            'user_id' => $user_id,
            'trial_id' => $trial_id,
            'round_id' => $round_id,
            'solution' => $solution,
            'success' => $success
        ]);

        $solution->save();

        LogFacade::info('Solution submitted', [
            'user_id' => $user_id,
            'trial_id' => $trial_id,
            'round_id' => $round_id,
            'success' => $success
        ]);

        return [
            'success' => $success,
            'message' => $result['message']
        ];
    }

    /**
     * Get the current round for a trial.
     *
     * @param int $trial_id
     * @return array
     */
    public function getCurrentRound(int $trial_id): array
    {
        $trial = Trial::findOrFail($trial_id);
        $round = $trial->rounds()
            ->where('is_active', true)
            ->first();

        if (!$round) {
            return [
                'status' => 'completed',
                'message' => 'No active round found'
            ];
        }

        return [
            'status' => 'active',
            'round' => $round,
            'time_remaining' => $this->calculateTimeRemaining($round)
        ];
    }

    /**
     * Get player's solutions for a trial.
     *
     * @param int $user_id
     * @param int $trial_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPlayerSolutions(int $user_id, int $trial_id)
    {
        return Solution::where('user_id', $user_id)
            ->where('trial_id', $trial_id)
            ->with('round')
            ->get();
    }

    /**
     * Get player's score for a trial.
     *
     * @param int $user_id
     * @param int $trial_id
     * @return int
     */
    public function getPlayerScore(int $user_id, int $trial_id): int
    {
        return Solution::where('user_id', $user_id)
            ->where('trial_id', $trial_id)
            ->where('success', true)
            ->count();
    }

    /**
     * Get player's group information for a trial.
     *
     * @param int $user_id
     * @param int $trial_id
     * @return array
     */
    public function getPlayerGroupInfo(int $user_id, int $trial_id): array
    {
        $group = Group::where('trial_id', $trial_id)
            ->whereHas('users', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->first();

        if (!$group) {
            return [
                'error' => 'Group not found'
            ];
        }

        return [
            'group_id' => $group->id,
            'group_name' => $group->name,
            'members' => $group->users()->pluck('name')
        ];
    }

    /**
     * Get player's network information for a trial.
     *
     * @param int $user_id
     * @param int $trial_id
     * @return array
     */
    public function getPlayerNetworkInfo(int $user_id, int $trial_id): array
    {
        $trial = Trial::findOrFail($trial_id);
        $network = $trial->network;

        if (!$network) {
            return [
                'error' => 'Network not found'
            ];
        }

        return [
            'network_id' => $network->id,
            'network_name' => $network->name,
            'nodes' => $network->nodes,
            'edges' => $network->edges
        ];
    }

    /**
     * Handle trial completion.
     *
     * @param int $user_id
     * @param int $trial_id
     * @return void
     */
    public function handleTrialCompletion(int $user_id, int $trial_id): void
    {
        $trial = Trial::findOrFail($trial_id);
        
        DB::transaction(function () use ($user_id, $trial) {
            $trial->users()->updateExistingPivot($user_id, [
                'completed_at' => Carbon::now(),
                'status' => 'completed'
            ]);

            if ($trial->allUsersCompleted()) {
                $trial->update(['is_active' => false]);
            }
        });

        LogFacade::info('Trial completed', [
            'user_id' => $user_id,
            'trial_id' => $trial_id
        ]);
    }

    /**
     * Handle trial leave.
     *
     * @param int $user_id
     * @param int $trial_id
     * @return void
     */
    public function handleTrialLeave(int $user_id, int $trial_id): void
    {
        $trial = Trial::findOrFail($trial_id);
        
        DB::transaction(function () use ($user_id, $trial) {
            $trial->users()->detach($user_id);
            
            if ($trial->users()->count() === 0) {
                $trial->update(['is_active' => false]);
            }
        });

        LogFacade::info('Trial left', [
            'user_id' => $user_id,
            'trial_id' => $trial_id
        ]);
    }

    /**
     * Get player's trial history.
     *
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPlayerHistory(int $user_id)
    {
        return Trial::whereHas('users', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })
        ->with(['users' => function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        }])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Get player's statistics.
     *
     * @param int $user_id
     * @return array
     */
    public function getPlayerStats(int $user_id): array
    {
        $total_trials = Trial::whereHas('users', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->count();

        $completed_trials = Trial::whereHas('users', function ($query) use ($user_id) {
            $query->where('user_id', $user_id)
                ->where('status', 'completed');
        })->count();

        $total_solutions = Solution::where('user_id', $user_id)->count();
        $successful_solutions = Solution::where('user_id', $user_id)
            ->where('success', true)
            ->count();

        return [
            'total_trials' => $total_trials,
            'completed_trials' => $completed_trials,
            'total_solutions' => $total_solutions,
            'successful_solutions' => $successful_solutions,
            'success_rate' => $total_solutions > 0 
                ? round(($successful_solutions / $total_solutions) * 100, 2)
                : 0
        ];
    }

    /**
     * Validate a solution against the correct factoid.
     *
     * @param string $solution
     * @param int $factoid_id
     * @return array
     */
    private function validateSolution(string $solution, int $factoid_id): array
    {
        $factoid = DB::table('factoids')
            ->where('id', $factoid_id)
            ->first();

        if (!$factoid) {
            return [
                'success' => false,
                'message' => 'Factoid not found'
            ];
        }

        $is_correct = strtolower(trim($solution)) === strtolower(trim($factoid->answer));

        return [
            'success' => $is_correct,
            'message' => $is_correct ? 'Correct!' : 'Incorrect, try again.'
        ];
    }

    /**
     * Calculate time remaining for a round.
     *
     * @param \App\Models\Round $round
     * @return int
     */
    private function calculateTimeRemaining(Round $round): int
    {
        $end_time = Carbon::parse($round->started_at)
            ->addSeconds($round->duration);

        return max(0, Carbon::now()->diffInSeconds($end_time, false));
    }
} 