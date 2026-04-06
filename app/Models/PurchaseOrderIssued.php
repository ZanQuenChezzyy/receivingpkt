<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderIssued extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_issued';

    protected $fillable = [
        'purchase_order_and_item',
        'mrp_type',
        'material_type',
        'purchase_order_no',
        'item_no',
        'material_code',
        'aac',
        'abc_indicator',
        'description',
        'qty_po',
        'uoi',
        'vendor_id',
        'vendor_name',
        'date_create',
        'delivery_date_po',
        'po_status',
        'incoterm',
        'total_amount_in_lc',
        'requisitioner',
    ];

    protected $casts = [
        'date_create' => 'date',
        'delivery_date_po' => 'date',
        'item_no' => 'integer',
        'qty_po' => 'integer',
        'total_amount_in_lc' => 'integer',
    ];

    public function monitoringNpks(): HasMany
    {
        return $this->hasMany(MonitoringNpk::class);
    }

    public function monitoringChemicals(): HasMany
    {
        return $this->hasMany(MonitoringChemical::class);
    }

    public function chemicalQcTuvs(): HasMany
    {
        return $this->hasMany(ChemicalQcTuv::class);
    }
}
