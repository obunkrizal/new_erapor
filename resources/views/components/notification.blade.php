@if(session()->has('notification') || session()->has('success') || session()->has('error') || session()->has('warning') || session()->has('info'))
    @php
        $notification = session('notification') ?: [
            'type' => session()->has('success') ? 'success' : (session()->has('error') ? 'error' : (session()->has('warning') ? 'warning' : 'info')),
            'title' => session()->has('success') ? 'Berhasil!' : (session()->has('error') ? 'Error!' : (session()->has('warning') ? 'Peringatan!' : 'Informasi')),
            'message' => session('success') ?: session('error') ?: session('warning') ?: session('info')
        ];
        
        $colors = [
            'success' => 'from-green-500 to-green-600 border-green-400',
            'error' => 'from-red-500 to-red-600 border-red-400',
            'warning' => 'from-yellow-500 to-yellow-600 border-yellow-400',
            'info' => 'from-blue-500 to-blue-600 border-blue-400',
        ];
        
        $icons = [
            'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'error' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z',
            'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        ];
    @endphp
    
    <div id="notification" class="fixed max-w-sm top-4 right-4 animate-slide-in" style="z-index: 9999;">
        <div class="px-6 py-4 text-white border rounded-lg shadow-xl bg-gradient-to-r {{ $colors[$notification['type']] }}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$notification['type']] }}"></path>
                    </svg>
                </div>
                <div class="flex-1 ml-3">
                    <h4 class="text-sm font-bold">{{ $notification['title'] }}</h4>
                    <p class="mt-1 text-sm opacity-90">{{ $notification['message'] }}</p>
                </div>
                <button onclick="closeNotification()" class="flex-shrink-0 ml-4 text-white transition-colors hover:text-gray-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif