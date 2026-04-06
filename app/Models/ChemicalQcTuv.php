<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChemicalQcTuv extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_issued_id',
        'tahapan_name',
        'qty_qc_tuv',
    ];

    protected $casts = [
        'qty_qc_tuv' => 'integer',
    ];

    public function purchaseOrderIssued(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderIssued::class);
    }
}
