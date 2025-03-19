<aside class="fixed top-0 left-0 h-screen z-50 transition-all duration-300 ease-in-out bg-[#2c3e50] text-[#ecf0f1] w-64 ">
    <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-between p-4 border-b border-gray-700">
            <a href="{{ route('admin.dashboard.index') }}" class="flex items-center space-x-3">
                <img src="{{ asset('images/secomus-logo.png') }}" alt="Logo" class="h-8 w-auto">
                <span class="font-semibold text-lg">Admin</span>
            </a>
            <button onclick="toggleSidebar()" class="text-white hover:text-gray-300 focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4" x-data="{ activeApp: '{{ request()->segment(2) }}' }" x-cloak>
            <ul class="space-y-2">
                <li class="px-3" x-data="{
                    open: {{ request()->segment(2) ? 'true' : 'false' }}
                }">
                    <button @click="open = !open" class="w-full flex items-center p-2 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200">
                        <div class="flex items-center justify-center w-6 h-6">
                            <i class="fas fa-th-large"></i>
                        </div>
                        <span class="ml-3">Ứng dụng</span>
                        <i class="fas fa-chevron-right ml-auto transition-transform duration-200" :class="{'rotate-90': open}"></i>
                    </button>
                    <ul x-show="open" x-cloak class="mt-2 space-y-1 px-3" x-transition>
                        @foreach ($apps as $db => $name)
                            <li class="nav-item has-treeview {{ $db }}" x-data="{
                                open: '{{ request()->segment(2) }}' === '{{ $db }}',
                                isActive: '{{ request()->segment(2) }}' === '{{ $db }}'
                            }">
                                <a @click="open = !open"
                                   class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 hover:cursor-pointer relative"
                                   :class="{ 'bg-gray-700': isActive }">
                                    <p class="ml-3 flex-1">
                                        {{ $name }}
                                    </p>
                                    <i class="fa fa-angle-right absolute right-0 mr-3 transition-transform duration-200"
                                       :class="{ 'rotate-90': open }"></i>
                                </a>
                                <ul x-show="open" x-cloak x-transition class="pl-4 space-y-1 mt-1">
                                    <li x-data="{
                                        selected: '{{ request()->segment(3) === "reports" && request()->segment(2) === $db }}'
                                    }">
                                        <a href="{{ route('admin.'.$db.'.reports') }}"
                                           class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200"
                                           :class="{ 'bg-gray-700': selected }">
                                            <i class="fa fa-circle text-gray-300 mr-3 transition-colors duration-200"
                                               :class="{ 'text-blue-500': selected }"></i>
                                            <p class="transition-colors duration-200"
                                               :class="{ 'font-semibold text-blue-500': selected }">Reports</p>
                                        </a>
                                    </li>
                                    <li x-data="{
                                        selected: '{{ request()->segment(3) === "discounts" && request()->segment(2) === $db }}'
                                    }">
                                        <a href="{{ route('admin.'.$db.'.discounts') }}"
                                           class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200"
                                           :class="{ 'bg-gray-700': selected }">
                                            <i class="fa fa-circle text-gray-300 mr-3 transition-colors duration-200"
                                               :class="{ 'text-blue-500': selected }"></i>
                                            <p class="transition-colors duration-200"
                                               :class="{ 'font-semibold text-blue-500': selected }">Discounts</p>
                                        </a>
                                    </li>
                                    <li x-data="{
                                        selected: '{{ request()->segment(3) === "coupons" && request()->segment(2) === $db }}'
                                    }">
                                        <a href="{{ route('admin.'.$db.'.coupons') }}"
                                           class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200"
                                           :class="{ 'bg-gray-700': selected }">
                                            <i class="fa fa-circle text-gray-300 mr-3 transition-colors duration-200"
                                               :class="{ 'text-blue-500': selected }"></i>
                                            <p class="transition-colors duration-200"
                                               :class="{ 'font-semibold text-blue-500': selected }">Coupons</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </li>

                <li class="px-3">
                    <a href="{{ route('admin.indexGenerate') }}"
                       class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200"
                       :class="{ 'bg-gray-700': '{{ request()->routeIs('admin.indexGenerate') }}' }">
                        <div class="flex items-center justify-center w-6 h-6">
                            <i class="fas fa-code"></i>
                        </div>
                        <span class="ml-3">Tạo mã giảm giá</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- User Profile -->
        <div class="p-4 border-t border-gray-700 cursor-pointer" onclick="window.location='{{ route('admin.user.current') }}'">
            <div class="flex items-center">
                <img src="{{ asset('images/user.png') }}" alt="Logo" class="h-8 w-8 rounded-full">
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>

    </div>
</aside>

