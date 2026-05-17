<?php

namespace App\Models;

use App\Enums\RecurringChargeCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringCharge extends Model
{
    protected $fillable = [
        'client_id',
        'project_id',
        'title',
        'category',
        'bundle_items',
        'amount',
        'vat_rate',
        'due_day',
        'due_month',
        'interval_years',
        'start_year',
        'notify_client',
        'active',
        'notes',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'category'      => RecurringChargeCategory::class,
        'bundle_items'  => 'array',
        'notify_client' => 'boolean',
        'active'        => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }

    public function nextDueDate(): Carbon
    {
        $interval   = $this->interval_years ?? 1;
        $startYear  = $this->start_year ?? now()->year;
        $currentYear = now()->year;

        if ($interval === 1) {
            $candidate = Carbon::create($currentYear, $this->due_month, $this->due_day)->startOfDay();
            return $candidate->isPast() ? $candidate->addYear() : $candidate;
        }

        // Find the next year >= currentYear that falls on the correct cycle
        $offset = ($currentYear - $startYear) % $interval;
        $targetYear = $offset === 0 ? $currentYear : $currentYear + ($interval - $offset);

        $candidate = Carbon::create($targetYear, $this->due_month, $this->due_day)->startOfDay();

        // If that date has already passed, jump one full interval ahead
        if ($candidate->isPast()) {
            $candidate->addYears($interval);
        }

        return $candidate;
    }

    public function intervalLabel(): string
    {
        return $this->interval_years === 1 ? 'Annual' : 'Every ' . $this->interval_years . ' years';
    }

    public function dueDateLabel(): string
    {
        return Carbon::create(null, $this->due_month)->format('F') . ' ' . $this->due_day;
    }
}
