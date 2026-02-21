@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 520px;">
    <h1>Select an instance</h1>

    <form method="POST" action="{{ route('instances.choose') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Instance</label>
            <select name="slug" class="form-select" required>
                @foreach($instances as $slug)
                    <option value="{{ $slug }}">{{ $slug }}</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary">Continue</button>
    </form>
</div>
@endsection
