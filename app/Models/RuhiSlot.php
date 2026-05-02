<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuhiSlot extends Model
{
    protected $table = 'r_slot';

    public $timestamps = false;

    protected $fillable = [
        'gs_id',
        'slot_name',
    ];

    public function gs(): BelongsTo
    {
        return $this->belongsTo(RuhiGs::class, 'gs_id', 'id');
    }

    public function lotItems(): HasMany
    {
        return $this->hasMany(RuhiGsOrderByColor::class, 'lot_id', 'id');
    }
}
