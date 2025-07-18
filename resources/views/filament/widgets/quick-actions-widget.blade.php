<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Aksi Cepat
        </x-slot>

        <x-slot name="description">
            Akses cepat ke fitur-fitur utama sistem
        </x-slot>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Add New Student --}}
            <a href="{{ route('filament.admin.resources.siswas.create') }}"
               class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-700 hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors group">
                <div class="flex flex-col items-center text-center space-y-2">
                    <div class="p-2 bg-primary-100 dark:bg-primary-800 rounded-full">
                        <x-heroicon-o-user-plus class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                    </div>
                    <div class="text-sm font-medium text-primary-900 dark:text-primary-100">Tambah Siswa</div>
                </div>
            </a>

            {{-- Add New Teacher --}}
            <a href="{{ route('filament.admin.resources.gurus.create') }}"
               class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-200 dark:border-success-700 hover:bg-success-100 dark:hover:bg-success-900/30 transition-colors group">
                <div class="flex flex-col items-center text-center space-y-2">
                    <div class="p-2 bg-success-100 dark:bg-success-800 rounded-full">
                        <x-heroicon-o-academic-cap class="w-6 h-6 text-success-600 dark:text-success-400"/>
                    </div>
                    <div class="text-sm font-medium text-success-900 dark:text-success-100">Tambah Guru</div>
                </div>
            </a>

            {{-- Create Assessment --}}
            <a href="{{ route('filament.admin.resources.nilais.create') }}"
               class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-700 hover:bg-warning-100 dark:hover:bg-warning-900/30 transition-colors group">
                <div class="flex flex-col items-center text-center space-y-2">
                    <div class="p-2 bg-warning-100 dark:bg-warning-800 rounded-full">
                        <x-heroicon-o-clipboard-document-check class="w-6 h-6 text-warning-600 dark:text-warning-400"/>
                    </div>
                    <div class="text-sm font-medium text-warning-900 dark:text-warning-100">Buat Penilaian</div>
                </div>
            </a>

            {{-- Manage Classes --}}
            <a href="{{ route('filament.admin.resources.kelas.index') }}"
               class="p-4 bg-info-50 dark:bg-info-900/20 rounded-lg border border-info-200 dark:border-info-700 hover:bg-info-100 dark:hover:bg-info-900/30 transition-colors group">
                <div class="flex flex-col items-center text-center space-y-2">
                    <div class="p-2 bg-info-100 dark:bg-info-800 rounded-full">
                        <x-heroicon-o-building-library class="w-6 h-6 text-info-600 dark:text-info-400"/>
                    </div>
                    <div class="text-sm font-medium text-info-900 dark:text-info-100">Kelola Kelas</div>
                </div>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
