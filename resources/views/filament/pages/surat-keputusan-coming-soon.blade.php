<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center min-h-[400px] text-center">
        <div class="mb-8">
            <div class="flex items-center justify-center w-24 h-24 mx-auto mb-4 rounded-full bg-warning-100">
                <x-heroicon-o-wrench-screwdriver class="w-12 h-12 text-warning-600" />
            </div>
        </div>

        <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
            Fitur Sedang Dikembangkan
        </h2>

        <p class="max-w-md mb-6 text-gray-600 dark:text-gray-400">
            Fitur Surat Keputusan Guru sedang dalam tahap pengembangan dan akan segera tersedia.
            Terima kasih atas kesabaran Anda.
        </p>

        {{-- <div class="p-4 mb-6 border rounded-lg bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-800">
            <div class="flex items-center">
                <x-heroicon-o-information-circle class="w-5 h-5 mr-2 text-warning-600 dark:text-warning-400" />
                <span class="text-sm text-warning-800 dark:text-warning-200">
                    Estimasi penyelesaian: Q1 2024
                </span>
            </div>
        </div> --}}


        <div class="flex mt-2 space-x-4">
            <x-filament::button class="mr-2 padding-x-4"
                color="gray"
                spaceAfter="false"
                tag="a"
                href="{{ filament()->getUrl() }}"
                icon="heroicon-o-arrow-left"
            >
                Kembali
            </x-filament::button>
            <x-filament::button
                color="primary"
                tag="a"
                href="mailto:admin@sekolah.com"
                icon="heroicon-o-envelope"
            >
                Hubungi Admin
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
