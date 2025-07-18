{{-- Logout Notification Component --}}
@if(session('logout_message'))
    @php
        $notification = session('logout_message');
    @endphp
    
    <div id="logout-notification" class="fixed z-50 max-w-sm top-4 right-4 animate-slide-in" style="z-index: 9999;">
        <div class="px-6 py-4 text-white border border-blue-400 rounded-lg shadow-xl bg-gradient-to-r from-blue-500 to-blue-600">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="flex-1 ml-3">
                    <h4 class="text-sm font-bold">{{ $notification['title'] }}</h4>
                    <p class="mt-1 text-sm opacity-90">{{ $notification['message'] }}</p>
                </div>
                <button onclick="closeLogoutNotification()" class="flex-shrink-0 ml-4 text-white transition-colors hover:text-gray-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Check for logout notification in localStorage (fallback method) --}}
@if(session('logout_user_name'))
    <script>
        // Store logout info in localStorage for cross-page persistence
        localStorage.setItem('logout_notification', JSON.stringify({
            title: 'Logout Berhasil',
            message: 'Sampai jumpa lagi, {{ session('logout_user_name') }}! Terima kasih telah menggunakan sistem.',
            timestamp: Date.now()
        }));
    </script>
@endif

<style>
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slide-out {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .animate-slide-in {
        animation: slide-in 0.4s ease-out;
    }
    
    .animate-slide-out {
        animation: slide-out 0.4s ease-in;
    }
    
    /* Ensure notification appears above everything */
    #logout-notification {
        position: fixed !important;
        z-index: 9999 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check for logout notification in localStorage (fallback method)
        const logoutNotification = localStorage.getItem('logout_notification');
        
        if (logoutNotification) {
            const notification = JSON.parse(logoutNotification);
            
            // Check if notification is recent (within last 15 seconds)
            if (Date.now() - notification.timestamp < 15000) {
                showLogoutNotification(notification.title, notification.message);
            }
            
            // Remove the notification from localStorage
            localStorage.removeItem('logout_notification');
        }
        
        // Auto close existing notification after 6 seconds
        const existingNotification = document.getElementById('logout-notification');
        if (existingNotification) {
            setTimeout(function() {
                closeLogoutNotification();
            }, 6000);
        }
    });
    
    function showLogoutNotification(title, message) {
        // Don't show if notification already exists
        if (document.getElementById('logout-notification')) {
            return;
        }
        
        const notificationHtml = `
            <div id="logout-notification" class="fixed z-50 max-w-sm top-4 right-4 animate-slide-in" style="z-index: 9999;">
                <div class="px-6 py-4 text-white border border-blue-400 rounded-lg shadow-xl bg-gradient-to-r from-blue-500 to-blue-600">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="flex-1 ml-3">
                            <h4 class="text-sm font-bold">${title}</h4>
                            <p class="mt-1 text-sm opacity-90">${message}</p>
                        </div>
                        <button onclick="closeLogoutNotification()" class="flex-shrink-0 ml-4 text-white transition-colors hover:text-gray-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', notificationHtml);
        
        // Auto close after 6 seconds
        setTimeout(closeLogoutNotification, 6000);
    }
    
    function closeLogoutNotification() {
        const notification = document.getElementById('logout-notification');
        if (notification) {
            notification.classList.remove('animate-slide-in');
            notification.classList.add('animate-slide-out');
            setTimeout(() => {
                notification.remove();
            }, 400);
        }
    }
</script>