@if(session('logout_user_name'))
    <script>
        // Store logout info in localStorage
        localStorage.setItem('logout_notification', JSON.stringify({
            title: 'Logout Berhasil',
            message: 'Sampai jumpa lagi, {{ session('logout_user_name') }}! Terima kasih telah menggunakan sistem.',
            timestamp: Date.now()
        }));
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check for logout notification in localStorage
        const logoutNotification = localStorage.getItem('logout_notification');
        
        if (logoutNotification) {
            const notification = JSON.parse(logoutNotification);
            
            // Check if notification is recent (within last 10 seconds)
            if (Date.now() - notification.timestamp < 10000) {
                showLogoutNotification(notification.title, notification.message);
            }
            
            // Remove the notification from localStorage
            localStorage.removeItem('logout_notification');
        }
    });
    
    function showLogoutNotification(title, message) {
        const notificationHtml = `
            <div id="logout-notification" class="fixed z-50 max-w-sm top-4 right-4 animate-slide-in">
                <div class="px-6 py-4 text-white bg-blue-500 border-l-4 border-blue-700 rounded-lg shadow-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="flex-1 ml-3">
                            <h4 class="text-sm font-bold">${title}</h4>
                            <p class="mt-1 text-sm opacity-90">${message}</p>
                        </div>
                        <button onclick="closeLogoutNotification()" class="flex-shrink-0 ml-4 text-white hover:text-gray-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', notificationHtml);
        
        // Auto close after 5 seconds
        setTimeout(closeLogoutNotification, 5000);
    }
    
    function closeLogoutNotification() {
        const notification = document.getElementById('logout-notification');
        if (notification) {
            notification.remove();
        }
    }
</script>

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
    
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
</style>