@php
    $state = $getState();
    $photoCount = is_array($state) && isset($state['photo_count']) ? $state['photo_count'] : 0;
    $previewImages = is_array($state) && isset($state['preview_images']) ? $state['preview_images'] : [];
    $hasMore = is_array($state) && isset($state['has_more']) ? $state['has_more'] : false;
@endphp

<div class="flex items-center space-x-2">
    @if($photoCount > 0)
        <div class="flex -space-x-1">
            @foreach($previewImages as $index => $image)
                @if($index < 3 && !empty($image))
                    <img src="{{ $image }}"
                         alt="Documentation"
                         class="w-8 h-8 rounded-full border-2 border-white object-cover shadow-sm hover:scale-110 transition-transform cursor-pointer"
                         onclick="window.open('{{ $image }}', '_blank')"
                         title="Klik untuk melihat gambar penuh">
                @endif
            @endforeach

            @if($hasMore && $photoCount > 3)
                <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-600">
                    +{{ $photoCount - 3 }}
                </div>
            @endif
        </div>

        <span class="text-sm text-gray-600 font-medium">
            {{ $photoCount }} foto
        </span>
    @else
        <div class="flex items-center space-x-1 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="text-sm italic">Tidak ada foto</span>
        </div>
    @endif
</div>
