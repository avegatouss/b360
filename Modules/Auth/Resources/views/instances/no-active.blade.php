@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 640px;">
    <h1>No active instance</h1>
    <div class="alert alert-warning mt-3">
        Your account has no active instance membership. Please contact an administrator.
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-outline-secondary">Logout</button>
    </form>
</div>
@endsection
