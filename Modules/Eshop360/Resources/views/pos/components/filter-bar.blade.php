{{-- POS Filter Bar --}}
@php $posRoute = $posRoute ?? 'eshop360.pos.index'; @endphp
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route($posRoute, $instance->slug ?? '') }}" class="row g-2 align-items-center" id="pos-filter-form">
            <div class="col">
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Recherche...') }}">
            </div>
            <div class="col-auto">
                <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Categorie') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="brand_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Marque') }}</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ (string) request('brand_id') === (string) $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($channels->isNotEmpty())
            <div class="col-auto">
                <select name="channel_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Prix standard') }}</option>
                    @foreach($channels as $channel)
                        <option value="{{ $channel->id }}" {{ $activeChannelId === (string) $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'category_id', 'brand_id', 'channel_id']))
            <div class="col-auto">
                <a href="{{ route($posRoute, $instance->slug ?? '') }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
            </div>
            @endif
        </form>
    </div>
</div>
