<?php

namespace App\Http\Controllers;

use App\Models\Trial;
use App\Models\User;
use App\Models\Message;
use App\Models\Solution;
use App\Models\Round;
use App\Models\Queue;
use App\Models\Log;
use App\Models\MTurk;
use App\Models\MturkHit;
use App\QueueManager\QueueManager;
use App\Services\PlayerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PlayerController extends Controller
{
    /**
     * @var PlayerService
     */
    private PlayerService $playerService;

    /**
     * Create a new controller instance.
     *
     * @param PlayerService $playerService
     */
    public function __construct(PlayerService $playerService)
    {
        $this->playerService = $playerService;
        $this->middleware('auth');
    }

    /**
     * Display the player's dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $user = Auth::user();
        $data = $this->playerService->getPlayerDashboardData($user);
        return view('player.dashboard', $data);
    }

    /**
     * Display the instructions for a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\View\View
     */
    public function instructions(int $trial_id): View
    {
        $trial = $this->playerService->getTrialInstructions($trial_id);
        return view('player.instructions', compact('trial'));
    }

    /**
     * Submit a solution for a trial.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $trial_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitSolution(Request $request, int $trial_id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'solution' => 'required|string',
                'round_id' => 'required|integer|exists:rounds,id'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $result = $this->playerService->submitSolution(
                Auth::id(),
                $trial_id,
                $request->round_id,
                $request->solution
            );

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Solution submission failed', [
                'user_id' => Auth::id(),
                'trial_id' => $trial_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to submit solution'], 500);
        }
    }

    /**
     * Get the current round for a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentRound(int $trial_id): JsonResponse
    {
        try {
            $round = $this->playerService->getCurrentRound($trial_id);
            return response()->json($round);
        } catch (\Exception $e) {
            Log::error('Failed to get current round', [
                'trial_id' => $trial_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get current round'], 500);
        }
    }

    /**
     * Get the player's solutions for a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSolutions(int $trial_id): JsonResponse
    {
        try {
            $solutions = $this->playerService->getPlayerSolutions(Auth::id(), $trial_id);
            return response()->json($solutions);
        } catch (\Exception $e) {
            Log::error('Failed to get solutions', [
                'user_id' => Auth::id(),
                'trial_id' => $trial_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get solutions'], 500);
        }
    }

    /**
     * Get the player's score for a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getScore(int $trial_id): JsonResponse
    {
        try {
            $score = $this->playerService->getPlayerScore(Auth::id(), $trial_id);
            return response()->json(['score' => $score]);
        } catch (\Exception $e) {
            Log::error('Failed to get score', [
                'user_id' => Auth::id(),
                'trial_id' => $trial_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get score'], 500);
        }
    }

    /**
     * Get the player's group information for a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupInfo(int $trial_id): JsonResponse
    {
        try {
            $groupInfo = $this->playerService->getPlayerGroupInfo(Auth::id(), $trial_id);
            return response()->json($groupInfo);
        } catch (\Exception $e) {
            Log::error('Failed to get group info', [
                'user_id' => Auth::id(),
                'trial_id' => $trial_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get group info'], 500);
        }
    }

    /**
     * Get the player's network information for a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNetworkInfo(int $trial_id): JsonResponse
    {
        try {
            $networkInfo = $this->playerService->getPlayerNetworkInfo(Auth::id(), $trial_id);
            return response()->json($networkInfo);
        } catch (\Exception $e) {
            Log::error('Failed to get network info', [
                'user_id' => Auth::id(),
                'trial_id' => $trial_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get network info'], 500);
        }
    }

    /**
     * Handle when a player completes a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeTrial(int $trial_id): RedirectResponse
    {
        try {
            $this->playerService->handleTrialCompletion(Auth::id(), $trial_id);
            Log::info('Trial completed', [
                'user_id' => Auth::id(),
                'trial_id' => $trial_id
            ]);

            return redirect()->route('player.dashboard')
                ->with('success', 'Trial completed successfully');
        } catch (\Exception $e) {
            Log::error('Trial completion failed', [
                'user_id' => Auth::id(),
                'trial_id' => $trial_id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to complete trial');
        }
    }

    /**
     * Handle when a player leaves a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leaveTrial(int $trial_id): RedirectResponse
    {
        try {
            $this->playerService->handleTrialLeave(Auth::id(), $trial_id);
            Log::info('Trial left', [
                'user_id' => Auth::id(),
                'trial_id' => $trial_id
            ]);

            return redirect()->route('player.dashboard')
                ->with('success', 'Left trial successfully');
        } catch (\Exception $e) {
            Log::error('Failed to leave trial', [
                'user_id' => Auth::id(),
                'trial_id' => $trial_id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to leave trial');
        }
    }

    /**
     * Get the player's trial history.
     *
     * @return \Illuminate\View\View
     */
    public function history(): View
    {
        $history = $this->playerService->getPlayerHistory(Auth::id());
        return view('player.history', compact('history'));
    }

    /**
     * Get the player's statistics.
     *
     * @return \Illuminate\View\View
     */
    public function stats(): View
    {
        $stats = $this->playerService->getPlayerStats(Auth::id());
        return view('player.stats', compact('stats'));
    }

    /**
     * Show the player's home page.
     *
     * @return \Illuminate\View\View
     */
    public function home(): View
    {
        $user = Auth::user();
        $trial = Trial::where('user_id', $user->id)->first();
        
        return view('player.home', compact('user', 'trial'));
    }

    /**
     * Handle ping requests from the player.
     *
     * @param int $last_sol
     * @param int $last_msg
     * @return \Illuminate\Http\JsonResponse
     */
    public function ping(int $last_sol, int $last_msg): JsonResponse
    {
        $user = Auth::user();
        $trial = Trial::where('user_id', $user->id)->first();

        if (!$trial) {
            return response()->json(['error' => 'No active trial found']);
        }

        $new_solutions = Solution::where('trial_id', $trial->id)
            ->where('id', '>', $last_sol)
            ->get();

        $new_messages = Message::where('trial_id', $trial->id)
            ->where('id', '>', $last_msg)
            ->get();

        return response()->json([
            'solutions' => $new_solutions,
            'messages' => $new_messages
        ]);
    }

    /**
     * Show the player's trial page.
     *
     * @return \Illuminate\View\View
     */
    public function playerTrial(): View
    {
        $user = Auth::user();
        $trial = Trial::where('user_id', $user->id)->first();
        
        return view('player.trial', compact('user', 'trial'));
    }

    /**
     * Initialize a new trial for the player.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initializeTrial(): RedirectResponse
    {
        $user = Auth::user();
        
        // Check if user already has an active trial
        $existing_trial = Trial::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existing_trial) {
            return redirect()->route('player.trial');
        }

        // Create new trial
        $trial = new Trial();
        $trial->user_id = $user->id;
        $trial->status = 'active';
        $trial->save();

        return redirect()->route('player.trial');
    }

    /**
     * Show the queue status for the player.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function queueStatus(): JsonResponse
    {
        $user = Auth::user();
        $queue = Queue::where('user_id', $user->id)->first();

        return response()->json([
            'position' => $queue ? $queue->position : null,
            'status' => $queue ? $queue->status : 'not_in_queue'
        ]);
    }

    /**
     * Show the trial instructions.
     *
     * @return \Illuminate\View\View
     */
    public function showInstructions(): View
    {
        return view('player.instructions');
    }

    /**
     * End the current trial round.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function endTrialRound(): RedirectResponse
    {
        $user = Auth::user();
        $trial = Trial::where('user_id', $user->id)->first();

        if ($trial) {
            $round = Round::where('trial_id', $trial->id)
                ->where('status', 'active')
                ->first();

            if ($round) {
                $round->status = 'completed';
                $round->save();
            }
        }

        return redirect()->route('player.trial');
    }

    /**
     * Show the post-trial survey.
     *
     * @return \Illuminate\View\View
     */
    public function showPostTrialSurvey(): View
    {
        return view('player.post-trial-survey');
    }

    /**
     * Start a new trial round.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startTrialRound(): RedirectResponse
    {
        $user = Auth::user();
        $trial = Trial::where('user_id', $user->id)->first();

        if ($trial) {
            $round = new Round();
            $round->trial_id = $trial->id;
            $round->status = 'active';
            $round->save();
        }

        return redirect()->route('player.trial');
    }

    /**
     * End the current trial.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function endTrial(): RedirectResponse
    {
        $user = Auth::user();
        $trial = Trial::where('user_id', $user->id)->first();

        if ($trial) {
            $trial->status = 'completed';
            $trial->save();
        }

        return redirect()->route('player.home');
    }

    /**
     * Handle when a trial is stopped.
     *
     * @return \Illuminate\View\View
     */
    public function trialStopped(): View
    {
        return view('player.trial-stopped');
    }

    /**
     * End the current task.
     *
     * @param string $reason
     * @return \Illuminate\Http\RedirectResponse
     */
    public function endTask(string $reason): RedirectResponse
    {
        $user = Auth::user();
        $trial = Trial::where('user_id', $user->id)->first();

        if ($trial) {
            $trial->status = 'completed';
            $trial->end_reason = $reason;
            $trial->save();
        }

        return redirect()->route('player.home');
    }

    /**
     * Post a new solution.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSolution(Request $request): JsonResponse
    {
        $user = Auth::user();
        $trial = Trial::where('user_id', $user->id)->first();

        if (!$trial) {
            return response()->json(['error' => 'No active trial found'], 400);
        }

        $solution = new Solution();
        $solution->trial_id = $trial->id;
        $solution->content = $request->input('content');
        $solution->save();

        return response()->json(['success' => true]);
    }
}
