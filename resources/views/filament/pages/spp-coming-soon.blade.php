<x-filament-panels::page>
    <div class="relative flex flex-col items-center justify-center min-h-[500px] text-center overflow-hidden">
        <!-- Background decorative elements -->
        <div class="absolute inset-0 z-0 opacity-10">
            <div class="absolute top-0 left-0 w-64 h-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-500 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 rounded-full w-96 h-96 bg-success-500 translate-x-1/3 translate-y-1/3 blur-3xl"></div>
        </div>

        <!-- Main content -->
        <div class="relative z-10 max-w-2xl mx-auto">
            <!-- Animated icon -->
            <div class="mb-8 transition-all duration-700 transform hover:scale-110">
                <div class="relative flex items-center justify-center w-32 h-32 mx-auto mb-6 shadow-lg rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800">
                    <div class="animate-pulse">
                        <svg class="w-16 h-16 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.极 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="absolute -top-2 -right-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-warning-500 animate-bounce">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.293 5.293a1 1 极 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Title with animation -->
            <h2 class="mb-6 text-4极l font-bold text-gray-900 dark:text-white animate-fade-in-up">
                <span class="text-transparent bg-gradient-to-r from-primary-600 to-success-600 bg-clip-text">
                    Fitur Sedang Dikembangkan
                </span>
            </h2>

            <!-- Description -->
            <p class="max-w-2xl mb-8 text-lg leading-relaxed text-gray-600 delay-100 dark:text-gray-300 animate-fade-in-up">
                Sistem Manajemen SPP (Pembayaran SPP) sedang dalam tahap pengembangan.
                Kami sedang menyiapkan fitur-fitur terbaik untuk memudahkan pengelolaan keuangan sekolah Anda.
            </p>

            <!-- Progress indicator -->
            <div class="mb-8 delay-200 animate-fade-in-up">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress Pengembangan</span>
                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">75%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-gradient-to-r from-primary-500 to-success-500 h-2.5 rounded-full transition-all duration-1000 ease-out" style="width: 75%"></div>
                </div>
            </div>

            <!-- Feature highlights -->
            <div class="grid grid-cols-1 gap-4 mb-8 delay-300 md:grid-cols-3 animate-fade-in-up">
                <div class="p-4 transition-shadow bg-white border border-gray-100 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 hover:shadow-md">
                    <div class="flex items-center justify-center w-8 h-8 mx-auto mb-2 rounded-full bg-primary-100 dark:bg-primary-900">
                        <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pembayaran Online</p>
                </div>
                <div class="p-4 transition-shadow bg-white border border-gray-100 rounded-lg shadow-sm dark:bg-gray-800 dark:极order-gray-700 hover:shadow-md">
                    <div class="flex items-center justify-center w-8 h-8 mx-auto mb-2 rounded-full bg-success-100 dark:bg-success-900">
                        <svg class="w-4 h-4 text-success-600 dark:text-success-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4极" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Laporan Keuangan</极>
                </div>
                <div class="p-4 transition-shadow bg-white border border-gray-100 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 hover:shadow-md">
                    <div class="flex items-center justify-center w-8 h-8 mx-auto mb-2 rounded-full bg-warning-100 dark:bg-warning-900">
                        <svg class="w-4 h-4 text-warning-600 dark:text-warning-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Riwayat Transaksi</p>
                </div>
            </div>
            <br>


            <!-- Action buttons -->
            <div class="flex flex-col justify-center gap-4 sm:flex-row animate-fade-in-up delay-400">
                <x-filament::button
                    class="transition-all duration-300 transform hover:scale-105"
                    color="gray"
                    tag="a"
                    href="{{ filament()->getUrl() }}"
                    icon="heroicon-o-arrow-left"
                >
                    Kembali ke Dashboard
                </x-filament::button>

                <x-filament::button
                    class="transition-all duration-300 transform hover:scale-105"
                    color="primary"
                    tag="a"
                    href="mailto:"
                    icon="heroicon-o-envelope"
                >
                    Hubungi Tim Support
                </x-filament::button>
            </div>
        </div>
        <br>
        <br>
         <div class="text-sm text-gray-600 mb-13 mt-13 dark:text-gray-400">
                Copyright &copy; {{ date('Y') }} - Developed by <a href=""  class="text-primary-600 dark:text-primary-400 hover:underline">ObunkRizal</a>
            </div>

        <!-- Floating decorative elements -->
        <div class="absolute opacity-50 bottom-4 right-4">
            <div class="w-16 h-16 rounded-full bg-primary-200 dark:bg-primary-800 animate-pulse"></div>
        </div>
        <div class="absolute top-5 left-4 opacity-30">
            <div class="w-12 h-12 rounded-full bg-success-200 dark:bg-success-800 animate-bounce"></div>
        </div>
    </div>

    <style>
        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out forwards;
        }

        .animate-fade-in-up.delay-100 {
            animation-delay: 0.1s;
        }

        .animate-fade-in-up.delay-200 {
            animation-delay: 0.2s;
        }

        .animate-fade-in-up.delay-300 {
            animation-delay: 0.3s;
        }

        .animate-fade-in-up.delay-400 {
            animation-delay: 0.4s;
        }
    </style>
</x-filament-panels::page>
