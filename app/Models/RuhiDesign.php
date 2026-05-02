<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuhiDesign extends Model
{
    use SoftDeletes;

    protected $table = 'r_design';

    public $timestamps = false;

    protected $fillable = [
        'design_name',
        'design_desc',
        'category_id',
        'photo1',
        'photo2',
        'dubby_qty',
        'zumka_qty',
        'uf',
        'note',
        'create_date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RuhiDesignCategory::class, 'category_id', 'id');
    }

    public function designProducts(): HasMany
    {
        return $this->hasMany(RuhiDesignProduct::class, 'design_id', 'id');
    }
}

