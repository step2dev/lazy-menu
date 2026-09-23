<nav data-test-template>
    @foreach($menuItems as $item)
        <span>{{ $item['label'] ?? '' }}</span>
    @endforeach
</nav>
