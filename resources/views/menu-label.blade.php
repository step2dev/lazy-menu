@php
    $iconView = $item['icon_view'] ?? null;
    $icon = $item['icon'] ?? null;

    if (is_string($iconView) && ! str_contains($iconView, '::')) {
        $lazyAdminIconView = 'lazy::'.$iconView;

        if (view()->exists($lazyAdminIconView)) {
            $iconView = $lazyAdminIconView;
        }
    }

    $svgIcon = null;

    if (is_string($icon)) {
        $normalizedIcon = ltrim($icon, "\xEF\xBB\xBF \t\n\r\0\x0B");
        $normalizedIcon = preg_replace('/^<\?xml[^?]*\?>\s*/i', '', $normalizedIcon) ?? $normalizedIcon;
        $normalizedIcon = preg_replace('/^<!DOCTYPE[^>]*>\s*/i', '', $normalizedIcon) ?? $normalizedIcon;

        if (preg_match('/^<svg\b/i', $normalizedIcon) === 1) {
            $svgIcon = $normalizedIcon;
        }
    }

    $rawSvgIcon = $svgIcon !== null;
@endphp

@if(is_string($iconView) && view()->exists($iconView))
    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center [&>svg]:h-5 [&>svg]:w-5" aria-hidden="true">
        @include($iconView)
    </span>
@elseif($rawSvgIcon)
    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center [&>svg]:h-5 [&>svg]:w-5" aria-hidden="true">
        {!! $svgIcon !!}
    </span>
@elseif(filled($icon))
    <i class="{{ $icon }} shrink-0" aria-hidden="true"></i>
@endif

<span class="min-w-0 flex-1">{{ __($item['label'] ?? '') }}</span>

@if(isset($item['badge']))
    <span class="ml-auto rounded-full bg-cyan-500 px-2 py-0.5 text-xs font-medium text-slate-950">
        {{ is_numeric($item['badge']) && (int) $item['badge'] > 99 ? '99+' : $item['badge'] }}
    </span>
@endif
