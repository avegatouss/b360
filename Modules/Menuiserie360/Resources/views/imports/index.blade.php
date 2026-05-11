<x-menuiserie360::layout title="Imports CSV">
    <x-menuiserie360::page-header
        title="Imports CSV"
        subtitle="Charger des matières premières par lot depuis un fichier CSV."
    />

    @if (session('import_result'))
        @php($result = session('import_result'))
        <div class="card mb-3 {{ count($result['errors']) === 0 ? 'border-success' : 'border-warning' }}">
            <div class="card-header {{ count($result['errors']) === 0 ? 'bg-success bg-opacity-25' : 'bg-warning bg-opacity-25' }}">
                <strong>Résultat du dernier import</strong>
            </div>
            <div class="card-body">
                <p>
                    <strong>{{ $result['created'] }}</strong> matière(s) créée(s) /
                    <strong>{{ $result['updated'] }}</strong> mise(s) à jour /
                    <strong class="{{ count($result['errors']) > 0 ? 'text-danger' : '' }}">{{ count($result['errors']) }}</strong> erreur(s).
                </p>

                @if (! empty($result['errors']))
                    <h6 class="mt-3">Détail des erreurs</h6>
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Ligne</th><th>Erreur</th></tr></thead>
                        <tbody>
                        @foreach ($result['errors'] as $err)
                            <tr>
                                <td class="text-muted">{{ $err['line'] }}</td>
                                <td>{{ $err['message'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><strong>Import matières premières (CSV)</strong></div>
        <div class="card-body">
            <p class="text-muted small">
                Format attendu : séparateur <code>,</code>, en-tête obligatoire avec colonnes
                <code>code, designation, categorie, unite, prix_unitaire, seuil_alerte</code>
                (optionnel : <code>fournisseur_principal, is_active</code>).
                Idempotent — le code unique par instance déclenche un update au lieu d'un doublon.
                Taille max 2 Mo.
            </p>

            <details class="mb-3">
                <summary class="small text-primary" style="cursor:pointer;">Exemple de contenu CSV</summary>
                <pre class="bg-light p-2 small mt-2"><code>code,designation,categorie,unite,prix_unitaire,seuil_alerte,fournisseur_principal,is_active
ALU-001,Profil aluminium 40x60 mm,profile_alu,m_lineaire,2500,50,Fournisseur A,1
ALU-002,Verre clair 4 mm,vitrage,m2,8000,10,Fournisseur B,1</code></pre>
                <p class="small text-muted mt-1">
                    Catégories autorisées : <code>profile_alu, vitrage, accessoire, autre</code>.
                    Unités : <code>m_lineaire, m2, piece</code>.
                </p>
            </details>

            <form method="POST" action="{{ route('menuiserie.imports.matieres.upload', ['slug' => request()->route('slug')]) }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Fichier CSV (.csv ou .txt, max 2 Mo)</label>
                        <input type="file" name="file" accept=".csv,.txt,text/csv" required class="form-control"/>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Importer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-menuiserie360::layout>
