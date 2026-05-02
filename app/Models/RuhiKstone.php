<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuhiKstone extends Model
{
    use SoftDeletes;

    protected $table = 'r_kstone';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'color_id',
        'quantity',
        'stoneweight',
        'dieweight',
        'create_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'stoneweight' => 'float',
        'dieweight' => 'float',
    ];

    public function color(): BelongsTo
    {
        return $this->belongsTo(RuhiKstoneColor::class, 'color_id', 'id');
    }

    public function itemKstones(): HasMany
    {
        return $this->hasMany(RuhiItemKstone::class, 'kstone_id', 'id');
    }
}
