<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargeReminder extends Model
{
    public $timestamps = false;

    protected $fillable = ['charge_id', 'days_offset', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }
}
