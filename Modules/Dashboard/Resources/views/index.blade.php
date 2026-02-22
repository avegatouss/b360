<x-dashboard::layouts.master
    :title="'Dashboard — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Tableau de bord">

    {{-- Statistiques --}}
    <div class="stat-grid">

        <div class="stat-card">
            <div class="stat-label">Membres actifs</div>
            <div class="stat-value">{{ $memberCount }}</div>
            <div class="stat-sub">sur cette instance</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Utilisateurs totaux</div>
            <div class="stat-value">{{ $totalUsers }}</div>
            <div class="stat-sub">dans le système</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Mode instance</div>
            <div class="stat-value" style="font-size:1.25rem;">{{ config('app.instance_mode', 'single') }}</div>
            <div class="stat-sub">{{ config('app.instance_db_strategy', 'shared') }}</div>
        </div>

    </div>

    {{-- Infos instance --}}
    <div class="section-card">
        <div class="section-title">Instance courante</div>

        <div class="info-row">
            <span class="info-key">Nom</span>
            <span class="info-val">{{ $instance->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-key">Slug</span>
            <span class="info-val"><code>{{ $instance->slug }}</code></span>
        </div>
        <div class="info-row">
            <span class="info-key">Domaine</span>
            <span class="info-val">{{ $instance->domain ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-key">Statut</span>
            <span class="info-val">
                @if($instance->is_active)
                    <span class="badge badge-green">Active</span>
                @else
                    <span class="badge badge-gray">Inactive</span>
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-key">Installée le</span>
            <span class="info-val">{{ $instance->installed_at?->format('d/m/Y H:i') ?? '—' }}</span>
        </div>
        @if($instance->meta && isset($instance->meta['is_root']) && $instance->meta['is_root'])
        <div class="info-row">
            <span class="info-key">Type</span>
            <span class="info-val"><span class="badge badge-blue">Instance ROOT</span></span>
        </div>
        @endif
    </div>

    {{-- Liens rapides --}}
    <div class="section-card">
        <div class="section-title">Accès rapides</div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;padding:.25rem 0;">
            <a href="{{ route('users.index', $instance->slug) }}"
               style="padding:.5rem 1rem;background:#eff6ff;color:#2563eb;border-radius:6px;font-size:.875rem;font-weight:500;text-decoration:none;">
                Gérer les utilisateurs
            </a>
        </div>
    </div>

</x-dashboard::layouts.master>
