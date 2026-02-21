@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit User</h1>

    @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif

    <form method="POST" action="{{ route('users.update', $user) }}" class="mb-4">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Password (optional)</label>
            <input name="password" type="password" class="form-control">
            @error('password') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <button class="btn btn-primary">Save</button>
        <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline">
            @csrf @method('DELETE')
            <button class="btn btn-danger" onclick="return confirm('Delete user?')">Delete</button>
        </form>
        <a class="btn btn-link" href="{{ route('users.index') }}">Back</a>
    </form>

    @include('users::partials.memberships')
</div>
@endsection
