<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuhiItemKstone extends Model
{
    protected $table = 'r_k_stone';

    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'kstone_id',
        'kstone_quantity',
        'kstone_weight',
        'kstone_dieweight',
        'red',
        'rg_red',
        'rg_green',
        'green',
        'white',
        'rodo',
    ];

    protected $casts = [
        'kstone_quantity' => 'integer',
        'kstone_weight' => 'float',
        'kstone_dieweight' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(RuhiProduct::class, 'item_id', 'id');
    }

    public function kstone(): BelongsTo
    {
        return $this->belongsTo(RuhiKstone::class, 'kstone_id', 'id');
    }
}
