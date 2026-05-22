<div class="card mb-0">
    <div class="card-header">
        <h5 class="card-title mb-0">Adhésions aux instances</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.memberships.sync', [$instance->slug, $user]) }}">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-3">
                    <thead>
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
                            $currentRole = $userRoles[$inst->id] ?? null;
                        @endphp
                        <tr>
                            <td>
                                <code>{{ $inst->slug }}</code>
                                @if($inst->isRoot())
                                    <span class="badge bg-primary ms-1">ROOT</span>
                                @endif
                            </td>
                            <td>
                                <select class="form-select form-select-sm" name="memberships[{{ $loop->index }}][status]">
                                    @foreach(['active' => 'Actif', 'invited' => 'Invité', 'disabled' => 'Désactivé'] as $val => $label)
                                        <option value="{{ $val }}" @selected(($m->status ?? 'invited') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="memberships[{{ $loop->index }}][instance_id]" value="{{ $inst->id }}">
                            </td>
                            <td>
                                <select class="form-select form-select-sm" name="memberships[{{ $loop->index }}][role]">
                                    <option value="">— Aucun —</option>
                                    @foreach(['instance-admin', 'manager', 'agent', 'user'] as $role)
                                        <option value="{{ $role }}" @selected($role === $currentRole)>{{ $role }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <button class="btn btn-outline-primary btn-sm">
                <i class="fas fa-check me-1"></i>Mettre à jour les adhésions
            </button>
        </form>
    </div>
</div>
