<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Feuille de codes-barres') }}</title>
    <style>
        __BLADE_BLOCK_20__

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: __BLADE_BLOCK_1__; color: #333; background: #fff; }
        .page { padding: 15px; }
        .page-title { text-align: center; margin-bottom: 15px; font-size: 14px; font-weight: 700; color: __BLADE_BLOCK_2__; }

        .grid { width: 100%; }
        .grid::after { content: ''; display: table; clear: both; }

        .barcode-cell {
            float: left;
            width: __BLADE_BLOCK_3__;
            margin: 0 __BLADE_BLOCK_4__;
            margin-bottom: 10px;
            border: 1px dashed #ccc;
            padding: 8px;
            text-align: center;
            page-break-inside: avoid;
        }

        .barcode-cell .product-name {
            font-size: __BLADE_BLOCK_5__;
            font-weight: 600;
            margin-bottom: 4px;
            max-height: 2.4em;
            overflow: hidden;
            line-height: 1.2;
        }

        .barcode-cell .barcode-img {
            height: __BLADE_BLOCK_6__;
            margin: 4px auto;
        }

        .barcode-cell .barcode-img img {
            height: 100%;
            max-width: 100%;
        }

        .barcode-cell .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: __BLADE_BLOCK_7__;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .barcode-cell .product-sku {
            font-size: __BLADE_BLOCK_8__;
            color: #888;
            margin-top: 1px;
        }

        .barcode-cell .product-price {
            font-size: __BLADE_BLOCK_9__;
            font-weight: 700;
            color: __BLADE_BLOCK_10__;
            margin-top: 3px;
        }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; margin: 0; }
            .page { padding: 5mm; }
            .barcode-cell { border: 1px dashed #ccc; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="page-title no-print">Codes-barres - {{ count($products) }} produit(s)</div>

    <div class="grid">
        @foreach($products as $product)
            @php $qty = $options['quantity'] ?? 1; @endphp
            @for($q = 0; $q < $qty; $q++)
            <div class="barcode-cell">
                @if($showName)
                <div class="product-name">{{ $product->name }}</div>
                @endif

                <div class="barcode-img">
                    @php
                        $code = $product->barcode ?? $product->sku ?? $product->id;
                        $format = strtolower($options['format'] ?? 'code128');
                        // Use DNS1D from milon/barcode or picqer/php-barcode-generator if available
                        // Fallback: display code as text
                    @endphp
                    @if(class_exists(\Milon\Barcode\DNS1D::class))
                        @php $generator = new \Milon\Barcode\DNS1D(); @endphp
                        {!! $generator->getBarcodeHTML($code, strtoupper(str_replace('code', 'C', $format)), 1.5, (int) str_replace('px', '', $barcodeHeight)) !!}
                    @elseif(class_exists(\Picqer\Barcode\BarcodeGeneratorHTML::class))
                        @php $generator = new \Picqer\Barcode\BarcodeGeneratorHTML(); @endphp
                        {!! $generator->getBarcode($code, $generator::TYPE_CODE_128) !!}
                    @else
                        <div style="border:1px solid #999;padding:4px;font-family:monospace;font-size:11px;">{{ $code }}</div>
                    @endif
                </div>

                <div class="barcode-text">{{ $code }}</div>

                @if($showSku && !empty($product->sku))
                <div class="product-sku">SKU: {{ $product->sku }}</div>
                @endif

                @if($showPrice)
                <div class="product-price">{{ number_format($product->price, 0, ',', ' ') }} {{ $settings['currency'] ?? __('FCFA') }}</div>
                @endif
            </div>
            @endfor
        @endforeach
    </div>
</div>
</body>
</html>
