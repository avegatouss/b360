@extends('dashboard::components.layouts.master')

@section('title', __('lang::common.language') . ' - Translations')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>Gestion des traductions</h4>
            <h6>Gérer les traductions de l'application</h6>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Traductions</h5>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="locale" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($locales as $loc)
                        <option value="{{ $loc }}" {{ $locale === $loc ? 'selected' : '' }}>
                            {{ config("lang.labels.{$loc}", $loc) }}
                        </option>
                    @endforeach
                </select>
                <select name="group" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tous les groupes --</option>
                    @foreach($groups as $g)
                        <option value="{{ $g }}" {{ $group === $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('translations.export', ['slug' => request('slug'), 'locale' => $locale]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-download"></i> Exporter
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($group)
        <form method="POST" action="{{ route('translations.bulk-update', ['slug' => request('slug')]) }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $locale }}">
            <input type="hidden" name="group" value="{{ $group }}">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 30%">Clé</th>
                        <th>Valeur</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($translations as $t)
                    <tr>
                        <td><code>{{ $t->key }}</code></td>
                        <td>
                            <input type="text" name="translations[{{ $t->key }}]" value="{{ $t->value }}" class="form-control form-control-sm">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary mt-3">
                <i class="ti ti-check"></i> Enregistrer
            </button>
        </form>
        @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Groupe</th>
                    <th>Clé</th>
                    <th>Valeur</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($translations as $t)
                <tr>
                    <td><span class="badge bg-secondary">{{ $t->group }}</span></td>
                    <td><code>{{ $t->key }}</code></td>
                    <td>{{ $t->value }}</td>
                    <td>
                        <form method="POST" action="{{ route('translations.destroy', ['slug' => request('slug'), 'translation' => $t->id]) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')">
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{ $translations->links() }}
    </div>
</div>

{{-- Add new translation --}}
<div class="card mt-3">
    <div class="card-header">
        <h5>Ajouter une traduction</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('translations.store', ['slug' => request('slug')]) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-2">
                    <select name="locale" class="form-select">
                        @foreach($locales as $loc)
                            <option value="{{ $loc }}" {{ $locale === $loc ? 'selected' : '' }}>{{ config("lang.labels.{$loc}", $loc) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="group" class="form-control" placeholder="Groupe (ex: eshop360::eshop)" value="{{ $group }}" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="key" class="form-control" placeholder="Clé (ex: product_name)" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="value" class="form-control" placeholder="Valeur" required>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-plus"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Import form --}}
<div class="card mt-3">
    <div class="card-header">
        <h5>Importer des traductions (JSON)</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('translations.import', ['slug' => request('slug')]) }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Langue</label>
                    <select name="locale" class="form-select">
                        @foreach($locales as $loc)
                            <option value="{{ $loc }}">{{ config("lang.labels.{$loc}", $loc) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fichier JSON</label>
                    <input type="file" name="file" class="form-control" accept=".json" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100"><i class="ti ti-upload"></i> Importer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
