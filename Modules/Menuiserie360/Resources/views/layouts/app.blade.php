<x-core::layouts.master>
    @if (session('success'))
        <div class="alert alert-success" style="padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning" style="padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; color: #856404; margin-bottom: 1rem;">
            {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" style="padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; margin-bottom: 1rem;">
            <ul style="margin: 0; padding-left: 1.2rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="padding: 1rem;">
        @yield('content')
        {{ $slot ?? '' }}
    </div>
</x-core::layouts.master>
