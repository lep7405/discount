<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secomus | Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#E9ECEF] flex items-center justify-center min-h-screen font-['Inter']">
<div class="w-full max-w-md p-6">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-semibold text-gray-700 mb-2">Secomus Register</h1>
            <p class="text-gray-600">Register a new account</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-6">
            @csrf

            <div class="space-y-1">
                <div class="relative">
                    <input type="text"
                           id="name"
                           name="name"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                           value="{{ old('name') }}"
                           placeholder="Full name">
                    <i class="fas fa-user text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                </div>
            </div>
            @if ($errors->has('name'))
                <span class="text-red-500">{{ $errors->first('name') }}</span>
            @endif

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
                            onclick="togglePassword('password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye-icon-password" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            @if ($errors->has('password'))
                <span class="text-red-500">{{ $errors->first('password') }}</span>
            @endif

            <div class="space-y-1">
                <div class="relative">
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="w-full pl-10 pr-12 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                           placeholder="Confirm password">
                    <i class="fas fa-lock text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                    <button type="button"
                            onclick="togglePassword('password_confirmation')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye-icon-password_confirmation" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                Register
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-blue-500 hover:text-blue-600 text-sm">I already have an account</a>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(`eye-icon-${inputId}`);

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
