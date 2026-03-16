<x-dashboard::layouts.master
    :title="'Codifarm Configuration — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Codifarm Configuration">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Codifarm Configuration</h4>
            <h6>Configure rates and revenue shares</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.codifarm.dashboard', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back to Dashboard</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('eshop360.codifarm.config.update', $instance->slug ?? '') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Margin Rate (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="margin_rate" class="form-control @error('margin_rate') is-invalid @enderror" value="{{ old('margin_rate', $config->margin_rate ?? 0) }}" required>
                    @error('margin_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Percentage of margin applied on each order.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Codifarm Share (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="codifarm_share" class="form-control @error('codifarm_share') is-invalid @enderror" value="{{ old('codifarm_share', $config->codifarm_share ?? 0) }}" required>
                    @error('codifarm_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Codifarm's share of the total margin.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Saphir Share (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="saphir_share" class="form-control @error('saphir_share') is-invalid @enderror" value="{{ old('saphir_share', $config->saphir_share ?? 0) }}" required>
                    @error('saphir_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Saphir's share of the total margin.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Commission Rate (%) </label>
                    <input type="number" step="0.01" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror" value="{{ old('commission_rate', $config->commission_rate ?? 0) }}">
                    @error('commission_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Commission rate for sales agents.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Debt Threshold (XAF)</label>
                    <input type="number" step="1" name="debt_threshold" class="form-control @error('debt_threshold') is-invalid @enderror" value="{{ old('debt_threshold', $config->debt_threshold ?? 0) }}">
                    @error('debt_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Alert threshold for outstanding debt.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Settlement Period (days)</label>
                    <input type="number" name="settlement_period" class="form-control @error('settlement_period') is-invalid @enderror" value="{{ old('settlement_period', $config->settlement_period ?? 30) }}">
                    @error('settlement_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Number of days for settlement cycle.</small>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $config->notes ?? '') }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Configuration</button>
                <a href="{{ route('eshop360.codifarm.dashboard', $instance->slug ?? '') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</x-dashboard::layouts.master>
