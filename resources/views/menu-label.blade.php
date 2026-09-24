@php
    $iconView = $item['icon_view'] ?? null;
    $icon = $item['icon'] ?? null;

    if (is_string($iconView) && ! str_contains($iconView, '::')) {
        $lazyAdminIconView = 'lazy::'.$iconView;

        if (view()->exists($lazyAdminIconView)) {
            $iconView = $lazyAdminIconView;
        }
    }

    $rawSvgIcon = is_string($icon) && str_starts_with(ltrim($icon), '<svg');
@endphp

@if(is_string($iconView) && view()->exists($iconView))
    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center [&>svg]:h-5 [&>svg]:w-5" aria-hidden="true">
        @include($iconView)
    </span>
@elseif($rawSvgIcon)
    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center [&>svg]:h-5 [&>svg]:w-5" aria-hidden="true">
        {!! $icon !!}
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
