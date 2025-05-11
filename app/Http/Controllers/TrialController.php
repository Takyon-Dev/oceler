<?php

namespace App\Http\Controllers;

use App\Models\Trial;
use App\Models\Queue;
use App\Models\Factoidset;
use App\Models\Network;
use App\Models\Nameset;
use App\Models\User;
use App\Models\Solution;
use App\Models\MturkHit;
use App\Services\TrialService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class TrialController extends Controller
{
    /**
     * @var TrialService
     */
    private TrialService $trialService;

    /**
     * Create a new controller instance.
     *
     * @param TrialService $trialService
     */
    public function __construct(TrialService $trialService)
    {
        $this->trialService = $trialService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of trials.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $trials = $this->trialService->getRecentTrials();
        return view('admin.trials', compact('trials'));
    }

    /**
     * Show the form for creating a new trial.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $factoidsets = Factoidset::pluck('name', 'id');
        $networks = Network::pluck('name', 'id');
        $namesets = Nameset::pluck('name', 'id');

        return view('admin.trial-config', compact('factoidsets', 'networks', 'namesets'));
    }

    /**
     * Store a newly created trial in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'trial_type' => 'required|integer',
                'passing_score' => 'required|numeric',
                'instructions' => 'required|string',
                'distribution_interval' => 'required|integer',
                'num_waves' => 'required|integer',
                'num_players' => 'required|integer',
                'num_to_recruit' => 'required|integer',
                'unique_factoids' => 'boolean',
                'pay_correct' => 'numeric',
                'pay_time_factor' => 'numeric',
                'payment_per_solution' => 'required|numeric',
                'payment_base' => 'required|numeric',
                'num_rounds' => 'required|integer',
                'num_groups' => 'required|integer',
                'instructions_image' => 'nullable|image|max:2048',
                'factoidset_id.*' => 'required|exists:factoidsets,id',
                'nameset_id.*' => 'required|exists:namesets,id',
                'network_id.*' => 'required|exists:networks,id',
                'survey_url.*' => 'nullable|url',
                'round_timeout.*' => 'required|integer'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $trial = $this->trialService->createTrial($request->all());
            Log::info('Trial created', ['trial_id' => $trial->id]);

            return redirect()->route('admin.trials')
                ->with('success', 'Trial created successfully');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Trial creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to create trial');
        }
    }

    /**
     * Remove the specified trial from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->trialService->deleteTrial($id);
            Log::info('Trial deleted', ['trial_id' => $id]);

            return redirect()->route('admin.trials')
                ->with('success', 'Trial deleted successfully');
        } catch (\Exception $e) {
            Log::error('Trial deletion failed', [
                'trial_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to delete trial');
        }
    }

    /**
     * Toggle the active status of a trial.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(int $id): RedirectResponse
    {
        try {
            $trial = $this->trialService->toggleTrialStatus($id);
            $status = $trial->is_active ? 'activated' : 'deactivated';
            
            Log::info("Trial {$status}", [
                'trial_id' => $id,
                'type' => $trial->trial_type,
                'num_players' => $trial->num_players
            ]);

            return redirect()->route('admin.trials')
                ->with('success', "Trial {$status} successfully");
        } catch (\Exception $e) {
            Log::error('Trial status toggle failed', [
                'trial_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to toggle trial status');
        }
    }

    /**
     * Stop a specific trial.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopTrial(int $id): RedirectResponse
    {
        try {
            $this->trialService->stopTrial($id);
            Log::info('Trial stopped by admin', ['trial_id' => $id]);

            return redirect()->route('admin.trials')
                ->with('success', 'Trial stopped successfully');
        } catch (\Exception $e) {
            Log::error('Trial stop failed', [
                'trial_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to stop trial');
        }
    }

    /**
     * Stop all active trials.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopAllTrials(): RedirectResponse
    {
        try {
            $this->trialService->stopAllTrials();
            Log::info('All trials stopped by admin');

            return redirect()->route('admin.trials')
                ->with('success', 'All trials stopped successfully');
        } catch (\Exception $e) {
            Log::error('Stop all trials failed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to stop all trials');
        }
    }

    /**
     * Display the trial queue for players.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function enterQueue(): View|RedirectResponse
    {
        try {
            $user = Auth::user();
            $result = $this->trialService->enterQueue($user);

            if ($result['redirect']) {
                return redirect()->route('player.instructions');
            }

            return view('player.queue');
        } catch (\Exception $e) {
            Log::error('Queue entry failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to enter queue');
        }
    }

    /**
     * Display the specified trial.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function getTrial(int $id): View
    {
        $trial = $this->trialService->getTrialDetails($id);
        return view('admin.trial-view', $trial);
    }

    /**
     * Show the form for editing the specified trial.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editTrial(int $id): View
    {
        $trial = $this->trialService->getTrialForEdit($id);
        return view('admin.trial-config', $trial);
    }

    /**
     * Update the specified trial in storage.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTrial(int $id, Request $request): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'distribution_interval' => 'required|integer',
                'num_players' => 'required|integer',
                'mult_factoid' => 'nullable|numeric',
                'pay_correct' => 'nullable|numeric',
                'pay_time_factor' => 'nullable|numeric',
                'payment_per_solution' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $this->trialService->updateTrial($id, $request->all());
            Log::info('Trial updated', ['trial_id' => $id]);

            return redirect()->route('admin.trials')
                ->with('success', 'Trial updated successfully');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Trial update failed', [
                'trial_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to update trial');
        }
    }

    /**
     * Get all active trial players and their solutions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListenAllTrialPlayers(): JsonResponse
    {
        try {
            $trials = $this->trialService->getActiveTrialsWithPlayers();
            return response()->json($trials);
        } catch (\Exception $e) {
            Log::error('Failed to get active trial players', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get active trial players'], 500);
        }
    }

    /**
     * Get players and their solutions for a specific trial.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListenTrialPlayers(int $id): JsonResponse
    {
        try {
            $trial = $this->trialService->getTrialWithPlayers($id);
            return response()->json($trial);
        } catch (\Exception $e) {
            Log::error('Failed to get trial players', [
                'trial_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get trial players'], 500);
        }
    }

    /**
     * Manage the queue of players waiting to join a trial.
     *
     * @return int
     */
    public function queue(): int
    {
        try {
            return $this->trialService->manageQueue();
        } catch (\Exception $e) {
            Log::error('Queue management failed', [
                'error' => $e->getMessage()
            ]);

            return -1;
        }
    }

    /**
     * Handle when a trial is stopped.
     *
     * @return \Illuminate\View\View
     */
    public function trialStopped(): View
    {
        try {
            $this->trialService->handleTrialStopped(Auth::id());
            Log::info('Trial stopped page viewed', ['user_id' => Auth::id()]);
            
            return view('player.trial-stopped');
        } catch (\Exception $e) {
            Log::error('Trial stopped page failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return view('player.trial-stopped');
        }
    }

    /**
     * Check the status of instructions for a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function instructionsStatus(int $trial_id): JsonResponse
    {
        try {
            $status = $this->trialService->getInstructionsStatus($trial_id, Auth::id());
            return response()->json($status);
        } catch (\Exception $e) {
            Log::error('Failed to get instructions status', [
                'trial_id' => $trial_id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get instructions status'], 500);
        }
    }

    /**
     * Mark instructions as read for a user.
     *
     * @param int $user_id
     * @return void
     */
    public function markInstructionsAsRead(int $user_id): void
    {
        try {
            $this->trialService->markInstructionsAsRead($user_id);
            Log::info('Instructions marked as read', ['user_id' => $user_id]);
        } catch (\Exception $e) {
            Log::error('Failed to mark instructions as read', [
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle when a user is not selected for a trial.
     *
     * @param int $trial_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function notSelectedForTrial(int $trial_id): RedirectResponse
    {
        try {
            $this->trialService->handleNotSelected($trial_id, Auth::id());
            return redirect()->route('player.end-task', ['reason' => 'overrecruited']);
        } catch (\Exception $e) {
            Log::error('Failed to handle not selected status', [
                'trial_id' => $trial_id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to process selection status');
        }
    }

    /**
     * Manage the queue of players.
     *
     * @return void
     */
    public function manageQueue(): void
    {
        try {
            $this->trialService->manageQueue();
        } catch (\Exception $e) {
            Log::error('Queue management failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
