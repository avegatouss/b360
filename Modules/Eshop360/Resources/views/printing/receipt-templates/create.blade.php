<x-dashboard::layouts.master
    :title="__('New Receipt Template —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('New Receipt Template')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>{{ __('New Receipt Template') }}</h4>
            <h6>{{ __('Create a new thermal receipt layout') }}</h6>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">{{ __('@foreach($errors->all() as $error)') }}<li>{{ $error }}</li>{{ __('@endforeach') }}</ul>
            </div>
        @endif

        <form action="{{ route('eshop360.receipt-templates.store') }}" method="POST">
            @csrf
            @include('eshop360::printing.receipt-templates._form')

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Create Template') }}</button>
                <a href="{{ route('eshop360.receipt-templates.index') }}" class="btn btn-secondary ms-2">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

</x-dashboard::layouts.master>
