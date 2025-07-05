@php
use App\Services\ThemeColorService;

$primaryColor = config('settings.theme_primary_color', '#635bff');
$secondaryColor = config('settings.theme_secondary_color', '#1f2937');

$primaryPalette = ThemeColorService::generateColorPalette($primaryColor);
@endphp

<style>
    :root {
        /* Base colors */
        --color-primary: {{ $primaryColor }};
        --color-secondary: {{ $secondaryColor }};
        
        /* Brand color palette */
        --color-brand-50: {{ $primaryPalette[50] }};
        --color-brand-100: {{ $primaryPalette[100] }};
        --color-brand-200: {{ $primaryPalette[200] }};
        --color-brand-300: {{ $primaryPalette[300] }};
        --color-brand-400: {{ $primaryPalette[400] }};
        --color-brand-500: {{ $primaryColor }};
        --color-brand-600: {{ $primaryPalette[600] }};
        --color-brand-700: {{ $primaryPalette[700] }};
        --color-brand-800: {{ $primaryPalette[800] }};
        --color-brand-900: {{ $primaryPalette[900] }};
    }
</style>
