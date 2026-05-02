<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuhiItemType extends Model
{
    use SoftDeletes;

    protected $table = 'r_item_type';

    public $timestamps = false;

    protected $fillable = [
        'item_type',
        'abbreviation',
        'type_by_color',
        'required_kstone',
        'show_order',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(RuhiProduct::class, 'product_type', 'id');
    }

    public function designProducts(): HasMany
    {
        return $this->hasMany(RuhiDesignProduct::class, 'item_type_id', 'id');
    }
}

