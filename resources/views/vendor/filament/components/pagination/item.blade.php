@props([
    'active' => false,
    'ariaLabel' => null,
    'disabled' => false,
    'icon' => null,
    'iconAlias' => null,
    'label' => null,
])

@php
    // Fallback for PHP builds without the intl extension (see AppServiceProvider).
    $formatNumber = fn (int|float $number): int|float|string => extension_loaded('intl') ? \Illuminate\Support\Number::format($number) : (int) $number;
@endphp

<li
    {{
        $attributes->class([
            'fi-pagination-item',
            'fi-disabled' => $disabled,
            'fi-active' => $active,
        ])
    }}
>
    <button
        @if (filled($ariaLabel))
            aria-label="{{ $ariaLabel }}"
        @endif
        @if ($active)
            aria-current="page"
        @endif
        @if ($disabled)
            aria-hidden="true"
        @endif
        @disabled($disabled)
        type="button"
        class="fi-pagination-item-btn"
    >
        @if ($icon || $iconAlias)
            {{
                \Filament\Support\generate_icon_html($icon, $iconAlias, attributes: (new \Filament\Support\View\ComponentAttributeBag)->merge(['aria-hidden' => 'true'], escape: false)->class([
                    'fi-pagination-item-icon',
                ]))
            }}
        @endif

        @if (filled($label))
            <span class="fi-pagination-item-label">
                {{ is_numeric($label) ? $formatNumber($label) : ($label ?? '...') }}
            </span>
        @endif
    </button>
</li>