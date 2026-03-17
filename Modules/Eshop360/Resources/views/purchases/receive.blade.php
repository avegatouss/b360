<x-dashboard::layouts.master
    :title="__('Réception') . ' —' . $purchase->reference . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Réception marchandise')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Réception — {{ $purchase->reference }}</h4>
            <h6>{{ $purchase->supplier?->name ?? $purchase->supplier_name ?? '—' }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.purchases.show', [$instance->slug ?? '', $purchase]) }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<form method="POST" action="{{ route('eshop360.purchases.receive', [$instance->slug ?? '', $purchase]) }}">
    @csrf

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label fw-semibold">{{ __('Entrepôt de destination') }}<span class="text-danger">*</span></label>
            <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                <option value="">{{ __('— Sélectionner un entrepôt —') }}</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}"
                        {{ old('warehouse_id', $purchase->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
            @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Lignes de commande') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Produit') }}</th>
                            <th class="text-center" style="width:120px">{{ __('Commandé') }}</th>
                            <th class="text-center" style="width:120px">{{ __('Déjà reçu') }}</th>
                            <th class="text-center" style="width:120px">{{ __('Restant') }}</th>
                            <th class="text-center" style="width:150px">{{ __('Qté à recevoir') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchase->items as $item)
                            @php
                                $remaining = max(0, $item->quantity - ($item->received_qty ?? 0));
                            @endphp
                            <tr @class(['table-success' => $remaining === 0])>
                                <td>
                                    <div class="fw-semibold">{{ $item->product->name ?? '—' }}</div>
                                    <small class="text-muted">{{ $item->product->sku ?? '' }}</small>
                                    <input type="hidden" name="items[{{ $loop->index }}][purchase_item_id]" value="{{ $item->id }}">
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">{{ $item->received_qty ?? 0 }}</td>
                                <td class="text-center">
                                    @if ($remaining === 0)
                                        <span class="badge bg-success">{{ __('Complet') }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ $remaining }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($remaining > 0)
                                        <input type="number"
                                               name="items[{{ $loop->index }}][received_qty]"
                                               class="form-control form-control-sm text-center"
                                               min="0"
                                               max="{{ $remaining }}"
                                               value="{{ $remaining }}"
                                               placeholder="0">
                                    @else
                                        <input type="hidden" name="items[{{ $loop->index }}][received_qty]" value="0">
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('eshop360.purchases.show', [$instance->slug ?? '', $purchase]) }}"
           class="btn btn-light">{{ __('Annuler') }}</a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-check me-1"></i>Confirmer la réception
        </button>
    </div>
</form>

</x-dashboard::layouts.master>
