@php($matieresJson = $matieres->map(fn ($m) => ['id' => $m->id, 'label' => $m->code.' — '.$m->designation, 'unite' => $m->unite, 'prix' => (float) $m->prix_unitaire])->toJson())
<x-menuiserie360::layout title="Nouveau devis menuiserie">
    <div class="card" x-data="devisForm({{ $matieresJson }})">
        <div class="card-body">
            <form method="POST" action="{{ route('menuiserie.devis.store', ['slug' => request()->route('slug')]) }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client <span class="text-danger">*</span></label>
                        <x-menuiserie360::client-search name="client_id" :value="old('client_id')" required />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Taux TVA</label>
                        <input type="number" name="taux_tva" :value="tauxTva" @input="tauxTva = parseFloat($event.target.value) || 0" step="0.01" min="0" max="1" class="form-control"/>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Validité (jours)</label>
                        <input type="number" name="validite_jours" value="{{ old('validite_jours', 30) }}" min="1" max="365" class="form-control"/>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Marge min.</label>
                        <input type="number" name="marge_minimum" value="{{ old('marge_minimum', 0.15) }}" step="0.01" min="0" max="1" class="form-control"/>
                    </div>
                </div>

                <hr/>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Lignes</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addLigne()">+ Ajouter une ligne</button>
                </div>

                <template x-for="(ligne, i) in lignes" :key="i">
                    <div class="border rounded p-2 mb-2 position-relative">
                        <button type="button" class="btn btn-sm btn-link text-danger position-absolute top-0 end-0" @click="removeLigne(i)" x-show="lignes.length > 1" title="Supprimer la ligne">&times;</button>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small">Désignation <span class="text-danger">*</span></label>
                                <input :name="`lignes[${i}][designation]`" x-model="ligne.designation" placeholder="ex: Fenêtre alu 1500x1200" class="form-control" required maxlength="200"/>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Matière liée</label>
                                <select :name="`lignes[${i}][matiere_id]`" class="form-select" @change="ligne.matiere_id = $event.target.value ? parseInt($event.target.value) : null; applyMatierePrice(ligne)" x-model="ligne.matiere_id">
                                    <option value="">— Aucune —</option>
                                    <template x-for="m in matieres" :key="m.id">
                                        <option :value="m.id" x-text="m.label" :selected="ligne.matiere_id === m.id"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small">Qté</label>
                                <input :name="`lignes[${i}][quantite]`" x-model.number="ligne.quantite" type="number" min="1" class="form-control" required/>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">PU HT</label>
                                <input :name="`lignes[${i}][prix_unitaire_ht]`" x-model.number="ligne.prix_unitaire_ht" type="number" step="0.01" min="0" class="form-control" required/>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Total HT</label>
                                <input :value="lineTotal(ligne).toFixed(2)" type="text" class="form-control" readonly tabindex="-1"/>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Coût revient</label>
                                <input :name="`lignes[${i}][cout_revient]`" x-model.number="ligne.cout_revient" type="number" step="0.01" min="0" class="form-control"/>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Largeur (mm)</label>
                                <input :name="`lignes[${i}][largeur_mm]`" x-model.number="ligne.largeur_mm" type="number" min="1" class="form-control"/>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Hauteur (mm)</label>
                                <input :name="`lignes[${i}][hauteur_mm]`" x-model.number="ligne.hauteur_mm" type="number" min="1" class="form-control"/>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="card bg-light mt-3">
                    <div class="card-body py-2 px-3">
                        <div class="row text-end">
                            <div class="col-md-4"><strong>Total HT :</strong> <span x-text="totalHt().toFixed(2)"></span> XOF</div>
                            <div class="col-md-4"><strong>TVA (<span x-text="(tauxTva * 100).toFixed(0)"></span>%) :</strong> <span x-text="totalTva().toFixed(2)"></span> XOF</div>
                            <div class="col-md-4"><strong>Total TTC :</strong> <span class="fs-5 text-primary" x-text="totalTtc().toFixed(2)"></span> XOF</div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Conditions</label>
                    <textarea name="conditions" rows="3" class="form-control" maxlength="5000">{{ old('conditions') }}</textarea>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Créer le devis</button>
                    <a href="{{ route('menuiserie.devis.index', ['slug' => request()->route('slug')]) }}" class="btn btn-link">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
    <script>
        function devisForm(matieres) {
            return {
                matieres: matieres,
                tauxTva: 0.18,
                lignes: [this.makeLigne()],
                makeLigne() {
                    return {
                        designation: '',
                        matiere_id: null,
                        quantite: 1,
                        prix_unitaire_ht: 0,
                        cout_revient: 0,
                        largeur_mm: null,
                        hauteur_mm: null,
                    };
                },
                addLigne() {
                    this.lignes.push(this.makeLigne());
                },
                removeLigne(i) {
                    if (this.lignes.length > 1) this.lignes.splice(i, 1);
                },
                applyMatierePrice(ligne) {
                    if (!ligne.matiere_id) return;
                    const m = this.matieres.find(x => x.id === parseInt(ligne.matiere_id));
                    if (m && (!ligne.prix_unitaire_ht || ligne.prix_unitaire_ht === 0)) {
                        ligne.prix_unitaire_ht = m.prix;
                    }
                },
                lineTotal(ligne) {
                    return (parseFloat(ligne.quantite) || 0) * (parseFloat(ligne.prix_unitaire_ht) || 0);
                },
                totalHt() {
                    return this.lignes.reduce((sum, l) => sum + this.lineTotal(l), 0);
                },
                totalTva() {
                    return this.totalHt() * (parseFloat(this.tauxTva) || 0);
                },
                totalTtc() {
                    return this.totalHt() + this.totalTva();
                },
            };
        }
    </script>
    @endpush
</x-menuiserie360::layout>
