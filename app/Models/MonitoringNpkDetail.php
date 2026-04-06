<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringNpkDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'monitoring_npk_id',
        'item_no',
        'material_code',
        'description',
        'quantity',
        'string',
        'location_id',
        'is_qty_tolerance',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_qty_tolerance' => 'boolean',
    ];

    public function MonitoringNpk(): BelongsTo
    {
        return $this->belongsTo(MonitoringNpk::class, 'monitoring_npk_id');
    }
}
