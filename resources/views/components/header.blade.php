<nav class="bg-white border-b flex items-center justify-between px-4 py-2">
    {{-- Left side --}}
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.dashboard.index') }}" class="hidden sm:block text-gray-600 hover:text-gray-700">
            Home
        </a>
    </div>

    {{-- Search form --}}
    <form class="flex-1 max-w-md mx-4">
        <div class="relative">
            <input
                class="w-full pl-10 pr-4 py-1.5 text-sm rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                type="search"
                placeholder="Tìm kiếm"
                aria-label="Tìm kiếm"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </form>

    {{-- Right side - Logout --}}
    <div class="flex items-center">
        <a class="text-gray-600 hover:text-gray-700" href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            {{ __('Log out') }}
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</nav>

