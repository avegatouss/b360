@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1">{{ __('Modifier le client') }}</h4>
            <p class="text-muted mb-0">{{ $customer->name }}</p>
        </div>
        <a href="{{ route('eshop360.channel-portal.customers.index', [$slug, $channelKey]) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> {{ __('Retour') }}
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('eshop360.channel-portal.customers.update', [$slug, $channelKey, $customer->id]) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $customer->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $customer->email) }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Telephone') }}</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $customer->phone) }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Entreprise') }}</label>
                    <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                           value="{{ old('company_name', $customer->company_name) }}">
                    @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">{{ __('Adresse') }}</label>
                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                           value="{{ old('address', $customer->address) }}">
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Ville') }}</label>
                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                           value="{{ old('city', $customer->city) }}">
                    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Pays') }}</label>
                    <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                           value="{{ old('country', $customer->country) }}">
                    @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Limite de credit') }}</label>
                    <div class="input-group">
                        <input type="number" name="credit_limit" class="form-control @error('credit_limit') is-invalid @enderror"
                               value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}" min="0" step="1">
                        <span class="input-group-text">XAF</span>
                    </div>
                    @error('credit_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $customer->notes) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> {{ __('Mettre a jour') }}
                </button>
                <a href="{{ route('eshop360.channel-portal.customers.index', [$slug, $channelKey]) }}" class="btn btn-secondary ms-2">
                    {{ __('Annuler') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
