@php $instance = $instance ?? \Modules\Core\Support\CurrentInstance::get(); @endphp
<x-dashboard::layouts.master :instance="$instance" :title="$title ?? 'Référentiel 360'">
    {{-- master.blade fournit déjà <div class="page-wrapper"><div class="content">…</div></div>.
         Ne pas ré-empiler ces wrappers ici (cf. feedback Menuiserie360 double-wrapping). --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h1 class="h3 mb-4">{{ $title ?? 'Référentiel 360' }}</h1>

    {{ $slot }}
</x-dashboard::layouts.master>
