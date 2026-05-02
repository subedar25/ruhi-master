<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuhiDesignProductItemKstone extends Model
{
    protected $table = 'r_design_product_item_kstone';

    public $timestamps = false;

    protected $fillable = [
        'design_id',
        'product_id',
        'kstone_id',
        'kstone_quantity',
        'red',
        'rg_red',
        'rg_green',
        'green',
        'white',
        'rodo',
    ];

    protected $casts = [
        'kstone_quantity' => 'integer',
        'red' => 'integer',
        'rg_red' => 'integer',
        'rg_green' => 'integer',
        'green' => 'integer',
        'white' => 'integer',
        'rodo' => 'integer',
    ];

    public function design(): BelongsTo
    {
        return $this->belongsTo(RuhiDesign::class, 'design_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(RuhiProduct::class, 'product_id', 'id');
    }

    public function kstone(): BelongsTo
    {
        return $this->belongsTo(RuhiKstone::class, 'kstone_id', 'id');
    }
}
