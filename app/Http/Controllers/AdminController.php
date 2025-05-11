<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Trial;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $users = User::with('trials')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $trials = Trial::with('users')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.dashboard', compact('users', 'trials'));
    }

    /**
     * Display the user management page.
     *
     * @return \Illuminate\View\View
     */
    public function users(): View
    {
        $users = User::with('trials')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Display the trials management page.
     *
     * @return \Illuminate\View\View
     */
    public function trials(): View
    {
        $trials = Trial::with('users')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.trials', compact('trials'));
    }

    /**
     * Display the queue management page.
     *
     * @return \Illuminate\View\View
     */
    public function queue(): View
    {
        $queue = Queue::with('user')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.queue', compact('queue'));
    }

    /**
     * Update a user's role.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserRole(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->role_id = $request->role_id;
        $user->save();

        Log::info('User ' . $id . ' role updated to ' . $request->role_id . ' by admin ' . Auth::id());

        return response()->json(['success' => true]);
    }

    /**
     * Delete a user.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        Log::info('User ' . $id . ' deleted by admin ' . Auth::id());

        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully');
    }

    /**
     * Get user statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserStats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('last_active', '>', Carbon::now()->subDay())->count(),
            'new_users_today' => User::where('created_at', '>', Carbon::today())->count(),
            'users_in_trials' => DB::table('trial_user')->distinct('user_id')->count(),
            'users_in_queue' => Queue::count()
        ];

        return response()->json($stats);
    }

    /**
     * Get trial statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrialStats(): JsonResponse
    {
        $stats = [
            'total_trials' => Trial::count(),
            'active_trials' => Trial::where('is_active', true)->count(),
            'completed_trials' => Trial::where('is_active', false)->count(),
            'trials_today' => Trial::where('created_at', '>', Carbon::today())->count(),
            'average_players_per_trial' => DB::table('trial_user')
                ->select(DB::raw('AVG(COUNT(*)) as avg_players'))
                ->groupBy('trial_id')
                ->value('avg_players')
        ];

        return response()->json($stats);
    }

    /**
     * Get queue statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQueueStats(): JsonResponse
    {
        $stats = [
            'total_in_queue' => Queue::count(),
            'active_in_queue' => Queue::where('updated_at', '>', Carbon::now()->subMinutes(5))->count(),
            'queue_by_type' => Queue::select('trial_type', DB::raw('COUNT(*) as count'))
                ->groupBy('trial_type')
                ->get()
                ->pluck('count', 'trial_type')
        ];

        return response()->json($stats);
    }

    /**
     * Clear the queue.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearQueue(): RedirectResponse
    {
        Queue::truncate();

        Log::info('Queue cleared by admin ' . Auth::id());

        return redirect()->route('admin.queue')
            ->with('success', 'Queue cleared successfully');
    }

    /**
     * Remove a user from the queue.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeFromQueue(int $id): RedirectResponse
    {
        Queue::where('user_id', $id)->delete();

        Log::info('User ' . $id . ' removed from queue by admin ' . Auth::id());

        return redirect()->route('admin.queue')
            ->with('success', 'User removed from queue');
    }

    /**
     * Display the admin settings page.
     *
     * @return \Illuminate\View\View
     */
    public function settings(): View
    {
        return view('admin.settings');
    }

    /**
     * Update admin settings.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trial_timeout' => 'required|integer|min:1',
            'queue_timeout' => 'required|integer|min:1',
            'max_trials_per_user' => 'required|integer|min:1',
            'min_players_per_trial' => 'required|integer|min:1',
            'max_players_per_trial' => 'required|integer|min:1'
        ]);

        foreach ($validated as $key => $value) {
            config(['app.' . $key => $value]);
        }

        Log::info('Admin settings updated by ' . Auth::id());

        return redirect()->route('admin.settings')
            ->with('success', 'Settings updated successfully');
    }
}
