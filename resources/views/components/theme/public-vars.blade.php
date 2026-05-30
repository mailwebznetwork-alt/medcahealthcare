@if ($cssBlock = app(\App\Services\Theme\ThemeResolver::class)->publicCssBlock())
<style id="medca-theme-vars">{!! $cssBlock !!}</style>
@endif
@if ($typographyBlock = app(\App\Services\Theme\ThemeResolver::class)->typographyCssBlock())
<style id="medca-theme-typography">{!! $typographyBlock !!}</style>
@endif
