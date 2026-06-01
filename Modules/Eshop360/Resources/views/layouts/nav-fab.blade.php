{{-- R-401-FIX S3 / ADR-022 — Contribution Eshop360 au slot
     'hierarchical-nav.fab' du master layout Dashboard.

     Extrait de Modules/Dashboard/Resources/views/components/layouts/
     master.blade.php (R-401 mitigation L323-330). Affiche un FAB (Floating
     Action Button) vers le menu hiérarchique Eshop360 — uniquement actif
     quand `eshop360.hierarchical_menu` est enabled.

     Variables disponibles : $instance, $contribution.
--}}
<div style="position:fixed;bottom:24px;left:24px;z-index:1050;">
    <a href="{{ route('eshop360.nav.home', $instance->slug ?? '') }}"
       class="btn btn-primary d-flex align-items-center gap-2 shadow-lg"
       style="border-radius:12px;padding:12px 20px;font-weight:600;">
        <i class="ti ti-layout-grid fs-18"></i> Navigation
    </a>
</div>
