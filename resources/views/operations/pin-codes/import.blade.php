<x-app-layout
    :page-title="__('Bulk import pin codes')"
    :welcome-line="__('Upload a UTF-8 CSV (Excel: Save As CSV).')"
>
    <div class="mom-card mb-8 p-6">
        <h2 class="mom-section-title">{{ __('Required columns') }}</h2>
        <p class="mom-subtext mt-2 max-w-3xl text-[var(--text-secondary)]">
            {{ __('Headers are matched case-insensitively. Required: pincode, area_name, city. Optional: locality, serviceability (yes/no/1/0), delivery_charge, meta_title, meta_description, seo_keywords.') }}
        </p>
        <p class="mom-micro mt-4">{{ __('Example header row') }}</p>
        <pre class="mom-subtext mt-2 overflow-x-auto rounded-mom-sm border border-[rgba(255,255,255,0.06)] bg-[rgba(0,0,0,0.2)] p-4 font-mono text-[12px] text-[var(--text-secondary)]">pincode,area_name,city,locality,serviceability,delivery_charge,meta_title,meta_description,seo_keywords</pre>
        <p class="mom-subtext mt-4 text-[var(--text-muted)]">{{ __('Existing pincodes are skipped (no overwrite). Maximum file size 5 MB.') }}</p>
    </div>

    <div class="mom-card p-6">
        <h2 class="mom-section-title">{{ __('Upload CSV') }}</h2>
        <form method="post" action="{{ route('operations.pin-codes.import.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf
            <div>
                <x-input-label for="file" :value="__('CSV file')" variant="mom" />
                <input
                    id="file"
                    name="file"
                    type="file"
                    accept=".csv,text/csv,text/plain"
                    required
                    class="mt-2 block w-full text-sm text-[var(--text-secondary)] file:mr-4 file:rounded-mom-md file:border-0 file:bg-[rgba(212,169,95,0.18)] file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-widest file:text-[#0a0a0a]"
                />
                <x-input-error class="mt-2" :messages="$errors->get('file')" variant="mom" />
            </div>
            <div class="flex flex-wrap gap-3">
                <x-primary-button variant="mom">{{ __('Run import') }}</x-primary-button>
                <a href="{{ route('operations.pin-codes.index') }}" class="inline-flex items-center justify-center rounded-mom-md border border-[rgba(255,255,255,0.045)] bg-[rgba(255,255,255,0.03)] px-5 py-2.5 text-xs font-semibold uppercase tracking-widest text-[var(--text-secondary)] shadow-mom-inner transition-all duration-320 ease-premium hover:border-[rgba(212,169,95,0.16)] hover:text-[var(--text-primary)]">{{ __('Back to directory') }}</a>
            </div>
        </form>
    </div>
</x-app-layout>
