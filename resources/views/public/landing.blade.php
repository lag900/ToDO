<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="h1Fs3IbIUF8YlHVRbEjb5te9hFjnwJRPXYLMA5TjxAM" />
    <title>Todo Batucore - Professional Task Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 overflow-x-hidden">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200 h-20">
        <div class="max-w-7xl mx-auto px-6 h-full flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <span class="text-white font-black text-xl">T</span>
                </div>
                <span class="text-xl font-black text-slate-800 tracking-tight">Todo Batucore</span>
            </div>
            <a href="/login" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all">Get Started</a>
        </div>
    </nav>

    <!-- Hero -->
    <main class="pt-32 pb-20 px-6 max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-indigo-600 text-sm font-bold mb-6">
                    Now with Google Calendar Sync
                </div>
                <h1 class="text-5xl lg:text-7xl font-black text-slate-900 mb-6 leading-tight tracking-tight">
                    The smartest way to <span class="text-indigo-600">organize</span> your work.
                </h1>
                <p class="text-xl text-slate-600 mb-10 leading-relaxed max-w-xl">
                    Todo Batucore helps you manage personal tasks, team projects, and deadlines in one beautiful interface. Integrated deeply with <strong>Google Calendar</strong>.
                </p>
                <div class="flex gap-4">
                    <a href="/login" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black shadow-xl shadow-indigo-500/30 transition-all text-lg">Start Building Today</a>
                </div>
            </div>
            <div class="relative">
                <div class="absolute inset-0 bg-indigo-500/20 blur-[120px] rounded-full -z-10"></div>
                <img src="https://i.postimg.cc/jqQRtc95/thinker-(1).png" alt="App Preview" class="rounded-[2.5rem] shadow-2xl border border-slate-200 opacity-20 mx-auto">
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-12 border-t border-slate-200 mt-20">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-8">
            <span class="font-black text-slate-800">Todo Batucore</span>
            <div class="flex gap-8 text-sm font-bold text-slate-500 uppercase tracking-widest">
                <a href="/privacy-policy" class="hover:text-indigo-600">Privacy Policy</a>
                <a href="/terms-of-service" class="hover:text-indigo-600">Terms of Service</a>
                <a href="mailto:abdozero2030@gmail.com" class="hover:text-indigo-600">Contact</a>
            </div>
            <p class="text-sm text-slate-400">© 2026 Todo Batucore.</p>
        </div>
    </footer>
</body>
</html>
