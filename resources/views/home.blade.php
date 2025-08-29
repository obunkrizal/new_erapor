<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Erapor - {{ $sekolah->nama_sekolah }}</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/favicon_paud.png') }}" rel="icon">
    <link href="{{ asset('assets/img/apple-touch-icon_paud.png') }}" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

    <!-- Add this to your JavaScript code -->
    <script>
        window.addEventListener("load", () => {
            clock();

            function clock() {
                const today = new Date();

                // get time components
                const hours = today.getHours();
                const minutes = today.getMinutes();
                const seconds = today.getSeconds();

                //add '0' to hour, minute & second when they are less 10
                const hour = hours < 10 ? "0" + hours : hours;
                const minute = minutes < 10 ? "0" + minutes : minutes;
                const second = seconds < 10 ? "0" + seconds : seconds;

                //make clock a 12-hour time clock
                const hourTime = hour > 12 ? hour - 12 : hour;

                // if (hour === 0) {
                //   hour = 12;
                // }
                //assigning 'am' or 'pm' to indicate time of the day
                const ampm = hour < 12 ? "AM" : "PM";

                // get date components
                const month = today.getMonth();
                const year = today.getFullYear();
                const day = today.getDate();

                //declaring a list of all months in  a year
                const monthList = [
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December"
                ];

                //get current date and time
                const date = monthList[month] + " " + day + ", " + year;
                const time = hourTime + ":" + minute + ":" + second + ampm;

                //combine current date and time
                const dateTime = date + " - " + time;

                //print current date and time to the DOM
                document.getElementById("date-time").innerHTML = dateTime;
                setTimeout(clock, 1000);
            }
        });
    </script>

    <!-- Custom CSS for notifications -->
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Oswald:wght@300;400&display=swap");

        html {
            font-size: 10px;
            /*62.5% is equal to 10px in most browsers; to make it easier to calculate REM units.*/
        }

        body-clock {
            text-align: center;
            font-family: "Oswald", sans-serif;
            font-weight: 200;
            font-size: 20pt;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #faedcd;
            height: 100vh;
        }

        #clock {
            max-width: 600px;
        }

        /* for smaller screens below 700px */
        @media only screen and (max-width: 700px) {
            body {
                font-size: 20pt;
            }
        }

        /*for smaller screens below 300px*/
        @media only screen and (max-width: 300px) {
            body {
                font-size: 20pt;
            }
        }

        .notification-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            z-index: 9998;
            opacity: 0;
            transition: all 0.4s ease;
            pointer-events: none;
        }

        .notification-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        .notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.7);
            z-index: 9999;
            min-width: 420px;
            max-width: 500px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            font-family: 'Inter', sans-serif;
        }

        .notification.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .notification-header {
            padding: 25px 30px 20px;
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .notification-header.success {
            background: linear-gradient(135deg, #65d1d1 0%, #3878ef 100%);
        }

        .notification-header.error {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        }

        .notification-header.warning {
            background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
        }

        .notification-header.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .notification-icon-wrapper {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .notification-icon {
            font-size: 28px;
            color: white;
        }

        .notification-title {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .notification-body {
            padding: 25px 30px 30px;
            text-align: center;
            background: #ffffff;
        }

        .notification-message {
            font-size: 16px;
            line-height: 1.6;
            color: #4a5568;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .notification-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .notification-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .notification-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .notification-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .notification-btn.secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .notification-btn.secondary:hover {
            background: #edf2f7;
            transform: translateY(-1px);
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .notification-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 0 0 20px 20px;
            transition: width 6s linear;
        }

        .notification-progress.animate {
            width: 100%;
        }

        /* Success specific styles */
        .notification.success .notification-btn.primary {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .notification.success .notification-btn.primary:hover {
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
        }

        /* Error specific styles */
        .notification.error .notification-btn.primary {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        }

        .notification.error .notification-btn.primary:hover {
            box-shadow: 0 8px 25px rgba(255, 65, 108, 0.4);
        }

        /* Warning specific styles */
        .notification.warning .notification-btn.primary {
            background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
        }

        .notification.warning .notification-btn.primary:hover {
            box-shadow: 0 8px 25px rgba(247, 151, 30, 0.4);
        }

        /* Info specific styles */
        .notification.info .notification-btn.primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .notification.info .notification-btn.primary:hover {
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .notification {
                min-width: 320px;
                max-width: 90vw;
                margin: 0 20px;
            }

            .notification-header,
            .notification-body {
                padding: 20px;
            }

            .notification-title {
                font-size: 18px;
            }

            .notification-message {
                font-size: 14px;
            }

            .notification-actions {
                flex-direction: column;
            }

            .notification-btn {
                width: 100%;
            }
        }

        /* Animation keyframes */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .notification-icon-wrapper {
            animation: pulse 2s infinite;
        }
    </style>
</head>

<body class="index-page">

    <!-- Notification Overlay -->
    <div class="notification-overlay" id="notification-overlay" onclick="closeAllNotifications()"></div>

    <!-- Check for session messages and display notifications -->
    @if (session('success'))
        <div class="notification success" id="success-notification">
            <div class="notification-header success">
                <button class="close-btn" onclick="closeNotification('success-notification')">&times;</button>
                <div class="notification-icon-wrapper">
                    <i class="bi bi-check-circle-fill notification-icon"></i>
                </div>
                <h3 class="notification-title">Berhasil!</h3>
            </div>
            <div class="notification-body">
                <p class="notification-message">{{ session('success') }}</p>
                <div class="notification-actions">
                    <button class="notification-btn primary" onclick="closeNotification('success-notification')">
                        Mengerti
                    </button>
                </div>
            </div>
            <div class="notification-progress" id="progress-success"></div>
        </div>
    @endif

    @if (session('error'))
        <div class="notification error" id="error-notification">
            <div class="notification-header error">
                <button class="close-btn" onclick="closeNotification('error-notification')">&times;</button>
                <div class="notification-icon-wrapper">
                    <i class="bi bi-x-circle-fill notification-icon"></i>
                </div>
                <h3 class="notification-title">Terjadi Kesalahan!</h3>
            </div>
            <div class="notification-body">
                <p class="notification-message">{{ session('error') }}</p>
                <div class="notification-actions">
                    <button class="notification-btn primary" onclick="closeNotification('error-notification')">
                        Coba Lagi
                    </button>
                    <button class="notification-btn secondary" onclick="closeNotification('error-notification')">
                        Tutup
                    </button>
                </div>
            </div>
            <div class="notification-progress" id="progress-error"></div>
        </div>
    @endif

    @if (session('info'))
        <div class="notification info" id="info-notification">
            <div class="notification-header info">
                <button class="close-btn" onclick="closeNotification('info-notification')">&times;</button>
                <div class="notification-icon-wrapper">
                    <i class="bi bi-info-circle-fill notification-icon"></i>
                </div>
                <h3 class="notification-title">Informasi</h3>
            </div>
            <div class="notification-body">
                <p class="notification-message">{{ session('info') }}</p>
                <div class="notification-actions">
                    <button class="notification-btn primary" onclick="closeNotification('info-notification')">
                        Paham
                    </button>
                </div>
            </div>
            <div class="notification-progress" id="progress-info"></div>
        </div>
    @endif

    @if (session('warning'))
        <div class="notification warning" id="warning-notification">
            <div class="notification-header warning">
                <button class="close-btn" onclick="closeNotification('warning-notification')">&times;</button>
                <div class="notification-icon-wrapper">
                    <i class="bi bi-exclamation-triangle-fill notification-icon"></i>
                </div>
                <h3 class="notification-title">Peringatan!</h3>
            </div>
            <div class="notification-body">
                <p class="notification-message">{{ session('warning') }}</p>
                <div class="notification-actions">
                    <button class="notification-btn primary" onclick="closeNotification('warning-notification')">
                        Saya Mengerti
                    </button>
                </div>
            </div>
            <div class="notification-progress" id="progress-warning"></div>
        </div>
    @endif

    <!-- Check specifically for logout message -->
    @if (session('logout_success'))
        <div class="notification success" id="logout-notification">
            <div class="notification-header success">
                <button class="close-btn" onclick="closeNotification('logout-notification')">&times;</button>
                <div class="notification-icon-wrapper">
                    <i class="bi bi-door-open-fill notification-icon"></i>
                </div>
                <h3 class="notification-title">Berhasil Keluar</h3>
            </div>
            <div class="notification-body">
                <p class="notification-message">{{ session('logout_success') }}</p>
                <div class="notification-actions">
                    <button class="notification-btn primary"
                        onclick="window.location.href='{{ route('filament.admin.auth.login') }}'">
                        Login Lagi
                    </button>
                    <button class="notification-btn secondary"
                        onclick="closeNotification('logout-notification')">&times;</button>
                </div>
            </div>
            <div class="notification-progress" id="progress-logout"></div>
        </div>
    @endif

    <header id="header" style="margin-bottom: 1px" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">
            <nav id="navmenu" class="navmenu">
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
        </div>
    </header>

    <main class="main">
        <!-- Add this to your HTML body -->
        <section id="hero" class="hero section">
            <!-- Hero Section -->
            <div class="hero-bg">
                <img src="{{ asset('assets/img/hero-bg.png') }}" alt="">
            </div>
            <div class="container text-center">
                <div class="d-flex flex-column justify-content-center align-items-center">
                    <div id="clock" style="margin-bottom: 10px">
                        <h1 id="date-time"></h1>
                    </div>
                    <h1 data-aos="fade-up">Welcome to <span>{{ ucwords($sekolah->nama_sekolah) }}</span></h1>
                    <p data-aos="fade-up" data-aos-delay="100">Sistem Informasi Akademik
                        {{ ucwords($sekolah->nama_sekolah) }}</p>
                    <div class="d-flex" data-aos="fade-up" data-aos-delay="200">
                        <a href="{{ route('filament.admin.auth.login') }}" class="btn-get-started">Login</a>
                    </div>
                    <img src="{{ asset('assets/img/hero-services-img.webp') }}" class="img-fluid hero-img"
                        alt="" data-aos="zoom-out" data-aos-delay="300">
                </div>
                <div class="mb-0 credits">
                    <p style="font-size: 12pt">© <span>Copyright</span> <strong class="px-1 sitename">ObunKRizal</strong><span>All Rights
                            Reserved</span></p>
                    <div class="mt-1 credits">
                        Designed by <a href="#">BootstrapMade</a>
                    </div>
                </div>
            </div>
        </section><!-- /Hero Section -->
    </main>

    {{-- <footer id="footer" class="footer position-fixed light-background">
        <div class="credits">
            <p>© <span>Copyright</span> <strong class="px-1 sitename">ObunKRizal</strong><span>All Rights
                    Reserved</span></p>
            <div class="credits">
                Designed by <a href="#">BootstrapMade</a>
            </div>
        </div>
    </footer> --}}

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Notification JavaScript -->
    <script>
        function closeNotification(notificationId) {
            const notification = document.getElementById(notificationId);
            const overlay = document.getElementById('notification-overlay');

            if (notification) {
                notification.classList.remove('show');
                overlay.classList.remove('show');

                setTimeout(() => {
                    notification.remove();
                    // Check if there are any other notifications
                    const remainingNotifications = document.querySelectorAll('.notification');
                    if (remainingNotifications.length === 0) {
                        overlay.style.display = 'none';
                    }
                }, 400);
            }
        }

        function closeAllNotifications() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(function(notification) {
                closeNotification(notification.id);
            });
        }

        // Show notifications and auto-hide after 6 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.notification');
            const overlay = document.getElementById('notification-overlay');

            if (notifications.length > 0) {
                // Show overlay
                overlay.style.display = 'block';
                setTimeout(() => {
                    overlay.classList.add('show');
                }, 50);

                notifications.forEach(function(notification) {
                    // Show notification with animation
                    setTimeout(() => {
                        notification.classList.add('show');
                    }, 100);

                    // Auto-hide after 6 seconds
                    setTimeout(() => {
                        if (notification.classList.contains('show')) {
                            closeNotification(notification.id);
                        }
                    }, 6000);
                });
            }
        });

        // Close notification on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAllNotifications();
            }
        });
    </script>

</body>

</html>
