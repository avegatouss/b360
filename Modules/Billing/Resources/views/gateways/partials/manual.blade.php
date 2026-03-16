<div class="mb-3">
    <label for="instructions" class="form-label">{{ __('Payment Instructions') }}</label>
    <textarea class="form-control" id="instructions"
              name="credentials[instructions]"
              rows="4">{{ $credentials['instructions'] ?? '' }}</textarea>
</div>

<div class="mb-3">
    <label for="bank_name" class="form-label">{{ __('Bank Name') }}</label>
    <input type="text" class="form-control" id="bank_name"
           name="credentials[bank_name]"
           value="{{ $credentials['bank_name'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="account_number" class="form-label">{{ __('Account Number') }}</label>
    <input type="text" class="form-control" id="account_number"
           name="credentials[account_number]"
           value="{{ $credentials['account_number'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="iban" class="form-label">{{ __('IBAN') }}</label>
    <input type="text" class="form-control" id="iban"
           name="credentials[iban]"
           value="{{ $credentials['iban'] ?? '' }}">
</div>
