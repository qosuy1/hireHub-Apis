<?php

namespace App\Models;

use App\Enums\AvailabilityStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FreelancerProfile extends Model
{
    /** @use HasFactory<\Database\Factories\FreelancerFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'phone',
        'bio',
        'hourly_rate',
        'avatar',
        'portfolio_links',
        // 'skills_summary',
        'availability_status',
        'average_rating',

    ];
    protected $appends = ['avatar_url', 'display_rating', 'portfolio_links', 'hourly_rate'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hourly_rate' => 'float',
            'is_verified' => 'boolean',
            // 'skills_summary' => 'array',
            'portfolio_links' => 'array',
            'verified_at' => 'timestamp',
        ];
    }

    // ====================== Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * all skills associated with freelancer profile.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'freelancer_profile_skill')
            ->withPivot('experience_years');
        // ->withTimestamps();
    }

    /**
     * all offers received from this freelancer profile.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'freelancer_id', 'user_id');
    }

    /**
     * all reviews for this freelancer profile
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function acceptedProjects(): HasManyThrough
    {
        return $this->hasManyThrough(
            Project::class,
            Offer::class,
            'freelancer_id',
            'id',
            'user_id',
            'project_id'
        )->where('offers.status', 'accepted')->latest();
    }

    // ====================== Scopes
    public function scopeAvaliability(Builder $query, string $status): void
    {
        if (in_array($status, AvailabilityStatusEnum::getValues()))
            $query->where('availability_status', $status);
    }
    /**
     * get highest rated freelancers
     */
    public function scopeBestRated(Builder $query): void
    {
        $query->orderByDesc('average_rating');
    }


    // ====================== Helpers Accessors
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => str_replace([' ', '-', '(', ')'], '', $value),
        );
    }
    protected function hourlyRate(): Attribute
    {
        return Attribute::make(
            set: fn($value) => round((float) $value, 2),
            get: fn($value) => round((float) $value, 2),
        );
    }
    protected function portfolioLinks(): Attribute
    {
        return Attribute::make(
            set: fn($value) => json_encode($value),
            get: fn($value) => json_decode($value, true) ?: [],
        );
    }
    // protected function skillsSummary(): Attribute
    // {
    //     return Attribute::make(
    //         set: fn($value) => json_encode($value),
    //         get: fn($value) => json_decode($value, true) ?: [],
    //     );
    // }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $avatar = $this->avatar;
                if (blank($avatar))
                    return asset('images/default_avatar.jpg');
                return asset('storage/' . $avatar);
            }
        );
    }


    // I have added "average_rating" column in freelancer_profiles to display the rate without relations and calculations
    //  I used "ReviewObserver" event =>  when make any operation on the Review model it's update the avg(rating)
    //  in cases (created,  updated , deleted ) for any review and store the new value in freelancer_profiles.average_Rating
    protected function displayRating(): Attribute
    {
        return Attribute::make(
            get: function () {
                $avg = $this->average_rating;

                if ($avg == 0)
                    return "No Reviews Yet";
                return number_format($avg, 1) . " ⭐";
            }
        );
    }
}
