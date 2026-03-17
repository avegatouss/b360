<x-dashboard::layouts.master
    :title="__('Edit Receipt Template —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Edit Receipt Template')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>{{ __('Edit Receipt Template') }}</h4>
            <h6>{{ $template->name }}</h6>
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

        <form action="{{ route('eshop360.receipt-templates.update', $template->id) }}" method="POST">
            @csrf @method('PUT')
            @include('eshop360::printing.receipt-templates._form', ['template' => $template])

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Update Template') }}</button>
                <a href="{{ route('eshop360.receipt-templates.index') }}" class="btn btn-secondary ms-2">{{ __('Cancel') }}</a>
                <a href="{{ route('eshop360.receipt-templates.preview', $template->id) }}" target="_blank" class="btn btn-outline-info ms-2"><i class="ti ti-eye me-1"></i>{{ __('Preview') }}</a>
            </div>
        </form>
    </div>
</div>

</x-dashboard::layouts.master>
