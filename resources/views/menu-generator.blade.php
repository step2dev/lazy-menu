<ul class="w-64 space-y-1 rounded-xl bg-slate-900 p-3 text-slate-100">
    @foreach($menuItems as $item)
        @include('lazy-menu::menu-item', ['item' => $item])
    @endforeach
</ul>
