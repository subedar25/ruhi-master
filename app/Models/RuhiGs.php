<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class RuhiGs extends Model implements AuditableContract
{
    use AuditableTrait;

    protected $table = 'r_gs';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'created_date',
    ];

    /**
     * @var array<int, string>
     */
    protected array $auditEvents = [
        'deleted',
    ];

    protected $auditInclude = [
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

