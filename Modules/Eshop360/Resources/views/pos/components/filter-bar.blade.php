{{-- POS Filter Bar --}}
@php $posRoute = $posRoute ?? 'eshop360.pos.index'; @endphp
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route($posRoute, $instance->slug ?? '') }}" class="row g-2 align-items-center" id="pos-filter-form">
            <div class="col">
                <input type="text" name="search" class="form-control form-control-sm" id="pos-search-input" value="{{ request('search') }}" placeholder="{{ __('Recherche produit, SKU...') }}" autocomplete="off">
            </div>
            <div class="col-auto" style="min-width: 180px;">
                <select name="category_id" class="form-select form-select-sm pos-select2-filter" data-placeholder="{{ __('Categorie') }}" onchange="this.form.submit()">
                    <option value=""></option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto" style="min-width: 180px;">
                <select name="brand_id" class="form-select form-select-sm pos-select2-filter" data-placeholder="{{ __('Marque') }}" onchange="this.form.submit()">
                    <option value=""></option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ (string) request('brand_id') === (string) $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($channels->isNotEmpty())
            <div class="col-auto" style="min-width: 180px;">
                <select name="channel_id" class="form-select form-select-sm pos-select2-filter" data-placeholder="{{ __('Prix standard') }}" onchange="this.form.submit()">
                    <option value=""></option>
                    @foreach($channels as $channel)
                        <option value="{{ $channel->id }}" {{ $activeChannelId === (string) $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @if(request()->hasAny(['search', 'category_id', 'brand_id', 'channel_id']))
            <div class="col-auto">
                <a href="{{ route($posRoute, $instance->slug ?? '') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Effacer filtres') }}"><i class="ti ti-x"></i></a>
            </div>
            @endif
        </form>
    </div>
</div>
