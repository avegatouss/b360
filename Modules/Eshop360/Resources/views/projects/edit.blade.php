<x-dashboard::layouts.master
    :title="'Modifier : ' . $project->name . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Modifier le projet">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Modifier le projet</h4>
            <h6>{{ $instance->name }} &mdash; {{ $project->name }}</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Voir le tableau"
               href="{{ route('eshop360.projects.show', [$instance->slug ?? '', $project]) }}">
                <i class="ti ti-layout-kanban"></i>
            </a>
        </li>
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

<form action="{{ route('eshop360.projects.update', [$instance->slug ?? '', $project]) }}" method="POST">
    @csrf
    @method('PUT')

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
                               value="{{ old('name', $project->name) }}" required autofocus>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="4">{{ old('description', $project->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Statut <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach(['planning' => 'Planification', 'active' => 'Actif', 'on_hold' => 'En pause', 'completed' => 'Terminé', 'cancelled' => 'Annulé'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $project->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priorité <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror">
                                @foreach(['low' => 'Basse', 'medium' => 'Moyenne', 'high' => 'Haute', 'critical' => 'Critique'] as $val => $label)
                                <option value="{{ $val }}" {{ old('priority', $project->priority) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div class="mt-3">
                        <label class="form-label">Avancement : <span id="progress-label">{{ old('progress', $project->progress ?? 0) }}%</span></label>
                        <input type="range" name="progress" class="form-range" min="0" max="100" step="5"
                               value="{{ old('progress', $project->progress ?? 0) }}"
                               oninput="document.getElementById('progress-label').textContent = this.value + '%'">
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
                                   value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de fin prévue</label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Budget</label>
                            <div class="input-group">
                                <input type="number" name="budget" step="0.01" min="0"
                                       class="form-control @error('budget') is-invalid @enderror"
                                       value="{{ old('budget', $project->budget) }}">
                                <span class="input-group-text">{{ $instance->settings['currency'] ?? 'FCFA' }}</span>
                            </div>
                            @error('budget') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column --}}
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
                            <option value="{{ $customer->id }}"
                                {{ old('customer_id', $project->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Project meta --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <p class="text-muted small mb-1">Créé le</p>
                    <p class="fw-semibold mb-3">{{ $project->created_at?->format('d/m/Y H:i') }}</p>
                    <p class="text-muted small mb-1">Dernière modification</p>
                    <p class="fw-semibold mb-0">{{ $project->updated_at?->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>Enregistrer les modifications
                    </button>
                    <a href="{{ route('eshop360.projects.show', [$instance->slug ?? '', $project]) }}"
                       class="btn btn-outline-primary">
                        <i class="ti ti-layout-kanban me-1"></i>Voir le tableau
                    </a>
                    <a href="{{ route('eshop360.projects.index', $instance->slug ?? '') }}"
                       class="btn btn-outline-secondary">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

</x-dashboard::layouts.master>
