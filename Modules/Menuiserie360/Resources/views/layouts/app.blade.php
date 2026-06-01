@php $instance = $instance ?? \Modules\Core\Support\CurrentInstance::get(); @endphp
<x-dashboard::layouts.master :instance="$instance">
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

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
            {{ $slot ?? '' }}
        </div>
    </div>
</x-dashboard::layouts.master>
