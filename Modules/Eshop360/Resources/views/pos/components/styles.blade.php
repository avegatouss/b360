{{-- POS Shared Styles --}}
<style>
    .pos-wrapper { min-height: calc(100vh - 140px); }
    .pos-product-grid { max-height: calc(100vh - 340px); overflow-y: auto; }
    .pos-sidebar { position: sticky; top: 80px; max-height: calc(100vh - 100px); overflow-y: auto; }
    .pos-product-card { cursor: pointer; transition: all .15s ease; border: 2px solid transparent; }
    .pos-product-card:hover { border-color: var(--bs-primary); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
    .pos-product-card.out-of-stock { opacity: .5; pointer-events: none; }
    .pos-cart-item { border-left: 3px solid var(--bs-primary); }
    .pos-cart-item:hover { background: var(--bs-light); }
    .pos-total-row { font-size: 1.4rem; }
    .pos-badge-stock { font-size: .65rem; font-weight: 600; }
    .pos-scan-zone { border: 2px dashed var(--bs-primary); border-radius: .5rem; background: rgba(var(--bs-primary-rgb), .03); }
    .pos-scan-zone:focus-within { border-color: var(--bs-success); background: rgba(var(--bs-success-rgb), .05); }
    .pos-quick-qty { width: 56px; text-align: center; }
    .pos-numpad .btn { min-width: 60px; min-height: 48px; font-size: 1.1rem; font-weight: 600; }
    #pos-change-display { transition: color .3s ease; }
    .pos-holding-card { border-left: 3px solid var(--bs-warning); }
    #pos-filter-form .form-select { max-width: 160px; }
    .pos-pagination .pagination { margin-bottom: 0; }
    .pos-pagination .page-link { padding: .25rem .5rem; font-size: .75rem; line-height: 1.2; }
    .pos-pagination .page-item .page-link svg,
    .pos-pagination .page-item .page-link img { width: 12px; height: 12px; }
    .pos-customer-bar { background: var(--bs-body-bg); z-index: 5; }
    .pos-line-discount { width: 52px; text-align: center; padding: .1rem .25rem; font-size: .7rem; }
    .pos-toast {
        position: fixed; bottom: 20px; right: 20px; z-index: 1090;
        padding: .5rem 1rem; border-radius: .375rem;
        color: #fff; font-size: .875rem; font-weight: 500;
        opacity: 0; transition: opacity .3s ease;
        pointer-events: none;
    }
    .pos-toast.show { opacity: 1; pointer-events: auto; }
    .pos-toast.success { background: #198754; }
    .pos-toast.error { background: #dc3545; }
    .pos-product-card.adding { opacity: .6; pointer-events: none; }
    @media (max-width: 1199px) {
        .pos-product-grid { max-height: none; }
        .pos-sidebar { position: static; max-height: none; }
    }
</style>
