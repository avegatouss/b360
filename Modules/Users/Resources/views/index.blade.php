<x-users::layouts.master>

@section('content')
<div class="container">
    <h1>Users</h1>

    @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

    <div class="mb-3">
        <a class="btn btn-primary" href="{{ route('users.create') }}">Create User</a>
    </div>

    <table class="table table-sm">
        <thead>
        <tr><th>Name</th><th>Email</th><th></th></tr>
        </thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.edit', $u) }}">Edit</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $users->links() }}
</div>


</x-users::layouts.master>
