@props(['wireModel' => 'block_custom_css'])

<div>
    <label class="block text-xs font-medium uppercase tracking-wide text-[var(--text-muted)]">{{ __('Custom CSS') }}</label>
    <p class="mom-subtext mt-1 mb-2 max-w-xl">{{ __('Optional styles for this block only. Rules are injected on the public page when the block renders. Do not wrap in <style> tags.') }}</p>
    <textarea
        wire:model="{{ $wireModel }}"
        rows="8"
        class="w-full rounded-mom-chrome border border-[var(--border-panel-soft)] bg-[var(--bg-card-matte)] px-3 py-2 font-mono text-xs"
        placeholder=".my-block { padding: 2rem; }"
    ></textarea>
    @error($wireModel)
        <p class="mt-1 text-xs text-[var(--danger)]">{{ $message }}</p>
    @enderror
</div>
