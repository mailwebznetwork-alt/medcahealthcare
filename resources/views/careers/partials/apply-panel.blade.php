@php
    /** @var \App\Models\Vacancy $vacancy */
@endphp

<aside class="mc-apply-panel" aria-label="{{ __('Apply for this role') }}">
    <div class="mc-apply-panel-stack">
        @include('careers.partials.whatsapp-apply', ['vacancy' => $vacancy])

        <div class="mc-apply-online">
            <h2>{{ __('Apply online') }}</h2>
            <p>{{ __('Submit your details — our hiring team will review your application.') }}</p>
            <div class="apply-form-wrap">
                @include('careers.partials.apply-form', ['vacancy' => $vacancy])
            </div>
        </div>
    </div>
</aside>
