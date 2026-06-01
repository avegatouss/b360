<x-dashboard::layouts.master
    :title="'Preferences — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Preferences utilisateur">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Preferences</h4>
            <h6>Personnalisez votre experience</h6>
        </div>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('users.preferences.update', $instance->slug ?? '') }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Language --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Langue</label>
                        <div class="col-sm-9">
                            <select name="language" class="form-select @error('language') is-invalid @enderror">
                                <option value="fr" @selected(($prefs['language'] ?? '') === 'fr')>Francais</option>
                                <option value="en" @selected(($prefs['language'] ?? '') === 'en')>English</option>
                                <option value="es" @selected(($prefs['language'] ?? '') === 'es')>Espanol</option>
                                <option value="pt" @selected(($prefs['language'] ?? '') === 'pt')>Portugues</option>
                            </select>
                            @error('language') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Theme --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Theme</label>
                        <div class="col-sm-9">
                            <select name="theme" class="form-select @error('theme') is-invalid @enderror">
                                <option value="light" @selected(($prefs['theme'] ?? '') === 'light')>Clair</option>
                                <option value="dark" @selected(($prefs['theme'] ?? '') === 'dark')>Sombre</option>
                                <option value="auto" @selected(($prefs['theme'] ?? '') === 'auto')>Automatique</option>
                            </select>
                            @error('theme') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Timezone --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Fuseau horaire</label>
                        <div class="col-sm-9">
                            <select name="timezone" class="form-select @error('timezone') is-invalid @enderror">
                                @foreach(['Africa/Douala', 'Africa/Lagos', 'Africa/Abidjan', 'Africa/Dakar', 'Africa/Kinshasa', 'Europe/Paris', 'UTC'] as $tz)
                                    <option value="{{ $tz }}" @selected(($prefs['timezone'] ?? '') === $tz)>{{ $tz }}</option>
                                @endforeach
                            </select>
                            @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Date format --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Format de date</label>
                        <div class="col-sm-9">
                            <select name="date_format" class="form-select @error('date_format') is-invalid @enderror">
                                <option value="d/m/Y" @selected(($prefs['date_format'] ?? '') === 'd/m/Y')>31/12/2026 (JJ/MM/AAAA)</option>
                                <option value="m/d/Y" @selected(($prefs['date_format'] ?? '') === 'm/d/Y')>12/31/2026 (MM/JJ/AAAA)</option>
                                <option value="Y-m-d" @selected(($prefs['date_format'] ?? '') === 'Y-m-d')>2026-12-31 (AAAA-MM-JJ)</option>
                            </select>
                            @error('date_format') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Notifications email --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Notifications email</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="notifications_email" value="1"
                                       @checked(($prefs['notifications_email'] ?? '0') === '1')>
                                <label class="form-check-label">Recevoir les notifications par email</label>
                            </div>
                        </div>
                    </div>

                    {{-- Notifications SMS --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Notifications SMS</label>
                        <div class="col-sm-9">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="notifications_sms" value="1"
                                       @checked(($prefs['notifications_sms'] ?? '0') === '1')>
                                <label class="form-check-label">Recevoir les notifications par SMS</label>
                            </div>
                        </div>
                    </div>

                    {{-- Items per page --}}
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Elements par page</label>
                        <div class="col-sm-9">
                            <select name="items_per_page" class="form-select @error('items_per_page') is-invalid @enderror">
                                @foreach([10, 20, 30, 50, 100] as $count)
                                    <option value="{{ $count }}" @selected(($prefs['items_per_page'] ?? '20') == $count)>{{ $count }}</option>
                                @endforeach
                            </select>
                            @error('items_per_page') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
