<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserTypeEnum;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        // 'phone',
        'username',
        'email',
        'password',
        'city_id',
        'type',
        'is_active',
        'verified_at'
    ];

    protected $appends = ['full_name', 'membership_date'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the freelancer profile associated with the user.
     */
    public function freelancerProfile()
    {
        return $this->hasOne(FreelancerProfile::class);
    }

    /**
     * Get the city
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the country through user's city
     */
    public function country() : HasOneThrough
    {
        return $this->hasOneThrough(
            Country::class,
            City::class,
            'id',
            'id',
            'city_id',
            'country_id'
        );
    }

    /**
     * get all projects posted by the user
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * get all offers posted by the user
     */
    public function offers()
    {
        return $this->hasMany(Offer::class, 'freelancer_id');
    }

    /**
     * get all reviews that writed by the user
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ==================== Scopes

    /**
     * filter freelancer type
     */
    public function scopeFreelancers(Builder $query)
    {
        return $query->where('type', UserTypeEnum::FREELANCER->value);
    }

    /**
     *  filter client users type
     */
    public function scopeClients(Builder $query)
    {
        return $query->where('type', UserTypeEnum::CLIENT->value);
    }

    /**
     *  filter active users
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     *  filter verified users
     */
    public function scopeVerified(Builder $query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * filter active verified freelancers
     */
    public function scopeActiveVerifiedFreelancers(Builder $query)
    {
        return $query->freelancers()
            ->active()
            ->verified();
    }




    // ====================Helpers

    /**
     * Check if the user is a freelancer.
     */
    public function isFreelancer(): bool
    {
        return $this->type === UserTypeEnum::FREELANCER->value;
    }
    public function isClient(): bool
    {
        return $this->type === UserTypeEnum::CLIENT->value;
    }

    /**
     * Check if the user is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    //============================= Accessors
    /**
     *  (month and year of registration)
     */
    protected function membershipDate(): Attribute
    {
        return Attribute::get(fn() => Carbon::parse($this->created_at)->format('F Y'));
    }
    /**
     * check if the user have profile
     */
    protected function hasProfile() : Attribute{
        return Attribute::get(function(){
            if($this->type === UserTypeEnum::CLIENT->value)
                return true;
            return $this->freelancerProfile()->exists();
        });
    }
    /**
     * get the full name of the user
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn() => "{$this->first_name} {$this->last_name}");
    }
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => bcrypt($value),
        );
    }
}
