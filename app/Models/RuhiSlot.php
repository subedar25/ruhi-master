<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class RuhiSlot extends Model implements AuditableContract
{
    use AuditableTrait;

    protected $table = 'r_slot';

    public $timestamps = false;

    protected $fillable = [
        'gs_id',
        'slot_name',
    ];

    /**
     * @var array<int, string>
     */
    protected array $auditEvents = [
        'deleted',
    ];

    protected $auditInclude = [
        'gs_id',
        'slot_name',
    ];

    public function gs(): BelongsTo
    {
        return $this->belongsTo(RuhiGs::class, 'gs_id', 'id');
    }

    public function lotItems(): HasMany
    {
        return $this->hasMany(RuhiGsOrderByColor::class, 'lot_id', 'id');
    }
}
