<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'budget',
        'delivery_date',
        'status',
    ];

    protected $appends = ['formatted_budget', 'left_days'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'delivery_date' => 'timestamp',
        ];
    }

    //==============Relations

    /**
     * get the user who posted the project
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all offers for the project
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
    public function acceptedOffer(): HasOne
    {
        return $this->hasOne(Offer::class)->where('status', 'accepted');
    }

    /**
     *all project tags
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'project_tag');
    }

    /**
     * all attachments for the project.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * get project review
     */
    public function review(): MorphOne
    {
        return $this->morphOne(Review::class, 'reviewable');
    }

    // ================= Scopes

    // filter open projects
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', 'open');
    }

    // filter the budget above amount
    public function scopeBudgetAbove(Builder $query, $amount): void
    {
        $query->when($amount, fn($q) => $q->where('budget', '>=', $amount));
    }

    /**
     * filter this month projects
     */
    public function scopeThisMonth(Builder $query): void
    {
        $query->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);
    }


    //======================== Accessor
    public function formattedBudget(): Attribute
    {
        return Attribute::get(function () {
            if ($this->type === 'fixed')
                return $this->budget . ' USD';
            if ($this->type === 'hourly')
                return $this->budget . '$/hr';
        });
    }

    public function leftDays(): Attribute
    {
        return Attribute::get(function (): int {
            $now = Carbon::now();
            $delivery_date = Carbon::parse($this->delivery_date);
            return (int) $now->diff($delivery_date)->format('%d');
        });
    }

    

}
