<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'mturk_id',
        'password',
        'role_id',
        'status',
        'last_login_at',
        'settings'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'settings' => 'array'
    ];

    /**
     * Get the messages for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function messages(): BelongsToMany
    {
        return $this->belongsToMany(Message::class)->withTimestamps();
    }

    /**
     * Get the user's messages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function message(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the trials for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function trials(): BelongsToMany
    {
        try {
            return $this->belongsToMany(Trial::class)
                ->withPivot('group_id', 'last_ping', 'instructions_read', 'selected_for_removal')
                ->withTimestamps();
        } catch (\Exception $e) {
            Log::error('Error accessing trials relationship', ['error' => $e->getMessage()]);
            return $this->belongsToMany(Trial::class);
        }
    }

    /**
     * Get the user's last trial type.
     *
     * @return int
     */
    public function lastTrialType(): int
    {
        try {
            return DB::table('trial_user_archive')
                ->where('user_id', $this->id)
                ->where('completed_trial', 1)
                ->where('trial_passed', 1)
                ->max('trial_type') ?: 0;
        } catch (\Exception $e) {
            Log::error('Error getting last trial type', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get the user's role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the user has the given role(s).
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        try {
            $this->have_role = $this->getUserRole();

            // Check if the user is a root account
            if ($this->have_role && $this->have_role->name === 'Root') {
                return true;
            }

            if (is_array($roles)) {
                foreach ($roles as $need_role) {
                    if ($this->checkIfUserHasRole($need_role)) {
                        return true;
                    }
                }
            } else {
                return $this->checkIfUserHasRole($roles);
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error checking user role', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get the user's role.
     *
     * @return \App\Models\Role|null
     */
    private function getUserRole(): ?Role
    {
        try {
            return $this->role()->first();
        } catch (\Exception $e) {
            Log::error('Error getting user role', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $need_role
     * @return bool
     */
    private function checkIfUserHasRole(string $need_role): bool
    {
        return ($this->have_role && strtolower($need_role) === strtolower($this->have_role->name));
    }
}
