<h2>Memberships</h2>

<form method="POST" action="{{ route('users.memberships.sync', $user) }}">
    @csrf
    @method('PUT')

    <table class="table table-sm">
        <thead>
        <tr>
            <th>Instance</th>
            <th>Status</th>
            <th>Roles</th>
        </tr>
        </thead>
        <tbody>
        @foreach($instances as $inst)
            @php $m = $memberships->get($inst->id); @endphp
            <tr>
                <td>{{ $inst->slug }}</td>
                <td>
                    <select class="form-select form-select-sm" name="memberships[{{ $loop->index }}][status]">
                        @foreach(['active','invited','disabled'] as $st)
                            <option value="{{ $st }}" @selected(($m->status ?? 'invited') === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="memberships[{{ $loop->index }}][instance_id]" value="{{ $inst->id }}">
                </td>
                <td>
                    <input class="form-control form-control-sm" name="memberships[{{ $loop->index }}][roles][]" placeholder="instance-admin / manager / agent / user">
                    <small class="text-muted">Enter one role per field (minimal UI).</small>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <button class="btn btn-outline-primary">Update memberships</button>
</form>
