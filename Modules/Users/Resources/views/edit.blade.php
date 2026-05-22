 <x-dashboard::layouts.master :title="'Modifier un utilisateur — ' . ($instance->name ?? ($instance->slug ?? 'B360'))" :instance="$instance" pageTitle="Modifier un utilisateur" :breadcrumbs="[
     ['label' => 'Utilisateurs', 'url' => route('users.index', ['slug' => $instance->slug])],
     ['label' => 'Modifier'],
 ]">

     @if (session('status'))
         <div class="alert alert-success alert-dismissible fade show" role="alert">
             <i class="fas fa-check-circle me-2"></i>
             <strong>Succès !</strong> {{ session('status') }}

             <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
     @endif

     <div class="card mb-3">
         <div class="card-header">
             <h5 class="card-title mb-0">Informations</h5>
         </div>
         <div class="card-body">
             <form method="POST" action="{{ route('users.update', [$instance->slug, $user]) }}">
                 @csrf
                 @method('PUT')

                 <div class="row">
                     <div class="col-md-6 mb-3">
                         <label class="form-label">Nom complet</label>
                         <input name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                             value="{{ old('full_name', $user->full_name) }}" required>
                         @error('full_name')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>

                     <div class="col-md-6 mb-3">
                         <label class="form-label">Email</label>
                         <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                             value="{{ old('email', $user->email) }}" required>
                         @error('email')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>
                 </div>

                 <div class="row">
                     <div class="col-md-6 mb-3">
                         <label class="form-label">Nom d'utilisateur <small
                                 class="text-muted">(optionnel)</small></label>
                         <input name="username" class="form-control @error('username') is-invalid @enderror"
                             value="{{ old('username', $user->username) }}">
                         @error('username')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>

                     <div class="col-md-6 mb-3">
                         <label class="form-label">Mot de passe <small class="text-muted">(laisser vide pour ne pas
                                 changer)</small></label>
                         <input name="password" type="password"
                             class="form-control @error('password') is-invalid @enderror">
                         @error('password')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>
                 </div>


                 <div class="row g-3 mb-3">
                     {{-- Les styles sont un peu répétitifs, mais ça permet d'avoir des indicateurs visuels plus clairs --}}
                    {{-- Compte actif --}}
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100 d-flex align-items-center justify-content-between
                                    @if(old('is_active', $user->is_active)) border-success bg-soft-success @else border-secondary bg-light @endif">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center
                                            @if(old('is_active', $user->is_active)) bg-success text-white @else bg-secondary text-white @endif"
                                    style="width: 36px; height: 36px;">
                                    <i class="fas fa-user-check" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size: 0.875rem;">Compte actif</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ old('is_active', $user->is_active) ? 'L\'utilisateur peut se connecter' : 'Accès désactivé' }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    id="is_active" role="switch"
                                    style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                    @checked(old('is_active', $user->is_active))>
                            </div>
                        </div>
                    </div>

                    {{-- Compte bloqué --}}
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100 d-flex align-items-center justify-content-between
                                    @if(old('is_blocked', $user->is_blocked)) border-danger bg-soft-danger @else border-secondary bg-light @endif">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center
                                            @if(old('is_blocked', $user->is_blocked)) bg-danger text-white @else bg-secondary text-white @endif"
                                    style="width: 36px; height: 36px;">
                                    <i class="fas fa-user-lock" style="font-size: 0.8rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark" style="font-size: 0.875rem;">Compte bloqué</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ old('is_blocked', $user->is_blocked) ? 'Utilisateur bloqué' : 'Aucune restriction' }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_blocked" value="0">
                                <input class="form-check-input" type="checkbox" name="is_blocked" value="1"
                                    id="is_blocked" role="switch"
                                    style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                    @checked(old('is_blocked', $user->is_blocked))>
                            </div>
                        </div>
                    </div>
                </div>

                 <div class="d-flex gap-2">
                     <button class="btn btn-primary">
                          <i class="fas fa-save me-1"></i>Enregistrer
                     </button>
                     <a class="btn btn-secondary" href="{{ route('users.index', $instance->slug) }}">
                         Retour
                     </a>
                 </div>
             </form>
         </div>
     </div>

     {{-- Zone de danger --}}
     <div class="card mb-3">
         <div class="card-header">
             <h5 class="card-title mb-0 text-danger">Zone de danger</h5>
         </div>
         <div class="card-body">
             <form method="POST" action="{{ route('users.destroy', [$instance->slug, $user]) }}"
                 onsubmit="return confirm('Supprimer cet utilisateur ? Cette action est irréversible.')">
                 @csrf
                 @method('DELETE')
                 <button class="btn btn-outline-danger">
                     <i class="fas fa-trash me-1"></i>Supprimer l'utilisateur
                 </button>
             </form>
         </div>
     </div>

     {{-- Memberships --}}
     @include('users::partials.memberships')


     @push('scripts')
         <script>
            const isActiveToggle = document.getElementById('is_active');
            const isBlockedToggle = document.getElementById('is_blocked');

            function updateCard(checkbox, activeClass, activeColor) {
                const card = checkbox.closest('.border');
                const icon = card.querySelector('.rounded-circle');
                const subtitle = card.querySelector('.text-muted');
                const isActive = checkbox.name === 'is_active';

                if (checkbox.checked) {
                    card.classList.remove('border-secondary', 'bg-light');
                    card.classList.add(activeClass, isActive ? 'bg-soft-success' : 'bg-soft-danger');
                    icon.classList.remove('bg-secondary');
                    icon.classList.add(activeColor);
                    subtitle.textContent = isActive ? "L'utilisateur peut se connecter" : "Utilisateur bloqué";
                } else {
                    card.classList.remove(activeClass, isActive ? 'bg-soft-success' : 'bg-soft-danger');
                    card.classList.add('border-secondary', 'bg-light');
                    icon.classList.remove(activeColor);
                    icon.classList.add('bg-secondary');
                    subtitle.textContent = isActive ? "Accès désactivé" : "Aucune restriction";
                }
            }

            isBlockedToggle.addEventListener('change', function () {
                // Si on bloque → on désactive automatiquement
                if (this.checked && isActiveToggle.checked) {
                    isActiveToggle.checked = false;
                    updateCard(isActiveToggle, 'border-success', 'bg-success');
                }
                updateCard(this, 'border-danger', 'bg-danger');
            });

            isActiveToggle.addEventListener('change', function () {
                // Si on active → on débloque automatiquement
                if (this.checked && isBlockedToggle.checked) {
                    isBlockedToggle.checked = false;
                    updateCard(isBlockedToggle, 'border-danger', 'bg-danger');
                }
                updateCard(this, 'border-success', 'bg-success');
            });
         </script>
     @endpush

 </x-dashboard::layouts.master>
