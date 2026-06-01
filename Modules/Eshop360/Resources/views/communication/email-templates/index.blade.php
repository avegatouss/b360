<x-dashboard::layouts.master
    :title="__('Templates E-mail —') . ' ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Templates E-mail')">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Templates E-mail') }}</h4>
                        <h6>{{ __('Gerez vos modeles d\'e-mails') }}</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Actualiser') }}" href="{{ route('eshop360.email-templates.index', $instance->slug) }}">
                            <i class="ti ti-refresh"></i>
                        </a>
                    </li>
                </ul>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Nom') }}</th>
                                    <th>{{ __('Sujet') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Statut') }}</th>
                                    <th class="no-sort">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $template->name)) }}</span>
                                    </td>
                                    <td>{{ Str::limit($template->subject, 50) }}</td>
                                    <td>
                                        @switch($template->type)
                                            @case('transactional')
                                                <span class="badge bg-primary">{{ __('Transactionnel') }}</span>
                                                @break
                                            @case('marketing')
                                                <span class="badge bg-success">{{ __('Marketing') }}</span>
                                                @break
                                            @case('system')
                                                <span class="badge bg-secondary">{{ __('Systeme') }}</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $template->type }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">{{ __('Actif') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Inactif') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 p-2 d-flex align-items-center border rounded"
                                               href="{{ route('eshop360.email-templates.edit', [$instance->slug, $template]) }}"
                                               data-bs-toggle="tooltip" title="{{ __('Modifier') }}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded btn-preview"
                                               href="#"
                                               data-url="{{ route('eshop360.email-templates.preview', [$instance->slug, $template]) }}"
                                               data-bs-toggle="tooltip" title="{{ __('Apercu') }}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">{{ __('Aucun template. Executez le seeder pour creer les templates par defaut.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Apercu du template') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>{{ __('Sujet :') }}</strong> <span id="preview-subject"></span></p>
                    <hr>
                    {{-- Preview is rendered in a sandboxed iframe to prevent XSS --}}
                    <iframe id="preview-frame" style="width:100%;min-height:400px;border:1px solid #dee2e6;border-radius:6px;background:#fafafa;" sandbox="allow-same-origin"></iframe>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-preview').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var url = this.getAttribute('data-url');
                fetch(url)
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        document.getElementById('preview-subject').textContent = data.subject;
                        var frame = document.getElementById('preview-frame');
                        frame.srcdoc = data.body;
                        var modal = new bootstrap.Modal(document.getElementById('previewModal'));
                        modal.show();
                    })
                    .catch(function () {
                        alert('Erreur lors du chargement de l\'apercu.');
                    });
            });
        });
    });
    </script>
    @endpush

</x-dashboard::layouts.master>
