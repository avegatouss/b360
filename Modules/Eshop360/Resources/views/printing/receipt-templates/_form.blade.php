@php $t = $template ?? null; @endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Template Name') }}<span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $t?->name) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Store (optional)') }}</label>
        <select name="store_id" class="form-control">
            <option value="">{{ __('All Stores') }}</option>
            @foreach($stores as $id => $name)
                <option value="{{ $id }}" @selected(old('store_id', $t?->store_id) == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('Paper Width') }}<span class="text-danger">*</span></label>
        <select name="paper_width" class="form-control" required>
            <option value="80mm" @selected(old('paper_width', $t?->paper_width ?? '80mm') === '80mm')>{{ __('80mm (Standard)') }}</option>
            <option value="58mm" @selected(old('paper_width', $t?->paper_width ?? '80mm') === '58mm')>{{ __('58mm (Compact)') }}</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('Font Size') }}<span class="text-danger">*</span></label>
        <select name="font_size" class="form-control" required>
            <option value="small" @selected(old('font_size', $t?->font_size) === 'small')>{{ __('Small') }}</option>
            <option value="normal" @selected(old('font_size', $t?->font_size ?? 'normal') === 'normal')>{{ __('Normal') }}</option>
            <option value="large" @selected(old('font_size', $t?->font_size) === 'large')>{{ __('Large') }}</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('Default Template') }}</label>
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="is_default" value="0">
            <input class="form-check-input" type="checkbox" name="is_default" value="1" @checked(old('is_default', $t?->is_default))>
            <label class="form-check-label">{{ __('Set as default') }}</label>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Header Text') }}</label>
        <textarea name="header_text" class="form-control" rows="3" placeholder="{{ __('Text displayed at the top of the receipt...') }}">{{ old('header_text', $t?->header_text) }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Footer Text') }}</label>
        <textarea name="footer_text" class="form-control" rows="3" placeholder="{{ __('Text displayed at the bottom of the receipt...') }}">{{ old('footer_text', $t?->footer_text) }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input type="hidden" name="show_logo" value="0">
            <input class="form-check-input" type="checkbox" name="show_logo" value="1" @checked(old('show_logo', $t?->show_logo ?? true))>
            <label class="form-check-label">{{ __('Show Logo') }}</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input type="hidden" name="show_address" value="0">
            <input class="form-check-input" type="checkbox" name="show_address" value="1" @checked(old('show_address', $t?->show_address ?? true))>
            <label class="form-check-label">{{ __('Show Address') }}</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input type="hidden" name="show_phone" value="0">
            <input class="form-check-input" type="checkbox" name="show_phone" value="1" @checked(old('show_phone', $t?->show_phone ?? true))>
            <label class="form-check-label">{{ __('Show Phone</label>
        </div>
    </div>
</div>
