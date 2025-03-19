<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Secomus') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Raleway', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div id="app" class="min-h-screen flex flex-col">
        <nav class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-3">
                <h2 class="text-center text-2xl font-semibold text-blue-600">{{ $headerMessage }}</h2>
            </div>
        </nav>

        <main class="flex-grow py-8">
            <div class="container mx-auto px-4">
                <div class="max-w-2xl mx-auto">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-gray-100 px-4 py-3 border-b">
                            <h3 class="text-lg font-semibold text-gray-800">{{ $contentMessage }}</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @if (isset($reasons))
                                <p class="text-gray-700">{{ $reasons }}</p>
                            @endif

                            @if (isset($customFail))
                                <p class="text-gray-700">{!! $customFail !!}</p>
                            @endif

                            @if (isset($extendMessage))
                                <p class="font-semibold text-gray-800">{{ $extendMessage }}</p>
                            @endif
                        </div>
                        @if (isset($couponCode))
                            <p class="font-semibold text-gray-800">Coupon code :  {{ $couponCode }}</p>
                        @endif
                        @if (isset($appUrl))
                            <div class="bg-gray-50 px-4 py-3 text-right">
                                @if (!empty($generateId) && $generateId == 28)
                                    <a href="{{ $appUrl }}" target="_blank" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded transition duration-300">Log in</a>
                                @else
                                    <a href="{{ $appUrl }}" target="_blank" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded transition duration-300">Install App</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
