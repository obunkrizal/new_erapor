<x-filament::page>
    @foreach ($widgets as $widget)
        <livewire:{{ $widget }} />
    @endforeach
</x-filament::page>
