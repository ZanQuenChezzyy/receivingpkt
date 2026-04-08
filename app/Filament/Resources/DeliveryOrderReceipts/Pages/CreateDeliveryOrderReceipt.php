<?php

namespace App\Filament\Resources\DeliveryOrderReceipts\Pages;

use App\Filament\Resources\DeliveryOrderReceipts\DeliveryOrderReceiptResource;
use App\Models\DeliveryOrderReceiptDetail;
use App\Models\PurchaseOrderIssued;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDeliveryOrderReceipt extends CreateRecord
{
    protected static string $resource = DeliveryOrderReceiptResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan array repeater tersedia
        if (isset($data['deliveryOrderReceiptDetails']) && is_array($data['deliveryOrderReceiptDetails'])) {

            // Lakukan looping untuk setiap baris material di dalam form repeater
            foreach ($data['deliveryOrderReceiptDetails'] as $key => $item) {

                // 1. Ambil input quantity dan pastikan koma ribuan dibersihkan
                $rawQuantity = $item['quantity'] ?? 0;
                $quantity = (float) str_replace(',', '', (string) $rawQuantity);

                // 2. Ambil harga satuan
                $unitPrice = (float) ($item['unit_price'] ?? 0);

                // 3. Ambil ID Referensi
                $poId = $item['purchase_order_issued_id'] ?? null;
                $itemNo = $item['item_no'] ?? null;

                // 4. Kalkulasi ulang dengan Capping Harga (Jika Toleransi)
                if ($poId && $itemNo) {
                    $poItem = PurchaseOrderIssued::find($poId);

                    if ($poItem) {
                        $qtyPo = (float) $poItem->qty_po;

                        // Hitung jumlah yang sudah pernah diterima sebelumnya
                        $netSaved = (float) DeliveryOrderReceiptDetail::where('purchase_order_issued_id', $poId)
                            ->where('item_no', $itemNo)
                            ->sum('quantity');

                        $sisaKuota = $qtyPo - $netSaved;

                        // Cek apakah quantity melebihi sisa kuota (Toleransi)
                        if ($quantity > $sisaKuota) {
                            $qtyBisaDibayar = max(0, $sisaKuota);
                            // Timpa nilai total_amount_snapshot di array data
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

        // Opsional: Pastikan field created_by terisi dengan ID user yang login
        $data['created_by'] = Auth::id();

        // Kembalikan array data yang sudah dikoreksi ke Filament untuk di-save ke Database
        return $data;
    }
}
