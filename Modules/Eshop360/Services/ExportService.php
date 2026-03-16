<?php

namespace Modules\Eshop360\Services;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Export service for reports and business data.
 * CSV is generated natively and XLSX is generated with a minimal Zip/XML workbook.
 */
final class ExportService
{
    public function csv(Collection|array $data, array $headers, string $filename, ?callable $rowMapper = null): StreamedResponse
    {
        $rows = $this->mapRows($data, $rowMapper);

        return new StreamedResponse(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ]);
    }

    public function products(Collection $products, string $format = 'csv'): StreamedResponse
    {
        return $this->download($products, [
            'ID', 'SKU', 'Nom', 'Categorie', 'Marque', 'Prix usine', 'PGHT', 'Prix vente', 'Stock', 'Statut',
        ], 'produits_' . now()->format('Y-m-d'), fn ($product) => [
            $product->id,
            $product->sku,
            $product->name,
            $product->category?->name ?? '',
            $product->brand?->name ?? '',
            (float) ($product->purchase_price_factory ?? $product->cost_price ?? 0),
            (float) ($product->pght ?? 0),
            (float) ($product->price ?? 0),
            (int) ($product->stocks?->sum('quantity') ?? 0),
            $product->is_active ? 'Actif' : 'Inactif',
        ], $format);
    }

    public function sales(Collection $orders, string $format = 'csv'): StreamedResponse
    {
        return $this->download($orders, [
            'Reference', 'Date', 'Client', 'Sous-total', 'Remise', 'TVA', 'Total', 'Statut paiement', 'Canal',
        ], 'ventes_' . now()->format('Y-m-d'), fn ($order) => [
            $order->reference,
            $order->created_at?->format('d/m/Y H:i') ?? '',
            $order->customer?->name ?? 'Comptoir',
            (float) ($order->subtotal ?? 0),
            (float) ($order->discount_amount ?? 0),
            (float) ($order->tax_amount ?? 0),
            (float) ($order->total ?? 0),
            $order->payment_status ?? 'N/A',
            $order->channel?->name ?? 'Direct',
        ], $format);
    }

    public function invoices(Collection $invoices, string $format = 'csv'): StreamedResponse
    {
        return $this->download($invoices, [
            'Reference', 'Date', 'Echeance', 'Client', 'Sous-total', 'TVA', 'Total', 'Statut',
        ], 'factures_' . now()->format('Y-m-d'), fn ($invoice) => [
            $invoice->reference,
            $invoice->created_at?->format('d/m/Y') ?? '',
            $invoice->due_date?->format('d/m/Y') ?? '',
            $invoice->customer?->name ?? '',
            (float) ($invoice->subtotal ?? 0),
            (float) ($invoice->tax_amount ?? 0),
            (float) ($invoice->total ?? 0),
            $invoice->status,
        ], $format);
    }

    public function customers(Collection $customers, string $format = 'csv'): StreamedResponse
    {
        return $this->download($customers, [
            'ID', 'Nom', 'Email', 'Telephone', 'Adresse', 'Solde portefeuille', 'Total achats', 'Cree le',
        ], 'clients_' . now()->format('Y-m-d'), fn ($customer) => [
            $customer->id,
            $customer->name,
            $customer->email ?? '',
            $customer->phone ?? '',
            $customer->address ?? '',
            (float) ($customer->wallet_balance ?? 0),
            (float) ($customer->total_orders_amount ?? 0),
            $customer->created_at?->format('d/m/Y') ?? '',
        ], $format);
    }

    public function stock(Collection $stocks, string $format = 'csv'): StreamedResponse
    {
        return $this->download($stocks, [
            'Produit', 'SKU', 'Entrepot', 'Quantite', 'Reserve', 'Disponible', 'Cout unitaire', 'Valeur stock',
        ], 'stock_' . now()->format('Y-m-d'), function ($stock) {
            $unitCost = (float) ($stock->product?->cost_price_real ?? $stock->product?->cost_price ?? 0);

            return [
                $stock->product?->name ?? '',
                $stock->product?->sku ?? '',
                $stock->warehouse?->name ?? '',
                (int) ($stock->quantity ?? 0),
                (int) ($stock->reserved_quantity ?? 0),
                (int) ($stock->available_quantity ?? 0),
                $unitCost,
                round(((int) ($stock->quantity ?? 0)) * $unitCost, 2),
            ];
        }, $format);
    }

    public function reportData(array $data, array $headers, string $filename, string $format = 'csv'): StreamedResponse
    {
        return $this->download(collect($data), $headers, pathinfo($filename, PATHINFO_FILENAME), null, $format);
    }

    public function suppliers(Collection $suppliers, string $format = 'csv'): StreamedResponse
    {
        return $this->download($suppliers, [
            'ID', 'Nom', 'Email', 'Telephone', 'Adresse', 'Pays', 'Solde du', 'Total achats', 'Cree le',
        ], 'fournisseurs_' . now()->format('Y-m-d'), fn ($supplier) => [
            $supplier->id,
            $supplier->name,
            $supplier->email ?? '',
            $supplier->phone ?? '',
            $supplier->address ?? '',
            $supplier->country ?? '',
            (float) ($supplier->balance ?? 0),
            (float) ($supplier->total_purchases_amount ?? 0),
            $supplier->created_at?->format('d/m/Y') ?? '',
        ], $format);
    }

    public function purchases(Collection $purchases, string $format = 'csv'): StreamedResponse
    {
        return $this->download($purchases, [
            'Reference', 'Date', 'Fournisseur', 'Total', 'Paye', 'Reste du', 'Statut', 'Notes',
        ], 'achats_' . now()->format('Y-m-d'), fn ($purchase) => [
            $purchase->reference ?? "PO-{$purchase->id}",
            $purchase->created_at?->format('d/m/Y') ?? '',
            $purchase->supplier?->name ?? $purchase->supplier_name ?? '',
            (float) ($purchase->total ?? 0),
            (float) ($purchase->paid_amount ?? 0),
            (float) ($purchase->due_amount ?? 0),
            $purchase->status ?? '',
            $purchase->notes ?? '',
        ], $format);
    }

    public function expenses(Collection $expenses, string $format = 'csv'): StreamedResponse
    {
        return $this->download($expenses, [
            'Date', 'Categorie', 'Description', 'Montant', 'Compte',
        ], 'depenses_' . now()->format('Y-m-d'), fn ($expense) => [
            $expense->date?->format('d/m/Y') ?? $expense->created_at?->format('d/m/Y') ?? '',
            $expense->category?->name ?? '',
            $expense->description ?? '',
            (float) ($expense->amount ?? 0),
            $expense->account?->name ?? '',
        ], $format);
    }

    private function download(Collection|array $data, array $headers, string $basename, ?callable $rowMapper, string $format): StreamedResponse
    {
        $format = strtolower($format);

        if ($format === 'xlsx' && class_exists(ZipArchive::class)) {
            return $this->xlsx($data, $headers, $basename . '.xlsx', $rowMapper);
        }

        return $this->csv($data, $headers, $basename . '.csv', $rowMapper);
    }

    private function xlsx(Collection|array $data, array $headers, string $filename, ?callable $rowMapper = null): StreamedResponse
    {
        $rows = $this->mapRows($data, $rowMapper);
        $tempFile = tempnam(sys_get_temp_dir(), 'eshop-xlsx-');
        $zip = new ZipArchive();

        $zip->open($tempFile, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($headers, $rows));
        $zip->close();

        return new StreamedResponse(function () use ($tempFile) {
            readfile($tempFile);
            @unlink($tempFile);
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ]);
    }

    private function mapRows(Collection|array $data, ?callable $rowMapper): array
    {
        $collection = $data instanceof Collection ? $data : collect($data);

        return $collection
            ->map(function ($item) use ($rowMapper): array {
                $row = $rowMapper
                    ? $rowMapper($item)
                    : (is_array($item) ? array_values($item) : array_values($item->toArray()));

                return array_map(fn ($value) => $this->normalizeValue($value), $row);
            })
            ->all();
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value ?? '';
    }

    private function worksheetXml(array $headers, array $rows): string
    {
        $xmlRows = [];
        $rowIndex = 1;

        $xmlRows[] = $this->worksheetRowXml($rowIndex++, $headers);

        foreach ($rows as $row) {
            $xmlRows[] = $this->worksheetRowXml($rowIndex++, $row);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . implode('', $xmlRows) . '</sheetData>'
            . '</worksheet>';
    }

    private function worksheetRowXml(int $rowIndex, array $values): string
    {
        $cells = [];

        foreach (array_values($values) as $columnIndex => $value) {
            $cellRef = $this->columnName($columnIndex) . $rowIndex;

            if (is_int($value) || is_float($value)) {
                $cells[] = '<c r="' . $cellRef . '"><v>' . $value . '</v></c>';
                continue;
            }

            $escaped = htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $cells[] = '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
        }

        return '<row r="' . $rowIndex . '">' . implode('', $cells) . '</row>';
    }

    private function columnName(int $index): string
    {
        $index++;
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Export" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';
    }
}
