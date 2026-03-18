<x-dashboard::layouts.master :title="__('Campagne de messages') . ' - ' . ($instance->name ?? '')" :instance="$instance">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Nouvelle campagne') }}</h4>
            <h6>{{ __('Envoi groupé d\'emails ou SMS aux clients') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.bulk-messages.history', $instance->slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-history me-1"></i>{{ __('Historique') }}
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('eshop360.bulk-messages.send', $instance->slug) }}" id="bulkForm">
                    @csrf

                    {{-- Type --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Type d\'envoi') }} <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" value="email" id="typeEmail"
                                       {{ old('type', 'email') === 'email' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeEmail"><i class="ti ti-mail me-1"></i>{{ __('Email') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" value="sms" id="typeSms"
                                       {{ old('type') === 'sms' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeSms"><i class="ti ti-message me-1"></i>{{ __('SMS') }}</label>
                            </div>
                        </div>
                        @error('type') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    {{-- Subject (email only) --}}
                    <div class="mb-3" id="subjectGroup">
                        <label class="form-label">{{ __('Objet') }} <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                               value="{{ old('subject') }}" placeholder="{{ __('Objet de l\'email') }}">
                        @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Template (email only) --}}
                    <div class="mb-3" id="templateGroup">
                        <label class="form-label">{{ __('Template email') }}</label>
                        <select name="template_id" class="form-select">
                            <option value="">{{ __('-- Aucun (texte libre) --') }}</option>
                            @foreach($emailTemplates as $tpl)
                                <option value="{{ $tpl->id }}" {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                                    {{ $tpl->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Message --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Message') }} <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                                  rows="6" placeholder="{{ __('Contenu du message...') }}">{{ old('message') }}</textarea>
                        @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">{{ __('Filtres destinataires') }}</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Groupe client') }}</label>
                            <select name="customer_group_id" class="form-select">
                                <option value="">{{ __('-- Tous --') }}</option>
                                @foreach($customerGroups as $group)
                                    <option value="{{ $group->id }}" {{ old('customer_group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Magasin') }}</label>
                            <select name="store_id" class="form-select">
                                <option value="">{{ __('-- Tous --') }}</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Canal de distribution') }}</label>
                            <select name="channel_id" class="form-select">
                                <option value="">{{ __('-- Tous --') }}</option>
                                @foreach($channels as $ch)
                                    <option value="{{ $ch->id }}" {{ old('channel_id') == $ch->id ? 'selected' : '' }}>
                                        {{ $ch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Statut client') }}</label>
                            <select name="status" class="form-select">
                                <option value="">{{ __('-- Tous --') }}</option>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Inscrits depuis') }}</label>
                            <input type="date" name="date_from" class="form-control" value="{{ old('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Inscrits jusqu\'au') }}</label>
                            <input type="date" name="date_to" class="form-control" value="{{ old('date_to') }}">
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mt-4">
                        <button type="button" class="btn btn-outline-primary" id="previewBtn">
                            <i class="ti ti-eye me-1"></i>{{ __('Aperçu destinataires') }}
                        </button>
                        <span id="recipientCount" class="text-muted small"></span>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-send me-1"></i>{{ __('Envoyer la campagne') }}
                        </button>
                        <a href="{{ route('eshop360.bulk-messages.history', $instance->slug) }}" class="btn btn-outline-secondary">
                            {{ __('Annuler') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Preview panel --}}
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">{{ __('Aperçu') }}</h6>
            </div>
            <div class="card-body" id="previewPanel">
                <p class="text-muted small">{{ __('Cliquez sur "Aperçu destinataires" pour voir les clients cibles.') }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var typeRadios = document.querySelectorAll('input[name="type"]');
    var subjectGroup = document.getElementById('subjectGroup');
    var templateGroup = document.getElementById('templateGroup');

    function toggleFields() {
        var isEmail = document.querySelector('input[name="type"]:checked')?.value === 'email';
        subjectGroup.style.display = isEmail ? '' : 'none';
        templateGroup.style.display = isEmail ? '' : 'none';
    }

    typeRadios.forEach(function (r) { r.addEventListener('change', toggleFields); });
    toggleFields();

    document.getElementById('previewBtn')?.addEventListener('click', function () {
        var form = document.getElementById('bulkForm');
        var data = new FormData(form);
        var params = new URLSearchParams();
        ['customer_group_id', 'store_id', 'channel_id', 'status', 'date_from', 'date_to'].forEach(function (k) {
            if (data.get(k)) params.set(k, data.get(k));
        });

        var panel = document.getElementById('previewPanel');
        var countSpan = document.getElementById('recipientCount');

        fetch("{{ route('eshop360.bulk-messages.preview', $instance->slug) }}?" + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            countSpan.textContent = json.count + ' destinataire(s)';
            // Build preview safely using DOM methods
            panel.textContent = '';
            var heading = document.createElement('p');
            heading.className = 'fw-medium';
            heading.textContent = json.count + ' destinataire(s)';
            panel.appendChild(heading);

            if (json.sample && json.sample.length) {
                var list = document.createElement('ul');
                list.className = 'list-unstyled small';
                json.sample.forEach(function (c) {
                    var li = document.createElement('li');
                    li.className = 'mb-1';
                    li.textContent = (c.name || '-') + ' (' + (c.email || c.phone || '-') + ')';
                    list.appendChild(li);
                });
                if (json.count > 10) {
                    var more = document.createElement('li');
                    more.className = 'text-muted';
                    more.textContent = '... et ' + (json.count - 10) + ' autre(s)';
                    list.appendChild(more);
                }
                panel.appendChild(list);
            }
        })
        .catch(function () {
            panel.textContent = '';
            var err = document.createElement('p');
            err.className = 'text-danger small';
            err.textContent = 'Erreur lors du chargement.';
            panel.appendChild(err);
        });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
