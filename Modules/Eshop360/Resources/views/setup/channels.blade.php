@extends('eshop360::setup.layout', ['step' => 2])

@section('content')
    @php $slug = $instance->slug ?? ''; @endphp

    <h5 class="fw-bold mb-3"><i class="ti ti-truck-delivery me-2"></i>Canaux de distribution</h5>
    <p class="text-muted mb-4">Ajoutez vos canaux de distribution (optionnel). Vous pourrez en ajouter d'autres plus tard.</p>

    <form method="POST" action="{{ route('eshop360.setup.channels.store', $slug) }}" x-data="channelManager()">
        @csrf

        <div id="channels-list">
            <template x-for="(channel, index) in channels" :key="index">
                <div class="border rounded-3 p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2"
                            @click="removeChannel(index)">
                        <i class="ti ti-trash"></i>
                    </button>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" :name="'channels['+index+'][name]'" class="form-control"
                                   x-model="channel.name" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Code</label>
                            <input type="text" :name="'channels['+index+'][code]'" class="form-control"
                                   x-model="channel.code" maxlength="20" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Couleur</label>
                            <input type="color" :name="'channels['+index+'][theme_color]'"
                                   class="form-control form-control-color w-100" x-model="channel.theme_color">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Taux de marge (%)</label>
                            <input type="number" :name="'channels['+index+'][margin_rate]'" class="form-control"
                                   x-model="channel.margin_rate" step="0.01" min="0" max="1" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold small">Modules activés</label>
                        <div class="row g-1">
                            @foreach($featureDefaults as $feature => $default)
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input type="hidden" :name="'channels['+index+'][features][{{ $feature }}]'" value="0">
                                        <input class="form-check-input" type="checkbox"
                                               :name="'channels['+index+'][features][{{ $feature }}]'" value="1"
                                               :id="'ch-'+index+'-{{ $feature }}'"
                                               {{ $default ? 'checked' : '' }}>
                                        <label class="form-check-label" :for="'ch-'+index+'-{{ $feature }}'">{{ ucfirst(str_replace('_', ' ', $feature)) }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <button type="button" class="btn btn-outline-primary mb-4" @click="addChannel()">
            <i class="ti ti-plus me-1"></i> Ajouter un canal
        </button>

        <div class="wizard-footer">
            <a href="{{ route('eshop360.setup.hub', $slug) }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-outline-secondary">
                    Passer cette étape <i class="ti ti-arrow-right ms-1"></i>
                </button>
                <button type="submit" class="btn btn-primary" x-show="channels.length > 0">
                    Suivant <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
<script>
    function channelManager() {
        return {
            channels: @json($channels ?: []),
            addChannel() {
                this.channels.push({
                    name: '', code: '', theme_color: '#2c3e50', margin_rate: 0.13, features: {}
                });
            },
            removeChannel(index) {
                this.channels.splice(index, 1);
            }
        };
    }
</script>
@endpush
