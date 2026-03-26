<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Todo Batucore is a task management platform with Google Calendar integration to manage tasks, deadlines and schedules.">
    <meta name="google-site-verification" content="h1Fs3IbIUF8YlHVRbEjb5te9hFjnwJRPXYLMA5TjxAM" />
    <title>Todo Batucore</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Optimized Font Loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">


    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Batucore">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    console.log('SW Registered:', reg.scope);
                }).catch(err => {
                    console.warn('SW Registration Failed:', err);
                });
            });
        }

        // Handle PWA Install Prompt (Deferred)
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
            // Optionally, we'll emit a custom event for Vue to listen to
            window.dispatchEvent(new CustomEvent('pwa-can-install'));
        });

        // Check for dark mode preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>

<body class="h-full bg-slate-50 dark:bg-slate-950">
    <div id="app" class="h-full"></div>
</body>

</html>