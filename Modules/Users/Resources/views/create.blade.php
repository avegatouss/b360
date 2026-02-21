@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create User</h1>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="{{ old('email') }}" required>
            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
            @error('password') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <button class="btn btn-primary">Create</button>
        <a class="btn btn-link" href="{{ route('users.index') }}">Back</a>
    </form>
</div>
@endsection
