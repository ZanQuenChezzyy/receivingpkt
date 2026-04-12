<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringChemicalDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'monitoring_chemical_id',
        'chemical_qc_tuv_id',
        'quantity_received',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
    ];

    public function monitoringChemical(): BelongsTo
    {
        return $this->belongsTo(MonitoringChemical::class, 'monitoring_chemical_id', 'id');
    }

    public function chemicalQcTuv(): BelongsTo
    {
        return $this->belongsTo(ChemicalQcTuv::class, 'chemical_qc_tuv_id', 'id');
    }
}
