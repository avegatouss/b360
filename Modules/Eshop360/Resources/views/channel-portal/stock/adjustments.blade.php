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
            <h4 class="fw-bold mb-1">{{ __('Ajustement de stock') }}</h4>
            <p class="text-muted mb-0">{{ __('Ajuster le stock des produits de ce canal') }}</p>
        </div>
        <a href="{{ route('eshop360.channel-portal.stock.index', [$slug, $channelKey]) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> {{ __('Retour au stock') }}
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('eshop360.channel-portal.stock.adjustments.store', [$slug, $channelKey]) }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Produit') }} <span class="text-danger">*</span></label>
                    <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                        <option value="">{{ __('Selectionner un produit...') }}</option>
                        @foreach($products ?? [] as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->sku ?? '---' }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="add" {{ old('type') === 'add' ? 'selected' : '' }}>{{ __('Ajout (+)') }}</option>
                        <option value="remove" {{ old('type') === 'remove' ? 'selected' : '' }}>{{ __('Retrait (-)') }}</option>
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ __('Quantite') }} <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                           value="{{ old('quantity', 1) }}" min="1" required>
                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">{{ __('Motif / Notes') }}</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                              placeholder="{{ __('Raison de l\'ajustement...') }}">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-adjustments me-1"></i> {{ __('Appliquer l\'ajustement') }}
                </button>
                <a href="{{ route('eshop360.channel-portal.stock.index', [$slug, $channelKey]) }}" class="btn btn-secondary ms-2">
                    {{ __('Annuler') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
