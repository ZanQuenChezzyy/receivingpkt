<?php

namespace App\Filament\Imports;

use App\Models\PurchaseOrderIssued;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class PurchaseOrderIssuedImporter extends Importer
{
    protected static ?string $model = PurchaseOrderIssued::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('purchase_order_and_item')
                ->rules(['max:20']),
            ImportColumn::make('material_type')
                ->requiredMapping()
                ->rules(['required', 'max:5']),
            ImportColumn::make('mrp_type')
                ->requiredMapping()
                ->rules(['required', 'max:10']),
            ImportColumn::make('purchase_order_no')
                ->requiredMapping()
                ->rules(['required', 'max:12']),
            ImportColumn::make('item_no')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('material_code')
                ->rules(['max:20']),
            ImportColumn::make('aac')
                ->rules(['max:1']),
            ImportColumn::make('abc_indicator')
                ->rules(['max:1']),
            ImportColumn::make('description')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('qty_po')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('uoi')
                ->requiredMapping()
                ->rules(['required', 'max:5']),
            ImportColumn::make('vendor_id')
                ->rules(['max:20']),
            ImportColumn::make('vendor_name')
                ->requiredMapping()
                ->rules(['required', 'max:100']),
            ImportColumn::make('date_create')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('delivery_date_po')
                ->rules(['date']),
            ImportColumn::make('po_status')
                ->rules(['max:2']),
            ImportColumn::make('incoterm')
                ->rules(['max:100']),
            ImportColumn::make('total_amount_in_lc')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('requisitioner')
                ->requiredMapping()
                ->rules(['required', 'max:100']),
        ];
    }

    public function resolveRecord(): ?PurchaseOrderIssued
    {
        // 1. Parsing Tanggal dari format Excel (d/m/Y) ke format DB (Y-m-d)
        $dateCreate = $this->parseDate($this->data['date_create'] ?? null);
        $deliveryDate = $this->parseDate($this->data['delivery_date_po'] ?? null);

        // 2. Logika "Create if not exist, otherwise Update"
        // Kita gunakan 'purchase_order_and_item' sebagai unique key sesuai migration Anda
        $record = PurchaseOrderIssued::firstOrNew([
            'purchase_order_and_item' => $this->data['purchase_order_and_item'],
        ]);

        // 3. Fill data terbaru
        $record->fill([
            'mrp_type' => $this->data['mrp_type'],
            'aac' => $this->data['aac'] ?? null,
            'abc_indicator' => $this->data['abc_indicator'] ?? null,
            'purchase_order_no' => $this->data['purchase_order_no'],
            'item_no' => $this->data['item_no'],
            'material_code' => $this->data['material_code'] ?? null,
            'description' => $this->data['description'],
            'qty_po' => $this->data['qty_po'],
            'uoi' => $this->data['uoi'],
            'vendor_id' => $this->data['vendor_id'] ?? null,
            'vendor_name' => $this->data['vendor_name'],
            'date_create' => $dateCreate,
            'delivery_date_po' => $deliveryDate,
            'po_status' => $this->data['po_status'] ?? null,
            'incoterm' => $this->data['incoterm'] ?? null,
            'total_amount_in_lc' => $this->data['total_amount_in_lc'],
            'requisitioner' => $this->data['incoterm'] ?? null,
        ]);

        // 4. Skip jika data yang di-import sama persis dengan yang di database
        // Ini sangat krusial untuk performa data skala besar
        if ($record->exists && ! $record->isDirty()) {
            return null;
        }

        return $record;
    }

    protected function parseDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            // Mencoba format d/m/Y sesuai import lama Anda
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            // Fallback jika formatnya sudah Y-m-d atau lainnya
            try {
                return Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import Purchase Order Terbit selesai. '.Number::format($import->successful_rows).' data berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' data gagal.';
        }

        return $body;
    }

    public function getJobMiddleware(): array
    {
        return [];
    }
}
