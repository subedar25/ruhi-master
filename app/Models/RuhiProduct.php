<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuhiProduct extends Model
{
    use SoftDeletes;

    protected $table = 'r_product';

    public $timestamps = false;

    protected $fillable = [
        'product_name',
        'product_desc',
        'photo1',
        'photo2',
        'product_type',
        'weight',
        'create_date',
    ];

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(RuhiItemType::class, 'product_type', 'id');
    }

    public function itemKstones(): HasMany
    {
        return $this->hasMany(RuhiItemKstone::class, 'item_id', 'id');
    }

    public function designProducts(): HasMany
    {
        return $this->hasMany(RuhiDesignProduct::class, 'product_id', 'id');
    }

    public function designs(): BelongsToMany
    {
        return $this->belongsToMany(RuhiDesign::class, 'r_design_products', 'product_id', 'design_id')
            ->distinct();
    }
}

