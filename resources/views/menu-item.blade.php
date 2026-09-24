@if(isset($item['group']))
    <li data-menu-group class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __($item['group']) }}</li>
@else
    <li>
        @php
            $target = $item['url'] ?? $item['route'] ?? null;
            $href = $target && Route::has($target) ? route($target, $item['parameters'] ?? []) : ($target ?? '#');
            $hasChildren = ! empty($item['children']);
            $active = $item['active'] ?? ($target && Route::has($target) && request()->routeIs($target));
            $isNamedRoute = is_string($target) && Route::has($target);
            $external = ! $isNamedRoute
                && is_string($target)
                && preg_match('/^https?:\/\//i', $target) === 1
                && parse_url($target, PHP_URL_HOST) !== request()->getHost();
            $linkTarget = $item['target'] ?? ($external ? '_blank' : null);
        @endphp
        @if($hasChildren && $target)
            <div class="flex items-center gap-1">
                <a href="{{ $href }}"
                   @if($linkTarget) target="{{ $linkTarget }}" @endif
                   @if($linkTarget === '_blank') rel="noopener noreferrer" @endif
                   @class(['flex min-w-0 flex-1 items-center gap-3 rounded-lg px-3 py-2 hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-400', 'bg-slate-700 text-white' => $active])>
                    @include('lazy-menu::menu-label', ['item' => $item])
                </a>
                <details class="relative" @if($active) open @endif>
                    <summary class="cursor-pointer rounded-lg px-2 py-2 hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-400" aria-label="{{ __('Toggle :item submenu', ['item' => __($item['label'] ?? '')]) }}"></summary>
                    <ul class="mt-1 space-y-1 border-l border-slate-700 pl-3">
                        @foreach($item['children'] as $child)
                            @include('lazy-menu::menu-item', ['item' => $child])
                        @endforeach
                    </ul>
                </details>
            </div>
        @elseif($hasChildren)
            <details @if($active) open @endif>
                <summary @class(['flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-400', 'bg-slate-700 text-white' => $active])>@include('lazy-menu::menu-label', ['item' => $item])</summary>
                <ul class="mt-1 space-y-1 border-l border-slate-700 pl-3">
                    @foreach($item['children'] as $child)
                        @include('lazy-menu::menu-item', ['item' => $child])
                    @endforeach
                </ul>
            </details>
        @else
            <a href="{{ $href }}"
                   @if($linkTarget) target="{{ $linkTarget }}" @endif
                   @if($linkTarget === '_blank') rel="noopener noreferrer" @endif
                   @class(['flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-400', 'bg-slate-700 text-white' => $active])>@include('lazy-menu::menu-label', ['item' => $item])</a>
        @endif
    </li>
@endif
