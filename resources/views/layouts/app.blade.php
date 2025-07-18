<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>App Layout</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div class="min-h-screen bg-gray-100">
        {{ $slot }}
    </div>
    @livewireScripts

    <x-public-notification />

    <script>
        (function() {
            var timeoutInMinutes = {{ config('session.lifetime') }};
            var timeoutInMilliseconds = timeoutInMinutes * 60 * 1000;
            var logoutUrl = "{{ route('filament.admin.auth.logout') }}";

            var timer;

            function resetTimer() {
                clearTimeout(timer);
                timer = setTimeout(logoutUser, timeoutInMilliseconds);
            }

            function logoutUser() {
                alert('You have been logged out due to inactivity.');
                window.location.href = logoutUrl;
            }

            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;
            document.onclick = resetTimer;
            document.onscroll = resetTimer;
        })();
    </script>
</body>
</html>
