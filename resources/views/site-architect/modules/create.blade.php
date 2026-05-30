@php
    $moduleFieldSeed = old('fields', []);
@endphp

<x-site-architect.workspace :page-title="__('Create module')" :welcome-line="__('Name your module and define the fields that appear in admin forms.')">
    <h2 class="mom-section-title mb-8">{{ __('Create new module') }}</h2>

    <x-module-field-builder.form :action="route('site-architect.modules.store')" :fields="$moduleFieldSeed">
        <x-admin.card>
            <h3 class="mom-section-title">{{ __('Module details') }}</h3>
            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="name" :value="__('Module name')" variant="mom" />
                    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name')" required variant="mom" placeholder="{{ __('Products') }}" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" variant="mom" />
                </div>
                <div>
                    <x-input-label for="slug" :value="__('Slug (optional)')" variant="mom" />
                    <x-text-input id="slug" name="slug" type="text" class="mt-2 block w-full" :value="old('slug')" variant="mom" placeholder="{{ __('products') }}" />
                    <p class="mom-subtext mt-2 text-xs">{{ __('Used in URLs and table name mod_products.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('slug')" variant="mom" />
                </div>
            </div>
        </x-admin.card>

        @include('site-architect.modules.partials.field-builder', ['fieldTypes' => $fieldTypes])

        <div class="flex flex-wrap gap-3">
            <x-primary-button variant="mom">{{ __('Create module') }}</x-primary-button>
            <a href="{{ route('site-architect.modules.index') }}" class="mom-cta-ghost">{{ __('Cancel') }}</a>
        </div>
    </x-module-field-builder.form>
</x-site-architect.workspace>
