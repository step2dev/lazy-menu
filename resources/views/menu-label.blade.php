@if(! empty($item['icon_view']))
    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center" aria-hidden="true">@include($item['icon_view'])</span>
@elseif(! empty($item['icon']))
    <i class="{{ $item['icon'] }} shrink-0" aria-hidden="true"></i>
@endif
<span class="min-w-0 flex-1">{{ __($item['label'] ?? '') }}</span>
@if(isset($item['badge']))
    <span class="ml-auto rounded-full bg-cyan-500 px-2 py-0.5 text-xs font-medium text-slate-950">{{ $item['badge'] }}</span>
@endif
