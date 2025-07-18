<div class="space-y-6">
    @foreach($fields as $fieldName)
        @php
            $images = $record->getImageUrls($fieldName);
            $fieldLabel = match($fieldName) {
                'fotoAgama' => 'Dokumentasi Agama',
                'fotoJatiDiri' => 'Dokumentasi Jati Diri',
                'fotoLiterasi' => 'Dokumentasi Literasi',
                'fotoNarasi' => 'Dokumentasi Narasi',
                default => ucfirst($fieldName)
            };
        @endphp

        @if(count($images) > 0)
            <div>
                <h3 class="text-lg font-semibold mb-3 text-gray-900">{{ $fieldLabel }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($images as $image)
                        <div class="aspect-square rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                            <img src="{{ $image }}"
                                 alt="{{ $fieldLabel }}"
                                 class="w-full h-full object-cover cursor-pointer hover:scale-105 transition-transform"
                                 onclick="window.open('{{ $image }}', '_blank')">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>
