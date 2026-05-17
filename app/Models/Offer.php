<?php

namespace App\Models;

use App\Enums\OfferStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    protected $fillable = [
        'client_id',
        'project_id',
        'title',
        'description',
        'status',
        'total',
        'original_price',
        'vat_rate',
        'sent_date',
        'accepted_date',
        'notes',
    ];

    protected $casts = [
        'status'        => OfferStatus::class,
        'total'          => 'decimal:2',
        'original_price' => 'decimal:2',
        'sent_date'     => 'date',
        'accepted_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfferItem::class)->orderBy('sort_order');
    }
}
