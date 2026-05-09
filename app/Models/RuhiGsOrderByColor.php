<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class RuhiGsOrderByColor extends Model implements AuditableContract
{
    use AuditableTrait;

    protected $table = 'r_gs_order_by_color';

    public $timestamps = false;

    protected $fillable = [
        'gs_id',
        'lot_id',
        'design_id',
        'design_qty',
        'design_red_qty',
        'design_red_green_qty',
        'design_green_qty',
        'white_qty',
    ];

    /**
     * @var array<int, string>
     */
    protected array $auditEvents = [
        'deleted',
    ];

    protected $auditInclude = [
        'gs_id',
        'lot_id',
        'design_id',
        'design_qty',
        'design_red_qty',
        'design_red_green_qty',
        'design_green_qty',
        'white_qty',
    ];

    public function gs(): BelongsTo
    {
        return $this->belongsTo(RuhiGs::class, 'gs_id', 'id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(RuhiSlot::class, 'lot_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(RuhiProduct::class, 'design_id', 'id');
    }

    public function design(): BelongsTo
    {
        return $this->belongsTo(RuhiDesign::class, 'design_id', 'id');
    }
}
