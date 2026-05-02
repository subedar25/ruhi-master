<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuhiGs extends Model
{
    use SoftDeletes;

    protected $table = 'r_gs';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'created_date',
    ];

    public function slots(): HasMany
    {
        return $this->hasMany(RuhiSlot::class, 'gs_id', 'id');
    }

    public function lotItems(): HasMany
    {
        return $this->hasMany(RuhiGsOrderByColor::class, 'gs_id', 'id');
    }
}

