<x-dashboard::layouts.master
    :title="'Parametres — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Parametres">

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Left nav: settings groups --}}
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($groups as $grp)
                        <a href="{{ route('settings.group', [$instance->slug, $grp->id]) }}"
                           class="list-group-item list-group-item-action {{ isset($currentGroup) && $currentGroup->id === $grp->id ? 'active' : '' }}">
                            {{ $grp->label }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Right panel: settings form --}}
        <div class="col-md-9">
            @if(isset($currentGroup))
                <form method="POST" action="{{ route('settings.group.update', [$instance->slug, $currentGroup->id]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @include($currentGroup->view, ['values' => $values ?? []])

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            @else
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="ti ti-settings fs-1 mb-3 d-block"></i>
                        <p>Selectionnez un groupe de parametres dans le menu de gauche.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

</x-dashboard::layouts.master>
