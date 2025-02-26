{{-- resources/views/layouts/admin.blade.php --}}
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

    <!-- Bootstrap 5 CSS -->
{{--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">--}}
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>



    <style>
        :root {
            --sidebar-bg-expanded: #2c3e50;
            --sidebar-bg-collapsed: #34495e;
            --sidebar-text: #ecf0f1;
            --header-bg-expanded: #3498db;
            --header-bg-collapsed: #2980b9;
            --header-text: #ffffff;
            --content-bg-expanded: #ecf0f1;
            --content-bg-collapsed: #bdc3c7;
            --transition-speed: 0.3s;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 50;
            transition: all var(--transition-speed) ease-in-out;
            background-color: var(--sidebar-bg-expanded);
            color: var(--sidebar-text);
        }

        .sidebar-expanded {
            width: 16rem;
        }

        .sidebar-collapsed {
            width: 4rem;
            background-color: var(--sidebar-bg-collapsed);
        }

        .main-content {
            margin-left: 16rem;
            transition: all var(--transition-speed) ease-in-out;
            width: calc(100% - 16rem);
            background-color: var(--content-bg-expanded);
        }

        .main-content.collapsed {
            margin-left: 4rem;
            width: calc(100% - 4rem);
            background-color: var(--content-bg-collapsed);
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            transition: all var(--transition-speed) ease-in-out;
        }

        .sidebar-icon {
            width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header {
            transition: all var(--transition-speed) ease-in-out;
            background-color: var(--header-bg-expanded);
            color: var(--header-text);
        }

        .header.collapsed {
            background-color: var(--header-bg-collapsed);
        }
    </style>

    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex">
    <x-sidebar />

    <div id="content" class="main-content flex-1 flex flex-col">
        <x-header />

        <main class="flex-grow py-6">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="w-full">
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
        const header = document.querySelector('.header');

        if(sidebar.classList.contains('sidebar-expanded')) {
            sidebar.classList.remove('sidebar-expanded');
            sidebar.classList.add('sidebar-collapsed');
            content.classList.add('collapsed');
            header.classList.add('collapsed');
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            sidebar.classList.add('sidebar-expanded');
            content.classList.remove('collapsed');
            header.classList.remove('collapsed');
        }
    }
</script>
</body>
</html>
