<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuhiDesignProduct extends Model
{
    protected $table = 'r_design_products';

    public $timestamps = false;

    protected $fillable = [
        'design_id',
        'item_type_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function design(): BelongsTo
    {
        return $this->belongsTo(RuhiDesign::class, 'design_id', 'id');
    }

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(RuhiItemType::class, 'item_type_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(RuhiProduct::class, 'product_id', 'id');
    }

    public function collateByColors(): HasMany
    {
        return $this->hasMany(RuhiCollateByColor::class, 'design_product_id', 'id');
    }
}
