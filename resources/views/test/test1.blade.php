<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
<div class="text-center">
   <h1>Test1</h1>
{{--    @if ($shop!=null){--}}
{{--        <h1>Shop : {{$shop}}</h1>--}}
{{--    }--}}
{{--        @else {--}}
{{--            <h1>No shop available</h1>--}}
{{--        }--}}
{{--    @endif--}}
    <h1>{{ $code }}</h1>
</div>
</body>
</html>
