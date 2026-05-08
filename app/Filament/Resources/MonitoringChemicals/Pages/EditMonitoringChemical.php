<?php

namespace App\Filament\Resources\MonitoringChemicals\Pages;

use App\Filament\Resources\MonitoringChemicals\MonitoringChemicalResource;
use App\Models\DeliveryOrderReceipt;
use App\Models\DeliveryOrderReceiptDetail;
use App\Models\PurchaseOrderIssued;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditMonitoringChemical extends EditRecord
{
    protected static string $resource = MonitoringChemicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $monitoringChemical = $this->record;
        $poItem = PurchaseOrderIssued::find($monitoringChemical->purchase_order_issued_id);

        if (! $poItem) {
            return; // Abort jika tidak ada data PO
        }

        // 1. Tentukan Source Type
        $matType = $poItem->material_type;
        $sourceType = match ($matType) {
            'ZSP' => 'Sparepart',
            'ZFP', 'ZRM' => 'Bahan Baku NPK',
            'ZSM', 'ZPM' => 'Chemical/Karung',
            default => 'Sparepart',
        };

        // 2. Generate Ulang Document Code
        $poNo = $poItem->purchase_order_no;
        $itemNo = $poItem->item_no;
        $doNo = $monitoringChemical->do_number;
        $date = Carbon::parse($monitoringChemical->received_date)->format('dmY');
        $stage = $monitoringChemical->tahapan;

        $parts = array_filter([$poNo, $itemNo, $doNo, $date, $stage]);
        $joinedString = implode('-', $parts);
        $finalDocumentCode = str_replace(' ', '', strtoupper($joinedString));

        // 3. Update atau Create Header DeliveryOrderReceipt
        $deliveryOrder = DeliveryOrderReceipt::updateOrCreate(
            ['monitoring_chemical_id' => $monitoringChemical->id],
            [
                'delivery_oder_no' => $monitoringChemical->do_number,
                'received_date' => $monitoringChemical->received_date,
                'received_by' => $monitoringChemical->received_by,
                'source_type' => $sourceType,
                'stage' => $stage,
                'document_code' => $finalDocumentCode,
                // Jangan update status jika user mungkin sudah mengubahnya di halaman DO Receipt
            ]
        );

        // 4. Hitung Ulang Unit Price & Total Amount Snapshot
        $qtyPo = (float) $poItem->qty_po;
        $unitPrice = ($qtyPo > 0) ? ((float) $poItem->total_amount_in_lc / $qtyPo) : 0;
        $totalAmount = (float) $monitoringChemical->quantity * $unitPrice;

        // 5. Update atau Create Detail DeliveryOrderReceiptDetail
        DeliveryOrderReceiptDetail::updateOrCreate(
            [
                'delivery_order_receipt_id' => $deliveryOrder->id,
                'purchase_order_issued_id' => $poItem->id, // Mengunci agar baris detailnya tepat
            ],
            [
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
                'is_qty_tolerance' => $monitoringChemical->is_qty_tolerance ?? false,
            ]
        );
    }
}
