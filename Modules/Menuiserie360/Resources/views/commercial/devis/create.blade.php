<x-menuiserie360::layout title="Nouveau devis menuiserie">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('menuiserie.devis.store', ['slug' => request()->route('slug')]) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Client (ID Eshop360) *</label>
                    <input type="number" name="client_id" class="form-control" required min="1"/>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Taux TVA (fraction)</label>
                        <input type="number" name="taux_tva" value="0.18" step="0.01" min="0" max="1" class="form-control"/>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Validité (jours)</label>
                        <input type="number" name="validite_jours" value="30" min="1" max="365" class="form-control"/>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Marge minimum (fraction)</label>
                        <input type="number" name="marge_minimum" value="0.15" step="0.01" min="0" max="1" class="form-control"/>
                    </div>
                </div>

                <h5>Lignes</h5>
                <p class="text-muted small">UI complète des lignes en P2-B-2. Pour V1 saisir au moins une ligne ci-dessous.</p>

                <div class="row g-2 mb-2">
                    <div class="col"><input name="lignes[0][designation]" placeholder="Désignation" class="form-control" required maxlength="200"/></div>
                    <div class="col"><input name="lignes[0][quantite]" type="number" placeholder="Qté" min="1" class="form-control" required value="1"/></div>
                    <div class="col"><input name="lignes[0][prix_unitaire_ht]" type="number" step="0.01" placeholder="PU HT" min="0" class="form-control" required/></div>
                    <div class="col"><input name="lignes[0][cout_revient]" type="number" step="0.01" placeholder="Coût revient" min="0" class="form-control"/></div>
                    <div class="col"><input name="lignes[0][largeur_mm]" type="number" placeholder="L mm" min="1" class="form-control"/></div>
                    <div class="col"><input name="lignes[0][hauteur_mm]" type="number" placeholder="H mm" min="1" class="form-control"/></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Conditions</label>
                    <textarea name="conditions" rows="3" class="form-control" maxlength="5000"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Créer le devis</button>
            </form>
        </div>
    </div>
</x-menuiserie360::layout>
