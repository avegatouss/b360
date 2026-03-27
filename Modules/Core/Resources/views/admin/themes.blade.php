<x-dashboard::layouts.master
    :title="'Themes — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance ?? null"
    pageTitle="Themes">

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Choisir un theme</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $themeLabels = [
                        'default' => ['label' => 'Par defaut', 'desc' => 'Bleu classique', 'color' => '#405189'],
                        'dark'    => ['label' => 'Sombre', 'desc' => 'Fond sombre, texte clair', 'color' => '#222529'],
                        'blue'    => ['label' => 'Bleu profond', 'desc' => 'Bleu intense', 'color' => '#1565c0'],
                        'green'   => ['label' => 'Vert', 'desc' => 'Vert nature', 'color' => '#2e7d32'],
                        'red'     => ['label' => 'Rouge', 'desc' => 'Rouge intense', 'color' => '#c62828'],
                    ];
                @endphp
                @foreach($themes as $theme)
                @php $info = $themeLabels[$theme] ?? ['label' => ucfirst($theme), 'desc' => '', 'color' => '#666']; @endphp
                <div class="col-md-4 col-lg-3 mb-3">
                    <div class="card h-100 {{ $currentTheme === $theme ? 'border-primary border-2' : '' }}">
                        <div class="card-body text-center">
                            <div class="rounded-circle mx-auto mb-3"
                                 style="width:60px;height:60px;background-color:{{ $info['color'] }};"></div>
                            <h6 class="fw-semibold">{{ $info['label'] }}</h6>
                            <p class="text-muted small mb-3">{{ $info['desc'] }}</p>
                            @if($currentTheme === $theme)
                                <span class="badge bg-primary">Actif</span>
                            @else
                                <form method="POST" action="{{ route('theme.switch') }}">
                                    @csrf
                                    <input type="hidden" name="theme" value="{{ $theme }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Activer</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
