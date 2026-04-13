<x-dashboard::layouts.master
    :title="__('Modules') . ' — ' . $channel->name"
    :instance="$instance"
    :pageTitle="__('Modules du canal') . ' : ' . $channel->name">

@php $slug = $instance->slug ?? ''; @endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form method="POST" action="{{ route('eshop360.channel-settings.features.update', $slug) }}">
    @csrf @method('PUT')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h6 class="fw-bold mb-0"><i class="ti ti-toggles me-2"></i>{{ __('Modules actifs pour') }} {{ $channel->name }}</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($featureDefaults as $feature => $default)
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input type="hidden" name="features[{{ $feature }}]" value="0">
                            <input class="form-check-input" type="checkbox" name="features[{{ $feature }}]" value="1"
                                   id="feat-{{ $feature }}" @checked(!empty($features[$feature]))>
                            <label class="form-check-label fw-semibold" for="feat-{{ $feature }}">
                                {{ ucfirst(str_replace('_', ' ', $feature)) }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer') }}</button>
    </div>
</form>

</x-dashboard::layouts.master>
