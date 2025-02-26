{{--@extends('admin.layouts.admin')--}}

<style>
    [x-cloak] { display: none !important; }
</style>

<aside class="sidebar bg-gray-800 text-gray-100 sidebar-expanded">
    <!-- Logo -->
    <a href="{{ route('admin.dashboard.index') }}" class="flex items-center h-16 px-6 bg-gray-900">
        <img src="" alt="Admin Logo" class="w-8 h-8">
        <span class="sidebar-text ml-3 text-xl font-semibold">Admin Secomus</span>
    </a>

    <!-- Sidebar content -->
    <div class="mt-5">
        <!-- User panel -->
        <div class="px-6 py-4 border-b border-gray-700">
            @if (Auth::check())
                <div class="flex items-center">
                    <img src="" class="w-10 h-10 rounded-full" alt="User Image">
                    <div class="sidebar-text ml-3">
                        <a href="{{ route('admin.user.current') }}" class="text-2xl font-medium hover:text-white ">
                            {{ Auth::user()->name }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Menu -->
        <nav class="mt-5" x-data="{ open: false }">
            <ul class="space-y-2">
                <li class="px-3">
                    <button @click="open = !open" class="sidebar-item w-full rounded-lg hover:bg-gray-700">
                        <div class="sidebar-icon">
                            <i class="fas fa-th-large"></i>
                        </div>
                        <span class="sidebar-text ml-3">Ứng dụng</span>
                        <i class="fas fa-chevron-right sidebar-text ml-auto transition-transform" :class="{'rotate-90': open}"></i>
                    </button>
                    <ul x-show="open" x-cloak class="mt-2 space-y-1 px-3" x-transition>
                        @foreach ($apps as $db => $name)
                            <li class="nav-item has-treeview {{ $db }}" x-data="{ open: false }">
                                <a @click="open = !open" class="nav-link flex items-center p-2 text-white rounded-lg hover:bg-gray-700 hover:cursor-pointer relative">
                                    <p class="ml-3 flex-1">
                                        {{ $name }}
                                    </p>
                                    <i class="fa fa-angle-right absolute right-0 mr-3 transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
                                </a>
                                <ul x-show="open" x-cloak x-transition class="nav nav-treeview pl-4">
                                    <li class="nav-item reports" x-data="{ selected: false }">
                                        <a href="{{ route('admin.'.$db.'.reports') }}" @click="selected = !selected" class="nav-link flex items-center p-2 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                            <i class="fa fa-circle nav-icon text-gray-300 mr-3 transition-colors duration-200" :class="{ 'text-blue-500': selected }"></i>
                                            <p class="ml-3 transition-colors duration-200" :class="{ 'font-semibold text-blue-500': selected }">Reports</p>
                                        </a>
                                    </li>
                                    <li class="nav-item discounts" x-data="{ selected: false }">
                                        <a href="{{ route('admin.'.$db.'.discounts') }}" @click="selected = !selected" class="nav-link flex items-center p-2 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                            <i class="fa fa-circle nav-icon text-gray-300 mr-3 transition-colors duration-200" :class="{ 'text-blue-500': selected }"></i>
                                            <p class="ml-3 transition-colors duration-200" :class="{ 'font-semibold text-blue-500': selected }">Discounts</p>
                                        </a>
                                    </li>
                                    <li class="nav-item coupons" x-data="{ selected: false }">
                                        <a href="{{ route('admin.'.$db.'.coupons') }}" @click="selected = !selected" class="nav-link flex items-center p-2 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                            <i class="fa fa-circle nav-icon text-gray-300 mr-3 transition-colors duration-200" :class="{ 'text-blue-500': selected }"></i>
                                            <p class="ml-3 transition-colors duration-200" :class="{ 'font-semibold text-blue-500': selected }">Coupons</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </li>

                <li class="px-3">
                    <a href="{{ route('admin.get_generate') }}" class="sidebar-item rounded-lg hover:bg-gray-700">
                        <div class="sidebar-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <span class="sidebar-text ml-3">Tạo mã giảm giá</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

