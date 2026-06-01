@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Parametres imprimante') }}</h4>
    <p class="text-muted mb-0">{{ __('Configuration de l\'impression pour ce canal') }}</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('eshop360.channel-portal.settings.printer.update', [$slug, $channelKey]) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Type d\'imprimante') }}</label>
                    <select name="printer_type" class="form-select cp-select2">
                        @php $printerType = $settings['printer_type'] ?? 'thermal'; @endphp
                        <option value="thermal" {{ $printerType === 'thermal' ? 'selected' : '' }}>{{ __('Thermique') }}</option>
                        <option value="inkjet" {{ $printerType === 'inkjet' ? 'selected' : '' }}>{{ __('Jet d\'encre') }}</option>
                        <option value="laser" {{ $printerType === 'laser' ? 'selected' : '' }}>{{ __('Laser') }}</option>
                        <option value="browser" {{ $printerType === 'browser' ? 'selected' : '' }}>{{ __('Navigateur') }}</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Largeur du recu (mm)') }}</label>
                    <input type="number" name="receipt_width" class="form-control" min="40" max="120"
                           value="{{ old('receipt_width', $settings['receipt_width'] ?? 80) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('En-tete du recu') }}</label>
                    <textarea name="receipt_header" class="form-control" rows="3" placeholder="{{ __('Texte affiche en haut du recu') }}">{{ old('receipt_header', $settings['receipt_header'] ?? '') }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Pied de page du recu') }}</label>
                    <textarea name="receipt_footer" class="form-control" rows="3" placeholder="{{ __('Texte affiche en bas du recu') }}">{{ old('receipt_footer', $settings['receipt_footer'] ?? '') }}</textarea>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="auto_print_receipt" id="auto_print_receipt" value="1"
                               {{ ($settings['auto_print_receipt'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="auto_print_receipt">{{ __('Impression automatique du recu') }}</label>
                    </div>
                </div>

                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold">{{ __('Etiquettes code-barres') }}</h6>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Largeur etiquette (mm)') }}</label>
                    <input type="number" name="barcode_label_width" class="form-control" min="20" max="200"
                           value="{{ old('barcode_label_width', $settings['barcode_label_width'] ?? 50) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Hauteur etiquette (mm)') }}</label>
                    <input type="number" name="barcode_label_height" class="form-control" min="10" max="100"
                           value="{{ old('barcode_label_height', $settings['barcode_label_height'] ?? 25) }}">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> {{ __('Enregistrer') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
