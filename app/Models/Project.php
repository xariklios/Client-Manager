<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'domain',
        'url',
        'hosting_plan',
        'maintenance_plan',
        'tech_notes',
        'start_date',
        'renewal_date',
        'domain_expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'status'       => ProjectStatus::class,
        'start_date'   => 'date',
        'renewal_date'       => 'date',
        'domain_expiry_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}
