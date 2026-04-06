<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderReceiptDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_receipt_id',
        'purchase_order_issued_id',
        'item_no',
        'quantity',
        'material_code',
        'description',
        'uoi',
        'mrp_type',
        'material_type',
        'aac',
        'abc_indicator',
        'requisitioner',
        'total_amount_snapshot',
        'location_id',
        'is_different_location',
        'is_qty_tolerance',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_amount_snapshot' => 'integer',
        'is_different_location' => 'boolean',
        'is_qty_tolerance' => 'boolean',
    ];

    public function purchaseOrderIssued(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderIssued::class, 'purchase_order_issued_id');
    }

    public function deliveryOrderReceipt(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrderReceipt::class, 'delivery_order_receipt_id');
    }

    public function locationReceiving(): BelongsTo
    {
        return $this->belongsTo(LocationReceiving::class, 'location_id');
    }
}
