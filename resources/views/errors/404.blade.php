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
    <h1 class="text-6xl font-bold text-red-500 mb-4">404</h1>
    <h2 class="text-3xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
    <p class="text-xl text-gray-600 mb-8">Xin lỗi, trang bạn tìm kiếm không tồn tại hoặc dữ liệu không có.</p>
    <a href="{{ route('admin.dashboard.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
        Quay về trang chủ
    </a>
</div>
</body>
</html>
