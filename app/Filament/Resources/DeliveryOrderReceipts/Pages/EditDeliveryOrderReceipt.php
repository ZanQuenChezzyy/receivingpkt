<?php

namespace App\Filament\Resources\DeliveryOrderReceipts\Pages;

use App\Filament\Resources\DeliveryOrderReceipts\DeliveryOrderReceiptResource;
use App\Models\DeliveryOrderReceiptDetail;
use App\Models\PurchaseOrderIssued;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryOrderReceipt extends EditRecord
{
    protected static string $resource = DeliveryOrderReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['deliveryOrderReceiptDetails']) && is_array($data['deliveryOrderReceiptDetails'])) {

            foreach ($data['deliveryOrderReceiptDetails'] as $key => $item) {

                $rawQuantity = $item['quantity'] ?? 0;
                $quantity = (float) str_replace(',', '', (string) $rawQuantity);

                $unitPrice = (float) ($item['unit_price'] ?? 0);

                $poId = $item['purchase_order_issued_id'] ?? null;
                $itemNo = $item['item_no'] ?? null;

                // 🌟 PENTING DI EDIT: Ambil ID baris detail ini (jika ada) agar tidak terhitung ganda di database
                $excludeId = $item['id'] ?? (is_numeric($key) ? $key : null);

                if ($poId && $itemNo) {
                    $poItem = PurchaseOrderIssued::find($poId);

                    if ($poItem) {
                        $qtyPo = (float) $poItem->qty_po;

                        // Hitung jumlah yang sudah pernah diterima (KECUALI baris yang sedang kita edit ini)
                        $netSavedQuery = DeliveryOrderReceiptDetail::where('purchase_order_issued_id', $poId)
                            ->where('item_no', $itemNo);

                        if ($excludeId) {
                            $netSavedQuery->where('id', '!=', $excludeId);
                        }

                        $netSaved = (float) $netSavedQuery->sum('quantity');

                        $sisaKuota = $qtyPo - $netSaved;

                        if ($quantity > $sisaKuota) {
                            $qtyBisaDibayar = max(0, $sisaKuota);
                            $data['deliveryOrderReceiptDetails'][$key]['total_amount_snapshot'] = $qtyBisaDibayar * $unitPrice;
                        } else {
                            $data['deliveryOrderReceiptDetails'][$key]['total_amount_snapshot'] = $quantity * $unitPrice;
                        }
                    } else {
                        $data['deliveryOrderReceiptDetails'][$key]['total_amount_snapshot'] = $quantity * $unitPrice;
                    }
                } else {
                    $data['deliveryOrderReceiptDetails'][$key]['total_amount_snapshot'] = $quantity * $unitPrice;
                }
            }
        }

        // Catatan: Di halaman edit, kita biasanya tidak mengubah 'created_by'
        // Jadi kita tidak perlu menambahkan $data['created_by'] = auth()->id(); di sini.

        return $data;
    }
}
