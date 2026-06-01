<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-building me-2"></i>Adhésions aux instances</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.memberships.sync', [$instance->slug, $user]) }}">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table table-hover table-sm mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>Instance</th>
                            <th>Statut</th>
                            <th>Rôle</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($instances as $inst)
                        @php
                            $m = $memberships->get($inst->id);
                            $currentInstRole = $userRoles[$inst->id] ?? ($userRoles[0] ?? null);
                        @endphp
                        <tr>
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                                        <i class="ti ti-building text-primary" style="font-size:14px;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $inst->name ?? $inst->slug }}</span>
                                        @if($inst->isRoot())
                                            <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:9px;">ROOT</span>
                                        @endif
                                        <small class="text-muted d-block">{{ $inst->slug }}</small>
                                    </div>
                                </div>
                                <input type="hidden" name="memberships[{{ $loop->index }}][instance_id]" value="{{ $inst->id }}">
                            </td>
                            <td class="align-middle">
                                <select class="form-select form-select-sm" name="memberships[{{ $loop->index }}][status]" style="min-width:120px;">
                                    @foreach(['active' => 'Actif', 'invited' => 'Invité', 'disabled' => 'Désactivé'] as $val => $label)
                                        <option value="{{ $val }}" @selected(($m->status ?? 'invited') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="align-middle">
                                <select class="form-select form-select-sm" name="memberships[{{ $loop->index }}][role]" style="min-width:140px;">
                                    <option value="">— Aucun —</option>
                                    @foreach(['instance-admin', 'manager', 'agent', 'user'] as $roleName)
                                        <option value="{{ $roleName }}" @selected($roleName === $currentInstRole)>{{ ucfirst($roleName) }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <button class="btn btn-outline-primary btn-sm">
                <i class="ti ti-check me-1"></i>Mettre à jour les adhésions
            </button>
        </form>
    </div>
</div>
