<x-dashboard::layouts.master
    :title="'Commande ' . ($onlineOrder->reference ?? $onlineOrder->id) . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Detail commande en ligne">

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Commande {{ $onlineOrder->reference ?? $onlineOrder->order_number ?? $onlineOrder->id }}</h4>
                        <h6>{{ $onlineOrder->created_at->format('d/m/Y H:i') }}</h6>
                    </div>
                </div>
                <div class="page-btn">
                    <a href="{{ route('eshop360.online-orders.index', $instance->slug ?? '') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>

            {{-- Status Timeline --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        @php
                            $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
                            $currentIndex = array_search($onlineOrder->status, $statuses);
                            $isCancelled = in_array($onlineOrder->status, ['cancelled', 'refunded']);
                        @endphp
                        @foreach($statuses as $i => $step)
                            <div class="text-center">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $i <= $currentIndex && !$isCancelled ? 'bg-success' : 'bg-light' }}" style="width:40px;height:40px;">
                                    <i class="ti ti-check text-white"></i>
                                </div>
                                <div class="mt-1 small {{ $i <= $currentIndex && !$isCancelled ? 'fw-bold' : 'text-muted' }}">{{ ucfirst($step) }}</div>
                            </div>
                            @if(!$loop->last)
                                <div class="flex-grow-1 border-top {{ $i < $currentIndex && !$isCancelled ? 'border-success' : '' }}" style="margin-top:-20px;"></div>
                            @endif
                        @endforeach
                        @if($isCancelled)
                            <div class="text-center">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-danger" style="width:40px;height:40px;">
                                    <i class="ti ti-x text-white"></i>
                                </div>
                                <div class="mt-1 small fw-bold text-danger">{{ ucfirst($onlineOrder->status) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Advance Status Buttons --}}
            @if(!$isCancelled && $onlineOrder->status !== 'delivered')
            <div class="card mb-3">
                <div class="card-body d-flex gap-2 flex-wrap">
                    @php
                        $nextStatus = $statuses[$currentIndex + 1] ?? null;
                    @endphp
                    @if($nextStatus)
                    <form method="POST" action="{{ route('eshop360.online-orders.status', [$instance->slug ?? '', $onlineOrder]) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-arrow-right me-1"></i>Passer a {{ ucfirst($nextStatus) }}
                        </button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('eshop360.online-orders.status', [$instance->slug ?? '', $onlineOrder]) }}" onsubmit="return confirm('Annuler cette commande ?')">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="ti ti-x me-1"></i>Annuler
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <div class="row">
                {{-- Order Info --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h5>Informations</h5></div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th>Reference</th><td>{{ $onlineOrder->reference ?? $onlineOrder->order_number ?? $onlineOrder->id }}</td></tr>
                                <tr><th>Client</th><td>{{ $onlineOrder->customer->name ?? $onlineOrder->customer_name ?? '---' }}</td></tr>
                                <tr><th>Email</th><td>{{ $onlineOrder->customer->email ?? $onlineOrder->customer_email ?? '---' }}</td></tr>
                                <tr><th>Telephone</th><td>{{ $onlineOrder->customer->phone ?? $onlineOrder->customer_phone ?? '---' }}</td></tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        @php
                                            $badgeClass = match($onlineOrder->status) {
                                                'completed', 'delivered' => 'bg-success',
                                                'pending' => 'bg-warning',
                                                'processing', 'confirmed' => 'bg-info',
                                                'shipped' => 'bg-purple',
                                                'cancelled', 'refunded' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($onlineOrder->status) }}</span>
                                    </td>
                                </tr>
                                <tr><th>Paiement</th><td>{{ ucfirst(str_replace('_', ' ', $onlineOrder->payment_method ?? '---')) }}</td></tr>
                                @if($onlineOrder->shipping_address)
                                <tr><th>Adresse</th><td>{{ $onlineOrder->shipping_address }}</td></tr>
                                @endif
                                @if($onlineOrder->notes)
                                <tr><th>Notes</th><td>{{ $onlineOrder->notes }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h5>Totaux</h5></div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th>Sous-total</th><td class="text-end">{{ number_format($onlineOrder->subtotal ?? 0, 2) }}</td></tr>
                                <tr><th>Taxes</th><td class="text-end">{{ number_format($onlineOrder->tax_amount ?? 0, 2) }}</td></tr>
                                @if(($onlineOrder->discount_amount ?? 0) > 0)
                                <tr><th>Remise</th><td class="text-end text-danger">-{{ number_format($onlineOrder->discount_amount, 2) }}</td></tr>
                                @endif
                                @if(($onlineOrder->shipping_amount ?? 0) > 0)
                                <tr><th>Livraison</th><td class="text-end">{{ number_format($onlineOrder->shipping_amount, 2) }}</td></tr>
                                @endif
                                <tr class="fw-bold border-top"><th>Total</th><td class="text-end">{{ number_format($onlineOrder->total ?? 0, 2) }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h5>Articles</h5></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th>SKU</th>
                                            <th>Prix unit.</th>
                                            <th>Qte</th>
                                            <th>Remise</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($onlineOrder->items as $item)
                                        <tr>
                                            <td>{{ $item->product_name ?? $item->product->name ?? '---' }}</td>
                                            <td><code>{{ $item->sku ?? '---' }}</code></td>
                                            <td>{{ number_format($item->unit_price ?? 0, 2) }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->discount ?? 0, 2) }}</td>
                                            <td class="fw-bold">{{ number_format($item->total ?? 0, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center text-muted">Aucun article</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-dashboard::layouts.master>
