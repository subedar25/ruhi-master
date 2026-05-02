<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuhiKstoneColor extends Model
{
    protected $table = 'r_kstone_color';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];

    public function kstones(): HasMany
    {
        return $this->hasMany(RuhiKstone::class, 'color_id', 'id');
    }
}
