@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 420px;">
    <h1>{{ $mode === 'instance' ? 'Login — ' . $instance->slug : 'Login' }}</h1>

    <form method="POST" action="{{ $mode === 'instance' ? url('/i/'.$instance->slug.'/login') : route('login.post') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" value="{{ old('email') }}" required autofocus>
            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
            @error('password') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button class="btn btn-primary w-100">Sign in</button>

        @if($mode === 'global')
            <div class="mt-3 text-muted small">
                Best practice: use instance login URL <code>/i/{slug}/login</code>.
            </div>
        @endif
    </form>
</div>
@endsection
