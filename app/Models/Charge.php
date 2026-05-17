<?php

namespace App\Models;

use App\Enums\ChargeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Charge extends Model
{
    protected $fillable = [
        'client_id',
        'project_id',
        'offer_id',
        'recurring_charge_id',
        'title',
        'description',
        'amount',
        'vat_rate',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'status'   => ChargeStatus::class,
        'due_date' => 'date',
        'amount'   => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recurringCharge(): BelongsTo
    {
        return $this->belongsTo(RecurringCharge::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(ChargeReminder::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function remainingBalance(): float
    {
        return max(0, (float) $this->amount - $this->totalPaid());
    }

    // Recalculate and save the status based on current payments total
    public function syncStatus(): void
    {
        $totalPaid = $this->totalPaid();

        $status = match(true) {
            $totalPaid <= 0                        => ChargeStatus::Unpaid,
            $totalPaid < (float) $this->amount     => ChargeStatus::PartiallyPaid,
            default                                => ChargeStatus::Paid,
        };

        $this->update(['status' => $status->value]);
    }

    // Charges that still need to be paid
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ChargeStatus::Unpaid->value,
            ChargeStatus::PartiallyPaid->value,
        ]);
    }

    // Unpaid charges past their due date
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->unpaid()->whereNotNull('due_date')->where('due_date', '<', now()->toDateString());
    }
}
