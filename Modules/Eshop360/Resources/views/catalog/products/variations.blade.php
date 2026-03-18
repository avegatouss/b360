<x-dashboard::layouts.master
    :title="__('Variantes') . ' — ' . $product->name"
    :instance="$instance"
    :pageTitle="__('Variantes produit')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $product->name }}</h4>
            <h6>{{ __('Gestion des variantes') }} ({{ $product->sku }})</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.products.show', [$instance->slug, $product]) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVariationModal">
            <i class="ti ti-plus me-1"></i>{{ __('Ajouter variante') }}
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Nom') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Code-barres') }}</th>
                        <th class="text-end">{{ __('Prix') }}</th>
                        <th class="text-end">{{ __('Cout') }}</th>
                        <th class="text-center">{{ __('Quantite') }}</th>
                        <th>{{ __('Attributs') }}</th>
                        <th class="text-center">{{ __('Actif') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($product->variations as $variation)
                        <tr>
                            <td class="fw-medium">
                                @if($variation->image)
                                    <img src="{{ asset('storage/' . $variation->image) }}" alt="" class="rounded me-2" style="width:28px;height:28px;object-fit:cover;">
                                @endif
                                {{ $variation->name }}
                            </td>
                            <td class="small text-muted">{{ $variation->sku ?: '—' }}</td>
                            <td class="small text-muted">{{ $variation->barcode ?: '—' }}</td>
                            <td class="text-end">{{ $variation->price !== null ? number_format($variation->price, 0, ',', ' ') : __('(produit)') }}</td>
                            <td class="text-end text-muted">{{ $variation->cost_price !== null ? number_format($variation->cost_price, 0, ',', ' ') : '—' }}</td>
                            <td class="text-center">{{ $variation->quantity }}</td>
                            <td class="small">
                                @if($variation->values)
                                    @foreach($variation->values as $attr => $val)
                                        <span class="badge bg-light text-dark me-1">{{ $attr }}: {{ $val }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($variation->is_active)
                                    <span class="badge bg-success">{{ __('Oui') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Non') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editVariation{{ $variation->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('eshop360.products.variations.destroy', [$instance->slug, $product, $variation]) }}" onsubmit="return confirm('Supprimer cette variante ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <div class="modal fade" id="editVariation{{ $variation->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('eshop360.products.variations.update', [$instance->slug, $product, $variation]) }}" enctype="multipart/form-data">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ __('Modifier') }}: {{ $variation->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @include('eshop360::catalog.products._variation-form', ['variation' => $variation])
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                                            <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="ti ti-layers-subtract fs-1 d-block mb-2"></i>
                                {{ __('Aucune variante pour ce produit.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addVariationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('eshop360.products.variations.store', [$instance->slug, $product]) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Nouvelle variante') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('eshop360::catalog.products._variation-form', ['variation' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Creer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
