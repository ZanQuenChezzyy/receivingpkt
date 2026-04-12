<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringChemical extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_category',
        'purchase_order_issued_id',
        'qc_by',
        'do_number',
        'quantity',
        'tahapan',
        'received_by',
        'received_date',
        'location_id',
        'is_qty_tolerance',
        'has_update_progress',
        'notes',
        'tanggal_pengajuan_simala',
        'tanggal_pengambilan_sample',
        'tanggal_terbit_coa',
        'leadtime_coa',
        'doc_status',
        'created_by',
    ];

    protected $casts = [
        'received_date' => 'date',
        'tanggal_pengajuan_simala' => 'date',
        'tanggal_pengambilan_sample' => 'date',
        'tanggal_terbit_coa' => 'date',
        'is_qty_tolerance' => 'boolean',
        'has_update_progress' => 'boolean',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderIssued::class, 'purchase_order_issued_id');
    }

    public function monitoringChemicalDetails(): HasMany
    {
        return $this->hasMany(MonitoringChemicalDetail::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }
}
