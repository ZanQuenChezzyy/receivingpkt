<?php

namespace App\Filament\Resources\MonitoringChemicals\Pages;

use App\Filament\Resources\MonitoringChemicals\MonitoringChemicalResource;
use App\Models\DeliveryOrderReceipt;
use App\Models\DeliveryOrderReceiptDetail;
use App\Models\PurchaseOrderIssued;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CreateMonitoringChemical extends CreateRecord
{
    protected static string $resource = MonitoringChemicalResource::class;

    protected function afterCreate(): void
    {
        $monitoringChemical = $this->record;
        $poItem = PurchaseOrderIssued::find($monitoringChemical->purchase_order_issued_id);

        if (! $poItem) {
            return; // Abort jika tidak ada data PO
        }

        // 1. Tentukan Source Type (Logika dari form DeliveryOrderReceipt)
        $matType = $poItem->material_type;
        $sourceType = match ($matType) {
            'ZSP' => 'Sparepart',
            'ZFP', 'ZRM' => 'Bahan Baku NPK',
            'ZSM', 'ZPM' => 'Chemical/Karung',
            default => 'Sparepart',
        };

        // 2. Generate Document Code (Logika dari updateDocumentCode)
        $poNo = $poItem->purchase_order_no;
        $itemNo = $poItem->item_no;
        $doNo = $monitoringChemical->do_number;
        $date = Carbon::parse($monitoringChemical->received_date)->format('dmY');
        $stage = $monitoringChemical->tahapan; // Ambil Tahapan dari Monitoring

        $parts = array_filter([$poNo, $itemNo, $doNo, $date, $stage]);
        $joinedString = implode('-', $parts);
        $finalDocumentCode = str_replace(' ', '', strtoupper($joinedString));

        // 3. Buat Header DeliveryOrderReceipt
        $deliveryOrder = DeliveryOrderReceipt::create([
            'monitoring_chemical_id' => $monitoringChemical->id,
            'delivery_oder_no' => $monitoringChemical->do_number,
            'received_date' => $monitoringChemical->received_date,
            'received_by' => $monitoringChemical->received_by,
            'created_by' => Auth::id(),
            'source_type' => $sourceType,
            'stage' => $stage,
            'document_code' => $finalDocumentCode,
            'status' => 'Diterima', // Default sesuai form Anda
        ]);

        // 4. Hitung Unit Price & Total Amount Snapshot
        $qtyPo = (float) $poItem->qty_po;
        $unitPrice = ($qtyPo > 0) ? ((float) $poItem->total_amount_in_lc / $qtyPo) : 0;
        $totalAmount = (float) $monitoringChemical->quantity * $unitPrice;

        // 5. Buat Detail DeliveryOrderReceiptDetail
        DeliveryOrderReceiptDetail::create([
            'delivery_order_receipt_id' => $deliveryOrder->id,
            'purchase_order_issued_id' => $poItem->id,
            'item_no' => $poItem->item_no,
            'quantity' => $monitoringChemical->quantity,
            'material_code' => $poItem->material_code,
            'description' => $poItem->description,
            'uoi' => $poItem->uoi,
            'mrp_type' => $poItem->mrp_type,
            'material_type' => $poItem->material_type,
            'aac' => $poItem->aac,
            'abc_indicator' => $poItem->abc_indicator,
            'requisitioner' => $poItem->requisitioner,
            'total_amount_snapshot' => $totalAmount,
            'location_id' => $monitoringChemical->location_id,
            'is_different_location' => false,
            'is_qty_tolerance' => $monitoringChemical->is_qty_tolerance ?? false,
        ]);
    }
}
