<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrderReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'monitoring_npk_id',
        'monitoring_chemical_id',
        'delivery_oder_no',
        'received_date',
        'received_by',
        'created_by',
        'source_type',
        'stage',
        'document_code',
        'status',
        'post_103',
    ];

    protected $casts = [
        'received_date' => 'date',
        'post_103' => 'date',
    ];

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function monitoringNpk(): BelongsTo
    {
        return $this->belongsTo(MonitoringNpk::class);
    }

    public function monitoringChemical(): BelongsTo
    {
        return $this->belongsTo(MonitoringChemical::class);
    }

    public function deliveryOrderReceiptDetails(): HasMany
    {
        return $this->hasMany(DeliveryOrderReceiptDetail::class);
    }
}
