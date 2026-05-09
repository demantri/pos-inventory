<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS') — POS Inventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    {{-- Toast Notification Container --}}
    <div id="toast-container"
        style="position:fixed;top:16px;right:16px;z-index:9999;
            display:flex;flex-direction:column;gap:8px;pointer-events:none;">
    </div>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(120%);
                opacity: 0;
            }
        }

        .toast-item {
            pointer-events: all;
            animation: slideIn 0.3s ease forwards;
        }

        .toast-item.hiding {
            animation: slideOut 0.3s ease forwards;
        }
    </style>
    @stack('styles')
</head>

<body class="overflow-hidden">

    {{-- Top bar POS --}}
    <div class="h-12 bg-gray-900 text-white flex items-center justify-between px-4 flex-shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <span class="text-sm font-semibold">POS Kasir</span>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-300">
            <span>{{ Auth::user()->name }}</span>
            <span class="text-gray-600">|</span>
            <span id="clock"></span>
        </div>
    </div>

    <div style="height: calc(100vh - 48px); overflow: hidden;">
        @yield('content')
    </div>

    @stack('scripts')
    <script>
        // Jam realtime
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent =
                now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>

</html>
