<x-core::layouts.master>
    <div class="container-fluid p-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h1 class="h3 mb-4">{{ $title ?? 'Menuiserie360' }}</h1>

        {{ $slot }}
    </div>
</x-core::layouts.master>
