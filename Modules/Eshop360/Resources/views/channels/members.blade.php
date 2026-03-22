<x-dashboard::layouts.master
    :title="__('Membres du canal') . ' — ' . ($channel->name ?? '') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Membres du canal')">

@php
    $slug = $instance->slug ?? '';
    $roleColors = ['admin' => 'danger', 'manager' => 'primary', 'operator' => 'info', 'cashier' => 'warning', 'viewer' => 'secondary', 'client' => 'success'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-users-group me-2"></i>{{ __('Membres du canal') }}: {{ $channel->name }}</h4>
        <p class="text-muted mb-0">{{ __('Gerer les utilisateurs et leurs roles dans ce canal de distribution') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.channels.show', [$slug, $channel]) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour au canal') }}</a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#add-member"><i class="ti ti-user-plus me-1"></i>{{ __('Ajouter un membre') }}</button>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- Role legend --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <div class="d-flex gap-3 flex-wrap align-items-center">
            <span class="text-muted fw-bold">{{ __('Hierarchie des roles') }}:</span>
            @foreach($roleLabels as $role => $label)
                <span class="badge bg-{{ $roleColors[$role] ?? 'secondary' }}-subtle text-{{ $roleColors[$role] ?? 'secondary' }} px-3 py-2">
                    <i class="ti ti-{{ match($role) { 'admin' => 'crown', 'manager' => 'star', 'operator' => 'tool', 'cashier' => 'cash-register', 'viewer' => 'eye', 'client' => 'user', default => 'user' } }} me-1"></i>{{ __($label) }}
                </span>
            @endforeach
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
    <div class="col-xl-2 col-sm-4">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 text-center">
            <h3 class="fw-bold mb-0">{{ $members->count() }}</h3>
            <span class="text-muted">{{ __('Total membres') }}</span>
        </div></div>
    </div>
    @foreach(['admin', 'manager', 'operator', 'cashier', 'viewer', 'client'] as $r)
        @php $cnt = $members->where('role', $r)->count(); @endphp
        @if($cnt > 0)
        <div class="col-xl-2 col-sm-4">
            <div class="card border-0 shadow-sm"><div class="card-body py-3 text-center">
                <h3 class="fw-bold mb-0 text-{{ $roleColors[$r] ?? 'secondary' }}">{{ $cnt }}</h3>
                <span class="text-muted">{{ __($roleLabels[$r] ?? $r) }}</span>
            </div></div>
        </div>
        @endif
    @endforeach
</div>

{{-- Members table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-list me-2"></i>{{ __('Membres') }} <span class="badge bg-primary ms-1">{{ $members->count() }}</span></h6></div>
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>{{ __('Utilisateur') }}</th>
                <th>{{ __('Email') }}</th>
                <th class="text-center">{{ __('Role') }}</th>
                <th>{{ __('Assigne le') }}</th>
                <th class="text-end" style="width:200px;">{{ __('Actions') }}</th>
            </tr></thead>
            <tbody>
                @forelse($members as $member)
                    @php $u = $member->user; @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-{{ $roleColors[$member->role] ?? 'secondary' }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <span class="fw-bold text-{{ $roleColors[$member->role] ?? 'secondary' }}">{{ strtoupper(substr($u?->full_name ?? '?', 0, 2)) }}</span>
                                </div>
                                <div class="fw-medium">{{ $u?->full_name ?? __('Utilisateur inconnu') }}</div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $u?->email ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $roleColors[$member->role] ?? 'secondary' }} px-3 py-2">
                                {{ __($roleLabels[$member->role] ?? $member->role) }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $member->created_at?->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <form action="{{ route('eshop360.channels.members.update', [$slug, $channel, $member]) }}" method="POST" class="d-flex gap-1">
                                    @csrf @method('PUT')
                                    <select name="role" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                                        @foreach($roleLabels as $r => $label)
                                            <option value="{{ $r }}" @selected($member->role === $r)>{{ __($label) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <form action="{{ route('eshop360.channels.members.destroy', [$slug, $channel, $member]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Retirer ce membre ?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="ti ti-user-minus"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="ti ti-users-minus fs-1 d-block mb-2"></i>{{ __('Aucun membre dans ce canal.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
</div>

{{-- Add Member Modal --}}
<div class="modal fade" id="add-member" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-user-plus me-2"></i>{{ __('Ajouter un membre') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.channels.members.store', [$slug, $channel]) }}" method="POST">@csrf
        <div class="modal-body">
            @if($availableUsers->isEmpty())
                <div class="alert alert-warning py-2"><i class="ti ti-alert-triangle me-1"></i>{{ __('Tous les utilisateurs sont deja membres de ce canal.') }}</div>
            @else
            <div class="mb-3"><label class="form-label">{{ __('Utilisateur') }} <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select s2-member-modal" required>
                    <option value="">{{ __('Selectionner') }}</option>
                    @foreach($availableUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->full_name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3"><label class="form-label">{{ __('Role') }} <span class="text-danger">*</span></label>
                <select name="role" class="form-select s2-member-modal" required>
                    @foreach($roleLabels as $r => $label)
                        <option value="{{ $r }}" @selected($r === 'operator')>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
            @if($availableUsers->isNotEmpty())<button type="submit" class="btn btn-primary"><i class="ti ti-user-plus me-1"></i>{{ __('Ajouter') }}</button>@endif
        </div>
    </form>
</div></div></div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>jQuery(function ($) { var $m = $('#add-member'); $('.s2-member-modal').each(function () { $(this).select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $m }); }); });</script>
@endpush

</x-dashboard::layouts.master>
