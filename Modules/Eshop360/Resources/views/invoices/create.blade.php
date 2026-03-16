<x-dashboard::layouts.master
    :title="'Nouvelle Facture — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Nouvelle Facture">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Nouvelle Facture</h4>
            <h6>Creer une nouvelle facture</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.invoices.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Retour</a>
    </div>
</div>

<form action="{{ route('eshop360.invoices.store', $instance->slug ?? '') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h5>Informations</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select name="customer_id" class="form-select">
                            <option value="">-- Selectionner --</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ ($order->customer_id ?? old('customer_id')) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('customer_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    @if($order)
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <div class="mb-3">
                        <label class="form-label">Commande liee</label>
                        <input type="text" class="form-control" value="{{ $order->order_number }}" disabled>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Date d'echeance</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays($settings['default_due_days'] ?? 30)->toDateString()) }}">
                        @error('due_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Template</label>
                        <select name="template" class="form-select">
                            <option value="default">Default</option>
                            <option value="modern">Modern</option>
                            <option value="classic">Classic</option>
                            <option value="minimal">Minimal</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remise globale</label>
                        <input type="number" name="discount_amount" class="form-control" value="{{ old('discount_amount', 0) }}" min="0" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Conditions</label>
                        <textarea name="terms" class="form-control" rows="3">{{ old('terms', $settings['default_terms'] ?? '') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pied de page</label>
                        <textarea name="footer_text" class="form-control" rows="2">{{ old('footer_text', $settings['default_footer'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Articles</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addItem()"><i class="ti ti-plus me-1"></i>Ajouter</button>
                </div>
                <div class="card-body">
                    <table class="table" id="items-table">
                        <thead>
                            <tr><th>Description</th><th>Produit</th><th>Qte</th><th>Prix unit.</th><th>Remise</th><th>Taxe</th><th></th></tr>
                        </thead>
                        <tbody>
                            @if($order)
                                @foreach($order->items as $idx => $item)
                                <tr>
                                    <td><input type="text" name="items[{{ $idx }}][description]" class="form-control form-control-sm" value="{{ $item->product_name ?? $item->product->name ?? '' }}" required></td>
                                    <td>
                                        <select name="items[{{ $idx }}][product_id]" class="form-select form-select-sm">
                                            <option value="">--</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ ($item->product_id ?? '') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[{{ $idx }}][quantity]" class="form-control form-control-sm" value="{{ $item->quantity }}" min="1" required></td>
                                    <td><input type="number" name="items[{{ $idx }}][unit_price]" class="form-control form-control-sm" value="{{ $item->unit_price }}" min="0" step="0.01" required></td>
                                    <td><input type="number" name="items[{{ $idx }}][discount]" class="form-control form-control-sm" value="{{ $item->discount ?? 0 }}" min="0" step="0.01"></td>
                                    <td><input type="number" name="items[{{ $idx }}][tax]" class="form-control form-control-sm" value="{{ $item->tax ?? 0 }}" min="0" step="0.01"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="ti ti-trash"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td><input type="text" name="items[0][description]" class="form-control form-control-sm" required></td>
                                    <td>
                                        <select name="items[0][product_id]" class="form-select form-select-sm">
                                            <option value="">--</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm" value="1" min="1" required></td>
                                    <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm" value="0" min="0" step="0.01" required></td>
                                    <td><input type="number" name="items[0][discount]" class="form-control form-control-sm" value="0" min="0" step="0.01"></td>
                                    <td><input type="number" name="items[0][tax]" class="form-control form-control-sm" value="0" min="0" step="0.01"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="ti ti-trash"></i></button></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>Creer la facture</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
let itemIndex = {{ ($order ? $order->items->count() : 1) }};
function addItem() {
    const tbody = document.querySelector('#items-table tbody');
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
    let options = '<option value="">--</option>';
    products.forEach(p => { options += `<option value="${p.id}">${p.name}</option>`; });

    tbody.insertAdjacentHTML('beforeend', `<tr>
        <td><input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm" required></td>
        <td><select name="items[${itemIndex}][product_id]" class="form-select form-select-sm">${options}</select></td>
        <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm" value="1" min="1" required></td>
        <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm" value="0" min="0" step="0.01" required></td>
        <td><input type="number" name="items[${itemIndex}][discount]" class="form-control form-control-sm" value="0" min="0" step="0.01"></td>
        <td><input type="number" name="items[${itemIndex}][tax]" class="form-control form-control-sm" value="0" min="0" step="0.01"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="ti ti-trash"></i></button></td>
    </tr>`);
    itemIndex++;
}
</script>
@endpush

</x-dashboard::layouts.master>
