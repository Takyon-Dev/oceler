<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Solution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'trial_id',
        'round',
        'category_id',
        'solution',
        'confidence'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confidence' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the trial that owns the solution.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class);
    }

    /**
     * Get the current solutions for a user in a specific trial and round.
     *
     * @param int $userId
     * @param int $trialId
     * @param int $round
     * @return array
     */
    public static function getCurrentSolutions(int $userId, int $trialId, int $round): array
    {
        return DB::table('solutions')
            ->select('solutions.id', 'solutions.user_id', 'solutions.category_id', 
                    'solution_categories.name', 'solutions.solution', 
                    'solutions.confidence', 'solutions.created_at')
            ->join('solution_categories', 'solutions.category_id', '=', 'solution_categories.id')
            ->join(DB::raw('(
                SELECT user_id, category_id, MAX(created_at) AS mTime
                FROM solutions
                WHERE user_id = ? AND trial_id = ? AND round = ?
                GROUP BY user_id, category_id
            ) AS d'), function($join) {
                $join->on('solutions.user_id', '=', 'd.user_id')
                     ->on('solutions.category_id', '=', 'd.category_id')
                     ->on('solutions.created_at', '=', 'd.mTime');
            })
            ->setBindings([$userId, $trialId, $round])
            ->orderBy('solutions.id')
            ->get()
            ->toArray();
    }

    /**
     * Check solutions against answer key for a specific user, trial, and round.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Trial $trial
     * @param int $currentRound
     * @return array
     */
    public static function checkSolutions(User $user, Trial $trial, int $currentRound): array
    {
        $answers = AnswerKey::where('factoidset_id', $trial->rounds[$currentRound - 1]->factoidset_id)
            ->with('solutionCategories')
            ->get();

        $solutions = self::where('trial_id', $trial->id)
            ->where('round', $currentRound)
            ->where('user_id', $user->id)
            ->orderBy('category_id')
            ->orderBy('id')
            ->get();

        $solutionAnswers = [];

        foreach ($answers as $answer) {
            $categoryId = $answer->solution_category_id;
            $solutionAnswers[$categoryId] = [
                'name' => $answer->solutionCategories->name,
                'answer' => array_map('strtolower', (array)$answer->solution),
                'is_correct' => false,
                'time_correct' => 0,
                'times' => []
            ];
        }

        foreach ($solutions as $solution) {
            if (in_array(strtolower($solution->solution), $solutionAnswers[$solution->category_id]['answer'])) {
                $solutionAnswers[$solution->category_id]['is_correct'] = true;
                $timeCorrect = $solution->updated_at->diffInSeconds($solution->created_at);

                if ($timeCorrect <= 0) {
                    $roundEndTime = Carbon::parse($trial->rounds[$currentRound - 1]->start_time)
                        ->addMinutes($trial->rounds[$currentRound - 1]->round_timeout);
                    $timeCorrect = $roundEndTime->diffInSeconds($solution->created_at);
                }

                $solutionAnswers[$solution->category_id]['times'][] = $timeCorrect;
                $solutionAnswers[$solution->category_id]['time_correct'] += $timeCorrect;
            }
        }

        return $solutionAnswers;
    }

    /**
     * Get solution categories for a specific factoidset.
     *
     * @param int $factoidsetId
     * @return array
     */
    public static function getSolutionCategories(int $factoidsetId): array
    {
        return AnswerKey::where('factoidset_id', $factoidsetId)
            ->with('solutionCategories')
            ->groupBy('solution_category_id')
            ->get()
            ->map(function($answer) {
                return [
                    'name' => $answer->solutionCategories->name,
                    'id' => $answer->solutionCategories->id
                ];
            })
            ->toArray();
    }

    /**
     * Get latest solutions for a specific trial, round, and users.
     *
     * @param int $trialId
     * @param int $round
     * @param int $lastSolutionId
     * @param array $userIds
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLatestSolutions(int $trialId, int $round, int $lastSolutionId, array $userIds)
    {
        return self::whereIn('user_id', $userIds)
            ->where('id', '>', $lastSolutionId)
            ->where('trial_id', $trialId)
            ->where('round', $round)
            ->get();
    }

    /**
     * Get date time array for form selection.
     *
     * @return array
     */
    public static function dateTimeArray(): array
    {
        return [
            'months' => [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ],
            'minutes' => collect(range(0, 60))
                ->mapWithKeys(fn($i) => [str_pad($i, 2, '0', STR_PAD_LEFT) => str_pad($i, 2, '0', STR_PAD_LEFT)])
                ->toArray()
        ];
    }
}
