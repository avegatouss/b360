<x-dashboard::layouts.master
    :title="($plan ? 'Modifier' : 'Nouveau') . ' plan — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="$plan ? 'Modifier le plan' : 'Nouveau plan'">

    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ $plan ? route('billing.plans.update', [$instance->slug, $plan->id]) : route('billing.plans.store', $instance->slug) }}">
                @csrf
                @if($plan) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $plan?->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $plan?->slug) }}" {{ $plan ? 'readonly' : '' }}>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $plan?->description) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Prix mensuel (EUR)</label>
                        <input type="number" name="price_monthly" class="form-control" step="0.01" min="0" value="{{ old('price_monthly', $plan?->price_monthly ?? '0.00') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Prix annuel (EUR)</label>
                        <input type="number" name="price_yearly" class="form-control" step="0.01" min="0" value="{{ old('price_yearly', $plan?->price_yearly) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jours d'essai</label>
                        <input type="number" name="trial_days" class="form-control" min="0" value="{{ old('trial_days', $plan?->trial_days ?? 14) }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $plan?->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Actif</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>{{ $plan ? 'Mettre a jour' : 'Creer' }}
                    </button>
                    <a href="{{ route('billing.plans.index', $instance->slug) }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

</x-dashboard::layouts.master>
