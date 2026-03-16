<x-dashboard::layouts.master
    :title="'Nouveau projet — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Nouveau projet">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Nouveau projet</h4>
            <h6>{{ $instance->name }} &mdash; Créer un projet</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Réduire" id="collapse-header">
                <i class="ti ti-chevron-up"></i>
            </a>
        </li>
    </ul>
    <div class="page-btn mt-0">
        <a href="{{ route('eshop360.projects.index', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i data-feather="arrow-left" class="me-2"></i>Retour aux projets
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('eshop360.projects.store', $instance->slug ?? '') }}" method="POST">
    @csrf

    <div class="row g-4">
        {{-- Left column: main info --}}
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 d-flex align-items-center gap-2">
                        <i class="ti ti-info-circle text-primary"></i> Informations générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du projet <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Ex: Refonte site web client X" required autofocus>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="4" placeholder="Description détaillée du projet…">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Statut <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="planning"  {{ old('status', 'planning') === 'planning'  ? 'selected' : '' }}>Planification</option>
                                <option value="active"    {{ old('status') === 'active'    ? 'selected' : '' }}>Actif</option>
                                <option value="on_hold"   {{ old('status') === 'on_hold'   ? 'selected' : '' }}>En pause</option>
                                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                                <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priorité <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror">
                                <option value="low"      {{ old('priority', 'medium') === 'low'      ? 'selected' : '' }}>Basse</option>
                                <option value="medium"   {{ old('priority', 'medium') === 'medium'   ? 'selected' : '' }}>Moyenne</option>
                                <option value="high"     {{ old('priority') === 'high'     ? 'selected' : '' }}>Haute</option>
                                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critique</option>
                            </select>
                            @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dates & budget --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 d-flex align-items-center gap-2">
                        <i class="ti ti-calendar text-primary"></i> Dates &amp; Budget
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Date de début</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de fin prévue</label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Budget</label>
                            <div class="input-group">
                                <input type="number" name="budget" step="0.01" min="0"
                                       class="form-control @error('budget') is-invalid @enderror"
                                       value="{{ old('budget') }}" placeholder="0.00">
                                <span class="input-group-text">{{ $instance->settings['currency'] ?? 'FCFA' }}</span>
                            </div>
                            @error('budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column: client + submit --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 d-flex align-items-center gap-2">
                        <i class="ti ti-building text-primary"></i> Client
                    </h5>
                </div>
                <div class="card-body">
                    <label class="form-label">Client associé</label>
                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                        <option value="">— Aucun client —</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Créer le projet
                    </button>
                    <a href="{{ route('eshop360.projects.index', $instance->slug ?? '') }}" class="btn btn-outline-secondary">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

</x-dashboard::layouts.master>
