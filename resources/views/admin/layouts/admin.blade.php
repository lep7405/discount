<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <title>{{ config('app.name', 'Laravel') }} | @yield('title_admin', 'Admin')</title>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <!-- jQuery (cần cho Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sidebar: {
                            expanded: '#2c3e50',
                            collapsed: '#34495e',
                            text: '#ecf0f1'
                        },
                        header: {
                            expanded: '#3498db',
                            collapsed: '#2980b9',
                            text: '#ffffff'
                        },
                        content: {
                            expanded: '#ecf0f1',
                            collapsed: '#bdc3c7'
                        }
                    },
                    transitionDuration: {
                        '300': '300ms',
                    }
                }
            }
        }
    </script>

    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex">
    <x-sidebar />

    <div id="content" class="flex-1 flex flex-col transition-all duration-300 ease-in-out ml-64 w-[calc(100%-16rem)] bg-[#ecf0f1]" x-data="{ collapsed: false }" :class="{ 'ml-16 w-[calc(100%-4rem)] bg-[#bdc3c7]': collapsed }">
        <x-header />

        <main class="flex-grow py-6">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="w-full mb-2">
                    <nav class="bg-white/80 backdrop-blur-sm py-3 px-4 rounded-lg shadow-sm">
                        <ol class="flex items-center space-x-2 text-sm">
                            <li>
                                <a href="{{ route('admin.dashboard.index') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                                    Home
                                </a>
                            </li>
                            <li class="text-gray-400">/</li>
                            @yield("li_breadcumb")
                            <li class="text-gray-900 font-medium">@yield('title_admin_breadcumb')</li>
                        </ol>
                    </nav>
                </div>
                @yield('main_content')
            </div>
        </main>

        <x-footer />
    </div>
</div>

@stack('scripts')
<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('aside');
        const content = document.getElementById('content');

        if(sidebar.classList.contains('w-64')) {
            // Collapse sidebar
            sidebar.classList.remove('w-64', 'bg-[#2c3e50]');
            sidebar.classList.add('w-16', 'bg-[#34495e]');

            // Update content
            content.classList.remove('ml-64', 'w-[calc(100%-16rem)]', 'bg-[#ecf0f1]');
            content.classList.add('ml-16', 'w-[calc(100%-4rem)]', 'bg-[#bdc3c7]');

            // Hide text in sidebar
            const sidebarTexts = document.querySelectorAll('aside span, aside p');
            sidebarTexts.forEach(text => {
                text.classList.add('hidden');
            });

            // Hide chevron icons
            const chevrons = document.querySelectorAll('aside .fa-chevron-right, aside .fa-angle-right');
            chevrons.forEach(chevron => {
                chevron.classList.add('hidden');
            });
        } else {
            // Expand sidebar
            sidebar.classList.remove('w-16', 'bg-[#34495e]');
            sidebar.classList.add('w-64', 'bg-[#2c3e50]');

            // Update content
            content.classList.remove('ml-16', 'w-[calc(100%-4rem)]', 'bg-[#bdc3c7]');
            content.classList.add('ml-64', 'w-[calc(100%-16rem)]', 'bg-[#ecf0f1]');

            // Show text in sidebar
            const sidebarTexts = document.querySelectorAll('aside span, aside p');
            sidebarTexts.forEach(text => {
                text.classList.remove('hidden');
            });

            // Show chevron icons
            const chevrons = document.querySelectorAll('aside .fa-chevron-right, aside .fa-angle-right');
            chevrons.forEach(chevron => {
                chevron.classList.remove('hidden');
            });
        }
    }
</script>
</body>
</html>

