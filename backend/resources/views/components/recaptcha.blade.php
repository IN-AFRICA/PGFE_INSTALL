@props(['page' => null])
@if(config('services.recaptcha.site_key'))
    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" data-action="{{ $page }}"></div>
@else
    {{-- recaptcha disabled: no site key configured --}}
@endif

