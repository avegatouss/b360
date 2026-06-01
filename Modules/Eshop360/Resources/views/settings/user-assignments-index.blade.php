<x-dashboard::layouts.master
    :title="__('Affectation des utilisateurs') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Affectation des utilisateurs')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>{{ __('Affectation des utilisateurs') }}</h4>
            <h6>{{ __('Gerez les ressources accessibles par chaque utilisateur') }}</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a href="{{ route('eshop360.settings.user-assignments.index', $instance->slug ?? '') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></a>
        </li>
    </ul>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">{{ __('Utilisateurs de l\'instance') }}</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info d-flex align-items-start mb-4">
            <i class="ti ti-info-circle fs-20 me-2 mt-1"></i>
            <div>
                {{ __('Les utilisateurs avec le role super-admin ou instance-admin ont acces a toutes les ressources par defaut. Seuls les autres utilisateurs necessitent une affectation.') }}
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover border">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Nom') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Roles') }}</th>
                        <th class="text-center">{{ __('Entrepots') }}</th>
                        <th class="text-center">{{ __('Magasins') }}</th>
                        <th class="text-center">{{ __('Clients') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $filteredUsers = $users->filter(function ($user) {
                            $roleNames = $user->roles->pluck('name')->toArray();
                            return !in_array('super-admin', $roleNames) && !in_array('instance-admin', $roleNames);
                        });
                    @endphp

                    @forelse($filteredUsers as $user)
                        @php
                            $counts = $assignmentCounts[$user->id] ?? collect();
                            $warehouseCount = $counts->firstWhere('resource_type', 'warehouse')?->count ?? 0;
                            $storeCount = $counts->firstWhere('resource_type', 'store')?->count ?? 0;
                            $customerCount = $counts->firstWhere('resource_type', 'customer')?->count ?? 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2 bg-light rounded-circle d-flex align-items-center justify-content-center">
                                        <span class="text-muted fw-bold">{{ strtoupper(substr($user->full_name ?? $user->email, 0, 2)) }}</span>
                                    </div>
                                    <span class="fw-medium">{{ $user->full_name ?? $user->email }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary-transparent me-1">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                @if($warehouseCount > 0)
                                    <span class="badge bg-success-transparent">{{ $warehouseCount }}</span>
                                @else
                                    <span class="badge bg-secondary-transparent" title="{{ __('Tous') }}">{{ __('Tous') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($storeCount > 0)
                                    <span class="badge bg-success-transparent">{{ $storeCount }}</span>
                                @else
                                    <span class="badge bg-secondary-transparent" title="{{ __('Tous') }}">{{ __('Tous') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($customerCount > 0)
                                    <span class="badge bg-success-transparent">{{ $customerCount }}</span>
                                @else
                                    <span class="badge bg-secondary-transparent" title="{{ __('Tous') }}">{{ __('Tous') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('eshop360.settings.user-assignments.edit', [$instance->slug ?? '', $user->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-settings me-1"></i>{{ __('Gerer') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="ti ti-users-group fs-24 d-block mb-2"></i>
                                {{ __('Aucun utilisateur a affecter. Seuls les super-admin et instance-admin sont presents.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
