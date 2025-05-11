<?php

namespace App\Models;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SoftDeletingTrait;
use DB;
use Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log as FacadesLog;

class Trial extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'start_time',
        'end_time',
        'config',
        'admin_id',
        'trial_type',
        'passing_score',
        'instructions',
        'distribution_interval',
        'num_waves',
        'num_players',
        'num_to_recruit',
        'unique_factoids',
        'pay_correct'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'deleted_at' => 'datetime',
        'unique_factoids' => 'boolean',
        'pay_correct' => 'boolean',
        'passing_score' => 'integer',
        'distribution_interval' => 'integer',
        'num_waves' => 'integer',
        'num_players' => 'integer',
        'num_to_recruit' => 'integer'
    ];

    /**
     * Get the admin associated with the trial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the rounds for the trial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    /**
     * Get the current round for the trial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentRound(): HasOne
    {
        return $this->hasOne(Round::class)->where('status', 'active');
    }

    /**
     * Get the users that belong to the trial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        try {
            return $this->belongsToMany(User::class)
                ->withPivot('group_id', 'instructions_read', 'last_ping', 'selected_for_removal')
                ->withTimestamps();
        } catch (\Exception $e) {
            FacadesLog::error('Error accessing users relationship', ['error' => $e->getMessage()]);
            return $this->belongsToMany(User::class);
        }
    }

    /**
     * Get the solutions for the trial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function solutions(): HasMany
    {
        return $this->hasMany(Solution::class);
    }

    /**
     * Get the groups for the trial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Get the archived users for the trial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function archive(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trial_user_archive', 'trial_id', 'user_id');
    }

    /**
     * Get the messages for the trial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Start the trial.
     *
     * @return bool
     */
    public function start(): bool
    {
        $this->status = 'active';
        $this->start_time = now();
        $result = $this->save();

        if ($result) {
            FacadesLog::info('Trial started', ['trial_id' => $this->id]);
        }

        return $result;
    }

    /**
     * Stop the trial.
     *
     * @return bool
     */
    public function stop(): bool
    {
        $this->status = 'completed';
        $this->end_time = now();
        $result = $this->save();

        if ($result) {
            FacadesLog::info('Trial stopped', ['trial_id' => $this->id]);
        }

        return $result;
    }

    /**
     * Check if the trial is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the trial is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the player count for the trial.
     *
     * @return int
     */
    public function getPlayerCount(): int
    {
        return $this->users()->count();
    }

    /**
     * Get the active round for the trial.
     *
     * @return \App\Models\Round|null
     */
    public function getActiveRound(): ?Round
    {
        return $this->rounds()->where('status', 'active')->first();
    }

    /**
     * Get the config value for the trial.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Store trial configuration from request or array.
     *
     * @param \Illuminate\Http\Request|array $input
     * @return bool
     */
    public function storeTrialConfig(Request|array $input): bool
    {
        try {
            if ($input instanceof Request) {
                $this->name = $input->name;
                $this->trial_type = $input->trial_type;
                $this->passing_score = $input->passing_score;
                $this->instructions = $input->instructions;
                $this->distribution_interval = $input->distribution_interval;
                $this->num_waves = $input->num_waves;
                $this->num_players = $input->num_players;
                $this->num_to_recruit = $input->num_to_recruit;
                $this->unique_factoids = $input->unique_factoids ?? false;
                $this->pay_correct = $input->pay_correct ?? false;
            } else {
                $this->config = $input;
            }

            return $this->save();
        } catch (\Exception $e) {
            FacadesLog::error('Error storing trial config', [
                'trial_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /*
     * Adds a new trial based on a config file.
     */
    public static function addTrialFromConfig(array $config): void
    {
        foreach ($config['trials'] as $trial_config) {
            $trial = new self();
            $request = new Request();
            $request->merge($trial_config);

            try {
                $trial->storeTrialConfig($request);
                $trial->logConfig();
            } catch (\Exception $e) {
                FacadesLog::error('Error adding trial from config', [
                    'config' => $trial_config,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    public function stopTrial(): void
    {
        try {
            $this->is_active = 0;
            $this->save();

            $this_users = FacadesDB::table('trial_user')
                ->where('trial_id', $this->id)
                ->get();

            foreach ($this_users as $this_user) {
                $this->removePlayerFromTrial($this_user->user_id, false, false);
            }
        } catch (\Exception $e) {
            FacadesLog::error('Error stopping trial', [
                'trial_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function removePlayerFromTrial(int $user_id, bool $completed_trial, bool $passed_trial): void
    {
        try {
            FacadesLog::info('Removing player from trial', [
                'user_id' => $user_id,
                'trial_id' => $this->id
            ]);

            $this_user = FacadesDB::table('trial_user')
                ->where('user_id', $user_id)
                ->first();

            if ($this_user && $this_user->group_id) {
                FacadesDB::table('trial_user_archive')->insert([
                    'created_at' => now(),
                    'updated_at' => now(),
                    'trial_id' => $this->id,
                    'user_id' => $user_id,
                    'trial_type' => $this->trial_type,
                    'group_id' => $this_user->group_id,
                    'last_ping' => $this_user->last_ping,
                    'completed_trial' => $completed_trial,
                    'trial_passed' => $passed_trial
                ]);

                FacadesDB::table('trial_user')
                    ->where('id', $this_user->id)
                    ->delete();
            } else {
                FacadesLog::info('User not found in trial_user table', [
                    'user_id' => $user_id,
                    'trial_id' => $this->id
                ]);
            }

            if (FacadesDB::table('trial_user')
                ->where('trial_id', $this->id)
                ->count() === 0) {
                $this->is_active = 0;
                $this->save();
            }
        } catch (\Exception $e) {
            FacadesLog::error('Error removing player from trial', [
                'user_id' => $user_id,
                'trial_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function logConfig(): void
    {
        try {
            $config = "\n================================================\nTrial Config:\n";
            $config .= "Name: " . $this->name . "\n";
            $config .= "Type: " . $this->trial_type . "\n";
            $config .= "Dist. Interval: " . $this->distribution_interval . "\n";
            $config .= "Num Waves: " . $this->num_waves . "\n";
            $config .= "Num Players: " . $this->num_players . "\n";
            $config .= "Unique Factoids: " . $this->unique_factoids . "\n";
            $config .= "Pay Correct Answers: " . $this->pay_correct . "\n";
            $config .= "Pay Time Factor: " . $this->pay_time_factor . "\n";
            $config .= "Pay Per Solution: " . $this->payment_per_solution . "\n";
            $config .= "Base Payment: " . $this->payment_base . "\n";
            $config .= "Num Rounds: " . $this->num_rounds . "\n";

            $rounds = Round::where('trial_id', $this->id)
                ->orderBy('round', 'ASC')
                ->get();

            foreach ($rounds as $round) {
                $factoidset = Factoidset::where('id', $round->factoidset_id)->first();
                $nameset = Nameset::where('id', $round->nameset_id)->first();
                $config .= "Round " . $round->round . " :\n";
                $config .= "Factoid set: " . $factoidset->name . "\n";
                $config .= "Name set: " . $nameset->name . "\n";
            }

            $config .= "Num Groups: " . $this->num_groups . "\n";
            $config .= "Networks:\n\n";

            $groups = Group::where('trial_id', $this->id)
                ->orderBy('group', 'ASC')
                ->with('network')
                ->get();

            foreach ($groups as $group) {
                $network = FacadesDB::table('networks')
                    ->where('id', '=', $group->network_id)
                    ->first();
                $config .= "Group " . $group->group . ", Network " . $network->name . ":\n";
                $config .= Network::getAdjacencyMatrix($group->network_id);
                $config .= "\n";
            }

            $config .= "\n================================================\n";

            FacadesLog::info('Trial config logged', [
                'trial_id' => $this->id,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            FacadesLog::error('Error logging trial config', [
                'trial_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

}
