{{-- POS Barcode Scanner --}}
<div class="pos-scan-zone p-3 mb-3">
    <div class="input-group input-group-lg">
        <span class="input-group-text bg-transparent border-0"><i class="ti ti-barcode fs-4 text-primary"></i></span>
        <input type="text"
               id="pos-barcode-input"
               class="form-control border-0 bg-transparent fs-5"
               placeholder="{{ __('Scanner ou saisir code-barres / SKU...') }}"
               autocomplete="off"
               autofocus>
        <button class="btn btn-primary px-4" type="button" id="pos-barcode-btn">
            <i class="ti ti-search fs-5"></i>
        </button>
    </div>
</div>
