<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuhiCollateByColor extends Model
{
    protected $table = 'r_collate_by_color';

    public $timestamps = false;

    protected $fillable = [
        'design_product_id',
        'color_id',
        'only_red_qty',
        'red_qty',
        'green_qty',
        'only_green_qty',
        'white_qty',
    ];

    public function designProduct(): BelongsTo
    {
        return $this->belongsTo(RuhiDesignProduct::class, 'design_product_id', 'id');
    }
}
