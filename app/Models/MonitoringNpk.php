<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringNpk extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_terbit_id',
        'delivery_oder_number',
        'location_id',
        'sample_receivied_date',
        'stage',
        'delivery_oder_delivery_date',
        'purchase_order_103_date',
        'received_date',
        'purchase_order_status',
        'purchase_order_status_a_date',
        'purchase_order_status_b_date',
        'purchase_order_status_a_files',
        'laprima_date',
        'coa_date',
        'coa_files',
        'doc_status',
        'created_by',
    ];

    protected $casts = [
        'sample_receivied_date' => 'date',
        'received_date' => 'date',
        'purchase_order_status_a_date' => 'date',
        'purchase_order_status_b_date' => 'date',
        'laprima_date' => 'date',
        'coa_date' => 'date',
        'purchase_order_status_a_files' => 'array', // Support multi-upload Filament
        'coa_files' => 'array',
    ];

    public function purchaseOrderIssued(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderIssued::class, 'purchase_order_terbit_id');
    }

    public function monitoringNpkDetails(): HasMany
    {
        return $this->hasMany(MonitoringNpkDetail::class);
    }
}
