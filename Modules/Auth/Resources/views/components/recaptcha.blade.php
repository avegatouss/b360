@if(config('recaptcha.enabled'))
<script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form[data-recaptcha]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ config("recaptcha.site_key") }}', {action: form.dataset.recaptchaAction || 'submit'}).then(function(token) {
                        let input = form.querySelector('input[name="recaptcha_token"]');
                        if (!input) {
                            input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'recaptcha_token';
                            form.appendChild(input);
                        }
                        input.value = token;
                        form.submit();
                    });
                });
            });
        });
    });
</script>
@endif
