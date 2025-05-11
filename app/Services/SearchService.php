<?php

namespace App\Services;

use App\Models\Search;
use App\Models\User;
use App\Models\Trial;
use App\Models\Round;
use App\Models\Factoid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SearchService
{
    /**
     * Perform a search for a factoid.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Round $round
     * @param string $search_term
     * @return array
     */
    public function searchFactoid(User $user, Round $round, string $search_term): array
    {
        try {
            $factoid = $this->findMatchingFactoid($round, $search_term);
            $success = $factoid !== null;

            $search = DB::transaction(function () use ($user, $round, $search_term, $factoid, $success) {
                $search = Search::create([
                    'user_id' => $user->id,
                    'trial_id' => $round->trial_id,
                    'round_id' => $round->id,
                    'search_term' => $search_term,
                    'factoid_id' => $factoid?->id,
                    'result' => $success ? 'found' : 'not_found',
                    'success' => $success,
                    'created_at' => Carbon::now()
                ]);

                Log::info('Search performed', [
                    'user_id' => $user->id,
                    'round_id' => $round->id,
                    'search_term' => $search_term,
                    'success' => $success
                ]);

                return $search;
            });

            return [
                'success' => true,
                'search' => $search,
                'factoid' => $factoid
            ];
        } catch (\Exception $e) {
            Log::error('Failed to perform search', [
                'user_id' => $user->id,
                'round_id' => $round->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to perform search'
            ];
        }
    }

    /**
     * Find a matching factoid for the search term.
     *
     * @param \App\Models\Round $round
     * @param string $search_term
     * @return \App\Models\Factoid|null
     */
    private function findMatchingFactoid(Round $round, string $search_term): ?Factoid
    {
        return $round->factoidset->factoids()
            ->where('content', 'like', '%' . $search_term . '%')
            ->first();
    }

    /**
     * Get search history for a user in a round.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Round $round
     * @return \Illuminate\Support\Collection
     */
    public function getSearchHistory(User $user, Round $round): Collection
    {
        return Search::where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->with('factoid')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get successful searches for a round.
     *
     * @param \App\Models\Round $round
     * @return \Illuminate\Support\Collection
     */
    public function getSuccessfulSearches(Round $round): Collection
    {
        return Search::where('round_id', $round->id)
            ->where('success', true)
            ->with(['user', 'factoid'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get search statistics for a round.
     *
     * @param \App\Models\Round $round
     * @return array
     */
    public function getSearchStats(Round $round): array
    {
        $total_searches = Search::where('round_id', $round->id)->count();
        $successful_searches = Search::where('round_id', $round->id)
            ->where('success', true)
            ->count();
        $unique_factoids_found = Search::where('round_id', $round->id)
            ->where('success', true)
            ->distinct('factoid_id')
            ->count('factoid_id');

        return [
            'total_searches' => $total_searches,
            'successful_searches' => $successful_searches,
            'unique_factoids_found' => $unique_factoids_found,
            'success_rate' => $total_searches > 0 ? ($successful_searches / $total_searches) * 100 : 0
        ];
    }

    /**
     * Get search statistics for a user in a round.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Round $round
     * @return array
     */
    public function getUserSearchStats(User $user, Round $round): array
    {
        $user_searches = Search::where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->get();

        $total_searches = $user_searches->count();
        $successful_searches = $user_searches->where('success', true)->count();
        $unique_factoids_found = $user_searches->where('success', true)
            ->pluck('factoid_id')
            ->unique()
            ->count();

        return [
            'total_searches' => $total_searches,
            'successful_searches' => $successful_searches,
            'unique_factoids_found' => $unique_factoids_found,
            'success_rate' => $total_searches > 0 ? ($successful_searches / $total_searches) * 100 : 0
        ];
    }

    /**
     * Check if a factoid has been found in a round.
     *
     * @param \App\Models\Round $round
     * @param int $factoid_id
     * @return bool
     */
    public function isFactoidFound(Round $round, int $factoid_id): bool
    {
        return Search::where('round_id', $round->id)
            ->where('factoid_id', $factoid_id)
            ->where('success', true)
            ->exists();
    }

    /**
     * Get all found factoids in a round.
     *
     * @param \App\Models\Round $round
     * @return \Illuminate\Support\Collection
     */
    public function getFoundFactoids(Round $round): Collection
    {
        return Factoid::whereIn('id', function ($query) use ($round) {
            $query->select('factoid_id')
                ->from('searches')
                ->where('round_id', $round->id)
                ->where('success', true);
        })->get();
    }

    /**
     * Get search suggestions based on previous searches.
     *
     * @param \App\Models\Round $round
     * @param string $prefix
     * @return array
     */
    public function getSearchSuggestions(Round $round, string $prefix): array
    {
        return Search::where('round_id', $round->id)
            ->where('search_term', 'like', $prefix . '%')
            ->select('search_term')
            ->distinct()
            ->pluck('search_term')
            ->toArray();
    }
} 