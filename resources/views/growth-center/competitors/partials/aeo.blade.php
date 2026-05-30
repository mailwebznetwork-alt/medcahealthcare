{{-- AEO signals (embedded under Growth Center SEO tab) --}}
<article class="mom-card p-5">
    <h3 class="mom-section-title">{{ __('AEO — AI visibility signals') }}</h3>
    <p class="mom-body-text mt-2 text-[var(--text-secondary)]">{{ __('Track how you want to be represented in AI answer engines. Public crawler policy lives in /llm.txt (edited under Technical settings below).') }}</p>

    <form method="post" action="{{ route('growth-center.aeo.store') }}" class="mt-6 space-y-4">
        @csrf
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="ai_crawl_enabled" value="0">
            <input type="checkbox" name="ai_crawl_enabled" value="1" @checked((bool) old('ai_crawl_enabled', $seoAiSignal?->ai_crawl_enabled ?? false)) class="rounded border-[rgba(255,255,255,0.12)] bg-transparent text-[var(--success)]">
            <span class="mom-micro">{{ __('AI crawl preference (internal signal)') }}</span>
        </label>
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
            <label class="block">
                <span class="mom-micro mb-1 block">{{ __('LLM visibility score (0–100)') }}</span>
                <input type="number" min="0" max="100" name="llm_visibility_score" value="{{ old('llm_visibility_score', $seoAiSignal?->llm_visibility_score ?? 0) }}" class="w-full rounded-mom-chrome border border-[rgba(255,255,255,0.06)] bg-[rgba(28,22,18,0.75)] px-3 py-2 text-sm text-[var(--text-primary)]" required>
            </label>
            <label class="block">
                <span class="mom-micro mb-1 block">{{ __('Entity consistency score (0–100)') }}</span>
                <input type="number" min="0" max="100" name="entity_consistency_score" value="{{ old('entity_consistency_score', $seoAiSignal?->entity_consistency_score ?? 0) }}" class="w-full rounded-mom-chrome border border-[rgba(255,255,255,0.06)] bg-[rgba(28,22,18,0.75)] px-3 py-2 text-sm text-[var(--text-primary)]" required>
            </label>
        </div>
        <button type="submit" class="mom-cta-primary mom-cta-compact">{{ __('Save AEO signals') }}</button>
    </form>

    <div class="mom-subtext mt-6 rounded-mom-chrome border border-[rgba(255,255,255,0.06)] bg-[rgba(28,22,18,0.45)] p-4">
        <p class="mom-micro">{{ __('How this fits together') }}</p>
        <ul class="mom-body-text mt-2 list-inside list-disc space-y-1 text-[var(--text-secondary)]">
            <li>{{ __('Scores here are for your dashboard / reporting; they do not change robots or llm.txt by themselves.') }}</li>
            <li>{{ __('Actual bot rules: edit “llm.txt” in Technical settings, or leave blank for the default allow-list.') }}</li>
            <li>{{ __('Structured data for AI: use Global FAQ, entity JSON-LD, and /ai-discovery (toggle in Technical).') }}</li>
        </ul>
    </div>
</article>
