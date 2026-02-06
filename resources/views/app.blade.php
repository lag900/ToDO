<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Todo Batucore is a task management platform with Google Calendar integration to manage tasks, deadlines and schedules.">
    <meta name="google-site-verification" content="h1Fs3IbIUF8YlHVRbEjb5te9hFjnwJRPXYLMA5TjxAM" />
    <title>Todo Batucore</title>
    <link rel="icon" type="image/png" href="https://i.postimg.cc/jqQRtc95/thinker-(1).png">
    
    <!-- Optimized Font Loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">


    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
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