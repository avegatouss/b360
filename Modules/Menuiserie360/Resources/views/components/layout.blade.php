@php $instance = $instance ?? \Modules\Core\Support\CurrentInstance::get(); @endphp
<x-dashboard::layouts.master :instance="$instance" :title="$title ?? 'Menuiserie360'">
    <div class="page-wrapper">
        <div class="content">
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
    </div>
</x-dashboard::layouts.master>
