<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Models\Round;
use App\Models\Trial;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SearchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param \App\Services\SearchService $searchService
     * @return void
     */
    public function __construct(
        private readonly SearchService $searchService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display the search interface.
     *
     * @param \App\Models\Trial $trial
     * @param \App\Models\Round $round
     * @return \Illuminate\View\View
     */
    public function index(Trial $trial, Round $round): View
    {
        $user = Auth::user();
        $searchHistory = $this->searchService->getSearchHistory($user, $round);
        $searchStats = $this->searchService->getUserSearchStats($user, $round);
        $foundFactoids = $this->searchService->getFoundFactoids($round);

        return view('search.index', compact('trial', 'round', 'searchHistory', 'searchStats', 'foundFactoids'));
    }

    /**
     * Perform a search.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Trial $trial
     * @param \App\Models\Round $round
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, Trial $trial, Round $round): JsonResponse
    {
        $request->validate([
            'search_term' => 'required|string|max:255'
        ]);

        $user = Auth::user();
        $result = $this->searchService->searchFactoid($user, $round, $request->search_term);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }

        return response()->json([
            'success' => true,
            'search' => $result['search'],
            'factoid' => $result['factoid']
        ]);
    }

    /**
     * Get search suggestions.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Trial $trial
     * @param \App\Models\Round $round
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(Request $request, Trial $trial, Round $round): JsonResponse
    {
        $request->validate([
            'prefix' => 'required|string|max:255'
        ]);

        $suggestions = $this->searchService->getSearchSuggestions($round, $request->prefix);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Get search statistics.
     *
     * @param \App\Models\Trial $trial
     * @param \App\Models\Round $round
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Trial $trial, Round $round): JsonResponse
    {
        $stats = $this->searchService->getSearchStats($round);
        $successfulSearches = $this->searchService->getSuccessfulSearches($round);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'successful_searches' => $successfulSearches
        ]);
    }

    /**
     * Get user's search history.
     *
     * @param \App\Models\Trial $trial
     * @param \App\Models\Round $round
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Trial $trial, Round $round): JsonResponse
    {
        $user = Auth::user();
        $searchHistory = $this->searchService->getSearchHistory($user, $round);

        return response()->json([
            'success' => true,
            'history' => $searchHistory
        ]);
    }

    /**
     * Check if a factoid has been found.
     *
     * @param \App\Models\Trial $trial
     * @param \App\Models\Round $round
     * @param int $factoid_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkFactoid(Trial $trial, Round $round, int $factoid_id): JsonResponse
    {
        $isFound = $this->searchService->isFactoidFound($round, $factoid_id);

        return response()->json([
            'success' => true,
            'found' => $isFound
        ]);
    }

    /**
     * Search for users.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function users(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $users = User::where('name', 'like', '%' . $validated['query'] . '%')
            ->orWhere('email', 'like', '%' . $validated['query'] . '%')
            ->select('id', 'name', 'email')
            ->take(10)
            ->get();

        return response()->json($users);
    }

    /**
     * Search for trials.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $trials = Trial::where('name', 'like', '%' . $validated['query'] . '%')
            ->orWhere('description', 'like', '%' . $validated['query'] . '%')
            ->with('users')
            ->take(10)
            ->get();

        return response()->json($trials);
    }

    /**
     * Search for messages.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function messages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $messages = Message::where(function($query) {
            $query->where('sender_id', Auth::id())
                ->orWhere('receiver_id', Auth::id());
        })
        ->where(function($query) use ($validated) {
            $query->where('subject', 'like', '%' . $validated['query'] . '%')
                ->orWhere('body', 'like', '%' . $validated['query'] . '%');
        })
        ->with('sender', 'receiver')
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

        return response()->json($messages);
    }

    /**
     * Perform a global search.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function global(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $results = [
            'users' => User::where('name', 'like', '%' . $validated['query'] . '%')
                ->orWhere('email', 'like', '%' . $validated['query'] . '%')
                ->select('id', 'name', 'email')
                ->take(5)
                ->get(),
            'trials' => Trial::where('name', 'like', '%' . $validated['query'] . '%')
                ->orWhere('description', 'like', '%' . $validated['query'] . '%')
                ->with('users')
                ->take(5)
                ->get(),
            'messages' => Message::where(function($query) {
                $query->where('sender_id', Auth::id())
                    ->orWhere('receiver_id', Auth::id());
            })
            ->where(function($query) use ($validated) {
                $query->where('subject', 'like', '%' . $validated['query'] . '%')
                    ->orWhere('body', 'like', '%' . $validated['query'] . '%');
            })
            ->with('sender', 'receiver')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
        ];

        return response()->json($results);
    }
}
