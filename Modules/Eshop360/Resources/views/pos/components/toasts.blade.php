{{-- POS Toast Notifications --}}
<div id="pos-toast" class="pos-toast"></div>

@if(session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div class="toast show bg-success text-white" role="alert">
        <div class="d-flex">
            <div class="toast-body"><i class="ti ti-check me-1"></i>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif
@if(session('error'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div class="toast show bg-danger text-white" role="alert">
        <div class="d-flex">
            <div class="toast-body"><i class="ti ti-x me-1"></i>{{ session('error') }}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif
