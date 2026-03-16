<x-dashboard::layouts.master
    :title="'New Import Order — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="New Import Order">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">New Import Order</h4>
            <h6>Create a new import order</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.imports.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back</a>
    </div>
</div>

<form action="{{ route('eshop360.imports.store', $instance->slug ?? '') }}" method="POST" id="importForm">
    @csrf
    <div class="card">
        <div class="card-header"><h5>Order Information</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers ?? [] as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Shipping Type <span class="text-danger">*</span></label>
                    <select name="shipping_type" class="form-select @error('shipping_type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        <option value="air" {{ old('shipping_type') === 'air' ? 'selected' : '' }}>Air</option>
                        <option value="sea" {{ old('shipping_type') === 'sea' ? 'selected' : '' }}>Sea</option>
                        <option value="land" {{ old('shipping_type') === 'land' ? 'selected' : '' }}>Land</option>
                    </select>
                    @error('shipping_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Expected Arrival Date</label>
                    <input type="date" name="expected_arrival" class="form-control" value="{{ old('expected_arrival') }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Products</h5>
            <button type="button" class="btn btn-sm btn-primary" id="addProductRow"><i class="ti ti-plus me-1"></i>Add Product</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="productsTable">
                    <thead>
                        <tr>
                            <th style="width:35%">Product</th>
                            <th style="width:15%">Quantity</th>
                            <th style="width:15%">Unit Price</th>
                            <th style="width:15%">Weight (kg)</th>
                            <th style="width:15%">Subtotal</th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                    <tbody id="productRows">
                        <tr class="product-row" data-index="0">
                            <td>
                                <select name="items[0][product_id]" class="form-select product-select" required>
                                    <option value="">Select Product</option>
                                    @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->cost_price ?? 0 }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="items[0][quantity]" class="form-control item-qty" min="1" value="1" required></td>
                            <td><input type="number" name="items[0][unit_price]" class="form-control item-price" step="0.01" min="0" value="0" required></td>
                            <td><input type="number" name="items[0][weight]" class="form-control" step="0.01" min="0" value="0"></td>
                            <td><span class="item-subtotal fw-bold">0.00</span></td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="ti ti-trash"></i></button></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total:</td>
                            <td><span id="grandTotal" class="fw-bold">0.00</span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary">Create Import Order</button>
        <button type="submit" name="status" value="draft" class="btn btn-secondary">Save as Draft</button>
        <a href="{{ route('eshop360.imports.index', $instance->slug ?? '') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;

    document.getElementById('addProductRow').addEventListener('click', function() {
        const tbody = document.getElementById('productRows');
        const firstRow = tbody.querySelector('.product-row');
        const newRow = firstRow.cloneNode(true);
        newRow.dataset.index = rowIndex;
        newRow.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else if (el.type === 'number') el.value = el.classList.contains('item-qty') ? 1 : 0;
        });
        newRow.querySelector('.item-subtotal').textContent = '0.00';
        tbody.appendChild(newRow);
        rowIndex++;
        bindRowEvents(newRow);
    });

    function bindRowEvents(row) {
        row.querySelector('.remove-row').addEventListener('click', function() {
            if (document.querySelectorAll('.product-row').length > 1) {
                row.remove();
                calcTotal();
            }
        });
        row.querySelectorAll('.item-qty, .item-price').forEach(el => {
            el.addEventListener('input', function() { calcRowSubtotal(row); calcTotal(); });
        });
        const sel = row.querySelector('.product-select');
        if (sel) {
            sel.addEventListener('change', function() {
                const opt = sel.options[sel.selectedIndex];
                const price = opt.dataset.price || 0;
                row.querySelector('.item-price').value = price;
                calcRowSubtotal(row);
                calcTotal();
            });
        }
    }

    function calcRowSubtotal(row) {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        row.querySelector('.item-subtotal').textContent = (qty * price).toFixed(2);
    }

    function calcTotal() {
        let total = 0;
        document.querySelectorAll('.item-subtotal').forEach(el => { total += parseFloat(el.textContent) || 0; });
        document.getElementById('grandTotal').textContent = total.toFixed(2);
    }

    document.querySelectorAll('.product-row').forEach(row => bindRowEvents(row));
});
</script>
@endpush

</x-dashboard::layouts.master>
