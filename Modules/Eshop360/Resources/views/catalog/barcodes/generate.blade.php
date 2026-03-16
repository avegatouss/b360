<x-dashboard::layouts.master
    :title="'Impression ' . ucfirst($type) . 's — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Impression {{ ucfirst($type) }}s">

<div class="page-header d-print-none">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Impression {{ $type === 'barcode' ? 'Codes-barres' : 'QR Codes' }}</h4>
            <h6>{{ $products->count() }} produit(s) - {{ $quantity }} exemplaire(s) chacun</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.barcodes.index', $instance->slug ?? '') }}" class="btn btn-secondary me-2"><i class="ti ti-arrow-left me-1"></i>Retour</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="ti ti-printer me-1"></i>Imprimer</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            @foreach($products as $product)
                @for($i = 0; $i < $quantity; $i++)
                <div class="col-auto mb-3 text-center p-2 border rounded" style="min-width: 180px;">
                    @if($type === 'barcode' && $product->barcode)
                        <div class="mb-1">
                            {!! DNS1D::getBarcodeHTML($product->barcode, 'C128', 1.5, 40) !!}
                        </div>
                        <small class="d-block">{{ $product->barcode }}</small>
                    @elseif($type === 'qrcode' && $product->qrcode)
                        <div class="mb-1">
                            {!! DNS2D::getBarcodeHTML($product->qrcode, 'QRCODE', 3, 3) !!}
                        </div>
                    @else
                        <div class="mb-1">
                            {!! DNS1D::getBarcodeHTML($product->sku, 'C128', 1.5, 40) !!}
                        </div>
                        <small class="d-block">{{ $product->sku }}</small>
                    @endif
                    <small class="d-block fw-bold mt-1">{{ Str::limit($product->name, 25) }}</small>
                    <small class="d-block">{{ number_format($product->price, 2) }}</small>
                </div>
                @endfor
            @endforeach
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .page-header, .sidebar, .header, nav, footer { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        body { padding: 0; margin: 0; }
    }
</style>
@endpush

</x-dashboard::layouts.master>
