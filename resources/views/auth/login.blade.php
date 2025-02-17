@auth
    <script>window.location.href = "{{ route('admin.dashboard.index') }}";</script>
@else
@endauth
@if (Auth::check())
    <script>window.location.href = "{{ route('admin.dashboard.index') }}";</script>
@endif
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secomus | Log in</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-[#E9ECEF] flex items-center justify-center min-h-screen font-['Inter']">
<div class="w-full max-w-md p-6">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-semibold text-gray-700 mb-2">Secomus Login</h1>
            <p class="text-gray-600">Log in to start</p>
        </div>
        @if (session('error'))
            <div class="text-red-500 text-sm">{{ session('error') }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf

            <div class="space-y-1">
                <div class="relative">
                    <input type="email"
                           id="email"
                           name="email"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                           value="{{ old('email') }}"
                           placeholder="Email">
                    <i class="fas fa-envelope text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                </div>
            </div>
            @if ($errors->has('email'))
                <span class="text-red-500">{{ $errors->first('email') }}</span>
            @endif
            <div class="space-y-1">
                <div class="relative">
                    <input type="password"
                           id="password"
                           name="password"
                           class="w-full pl-10 pr-12 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                           placeholder="Password">
                    <i class="fas fa-lock text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                    <button type="button"
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye-icon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            @if ($errors->has('password'))
                <span class="text-red-500">{{ $errors->first('password') }}</span>
            @endif
            <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                Log In
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('register') }}" class="text-blue-500 hover:text-blue-600 text-sm">Register</a>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
</script>
</body>
</html>

