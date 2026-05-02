<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RuhiDesignCategory extends Model
{
    use SoftDeletes;

    protected $table = 'r_design_category';

    public $timestamps = false;

    protected $fillable = [
        'category_name',
        'abbreviation',
        'created_date',
    ];

    protected $casts = [
        'created_date' => 'datetime',
    ];

    public function designs(): HasMany
    {
        return $this->hasMany(RuhiDesign::class, 'category_id', 'id');
    }
}

