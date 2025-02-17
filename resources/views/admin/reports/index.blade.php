@extends('admin.layouts.admin')

@section('title_admin')
    Reports
@endsection
@section("li_breadcumb")
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.reports') }}">{{ $appName }}</a></li>
@endsection

@section('main_content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Discounts Card -->
        <div class="overflow-hidden rounded-lg bg-cyan-600 shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <h3 class="text-3xl font-bold text-white">{{ $count_discount }}</h3>
                        <p class="mt-1 text-white">Discounts</p>
                    </div>
                    <div class="ml-4">
                        <i class="fa fa-money text-3xl text-white/80" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coupons Card -->
        <div class="overflow-hidden rounded-lg bg-red-500 shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <h3 class="text-3xl font-bold text-white">{{ $count_coupon }}</h3>
                        <p class="mt-1 text-white">Coupons</p>
                    </div>
                    <div class="ml-4">
                        <i class="fa fa-gift text-3xl text-white/80" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discounts Used Card -->
        <div class="overflow-hidden rounded-lg bg-amber-400 shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <h3 class="text-3xl font-bold text-white">{{ $count_discount_used }}</h3>
                        <p class="mt-1 text-white">Discounts Used</p>
                    </div>
                    <div class="ml-4">
                        <i class="fa fa-check-square-o text-3xl text-white/80" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coupons Used Card -->
        <div class="overflow-hidden rounded-lg bg-green-500 shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <h3 class="text-3xl font-bold text-white">{{ $count_coupon_used }}</h3>
                        <p class="mt-1 text-white">Coupons Used</p>
                    </div>
                    <div class="ml-4">
                        <i class="fa fa-ticket text-3xl text-white/80"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="mt-8 grid grid-cols-1 gap-8">
        <!-- Discounts Table -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <!-- Card Header -->
            <div class="border-b border-gray-100 px-6 py-5 flex justify-between items-center bg-gradient-to-r from-white to-gray-50">
                <h2 class="text-2xl font-semibold text-gray-800 tracking-tight">Discounts List</h2>
                <a href="{{ route('admin.'.$databaseName.'.discounts_new') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Discount
                </a>
            </div>

            <!-- Table Controls -->
            <div class="px-6 py-4 flex justify-between items-center border-b border-gray-100 bg-gray-50/50">
                <form id="entries-form" method="GET" action="{{ url()->current() }}" class="flex items-center">
                    <label class="text-sm font-medium text-gray-600">Show</label>
                    <input type="hidden" name="search_discount" value="{{ $search_discount }}">
                    <input type="hidden" name="started_at" value="{{ $started_at }}">
                    <select id="entries-select" name="per_page_discount"
                            class="mx-2 appearance-none bg-white border-2 border-gray-200 rounded-lg text-sm px-3 py-1.5 pr-8 hover:border-blue-500 transition-colors duration-200 bg-no-repeat bg-[length:1.5em_1.5em] bg-[right_0.5rem_center] bg-[url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e')]"
                            onchange="this.form.submit()">
                        <option value="5" {{ $per_page_discount == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $per_page_discount == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $per_page_discount == 20 ? 'selected' : '' }}>20</option>
                        <option value="-1" {{ $per_page_discount == -1 ? 'selected' : '' }}>All</option>
                    </select>
                    <label class="text-sm font-medium text-gray-600">entries</label>
                </form>
                <div class="flex items-center">
                    <form id="search-discount-form" class="flex items-center">
                        <input type="hidden" name="per_page_discount" value="{{ $per_page_discount }}">
                        <input type="hidden" name="started_at" value="{{ $started_at }}">
                        <label for="table-discount-search" class="text-sm font-medium text-gray-600 mr-2">Search:</label>
                        <input type="search"
                               name="search_discount"
                               id="table-discount-search"
                               value="{{ $search_discount }}"
                               class="min-w-[200px] border-2 border-gray-200 rounded-lg text-sm px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                               placeholder="Search discounts...">
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="p-6">
                <div class="max-h-[70vh] overflow-y-auto relative scrollbar-hide scrollbar-thumb-gray-400 scrollbar-track-gray-100">
                    <table id="data" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <span>Start</span>
                                    <div class="relative inline-block text-left">
                                        <div>
                                            <button type="button"
                                                    onclick="toggleDropdownTime()"
                                                    class="inline-flex justify-center items-center px-3 py-1 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div id="startDropdown"
                                             class="hidden origin-top-right absolute right-0 mt-2 w-32 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10 transform transition-all duration-200">
                                            <div class="py-1">
                                                <button onclick="submitForm('asc')"
                                                        class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 w-full text-left transition-colors duration-200">
                                                    <span class="h-2 w-2 bg-green-500 rounded-full mr-2"></span>
                                                    Ascending
                                                </button>
                                                <button onclick="submitForm('desc')"
                                                        class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 w-full text-left transition-colors duration-200">
                                                    <span class="h-2 w-2 bg-red-500 rounded-full mr-2"></span>
                                                    Descending
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">End</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($discounts as $item)
                            <tr href="{{ route('admin.'.$databaseName.'.get_edit_discount', $item->id) }}"
                                class="hover:bg-blue-50/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('admin.'.$databaseName.'.get_edit_discount', $item->id) }}"
                                       class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">{{ $item->id }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <a href="{{ route('admin.'.$databaseName.'.get_edit_discount', $item->id) }}"
                                       class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">{{ $item->name }}</a>
                                    <br>
                                    <span class="text-gray-500 text-sm">
                        {{ $item->type == 'percentage' ? $item->value.'%' : $item->value." USD" }},
                        {{ $item->usage_limit == 0 ? 'Unlimited time' : $item->usage_limit." times" }} Usage,
                        {{ $item->trial_days ?? 0 }} days trial
                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                    @if ($item->started_at)
                                        {{ date_format(date_create($item->started_at), "d-m-Y") }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                    @if ($item->expired_at)
                                        {{ date_format(date_create($item->expired_at), "d-m-Y") }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Info and Links -->
                    <div id="pagination-section" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <!-- Showing entries info -->
                            <div class="text-sm text-gray-600 font-medium">
                                Showing {{ ($current_pages_discount - 1) * $per_page_discount + 1 }} to {{ min($current_pages_discount * $per_page_discount, $total_items_discount) }} of {{ $total_items_discount }} entries
                            </div>

                            <!-- Pagination controls -->
                            <div class="flex items-center space-x-1">
                                <!-- First Page -->
                                @if ($current_pages_discount > 1)
                                    <a href="?page=1&per_page_discount={{ $per_page_discount }}&search_discount={{ $search_discount }}&started_at={{ $started_at }}"
                                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                        <i class="fas fa-angle-double-left"></i>
                    </span>
                                @endif

                                <!-- Previous Page -->
                                @if ($current_pages_discount > 1)
                                    <a href="?page={{ $current_pages_discount - 1 }}&per_page_discount={{ $per_page_discount }}&search_discount={{ $search_discount }}&started_at={{ $started_at }}"
                                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                        <i class="fas fa-angle-left"></i>
                    </span>
                                @endif

                                <!-- Current Page Indicator -->
                                <span class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md">
                    {{ $current_pages_discount }}/{{ $total_pages_discount }}
                </span>

                                <!-- Next Page -->
                                @if ($current_pages_discount < $total_pages_discount)
                                    <a href="?page={{ $current_pages_discount + 1 }}&per_page_discount={{ $per_page_discount }}&search_discount={{ $search_discount }}&started_at={{ $started_at }}"
                                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                        <i class="fas fa-angle-right"></i>
                    </span>
                                @endif

                                <!-- Last Page -->
                                @if ($current_pages_discount < $total_pages_discount)
                                    <a href="?page={{ $total_pages_discount }}&per_page_discount={{ $per_page_discount }}&search_discount={{ $search_discount }}&started_at={{ $started_at }}"
                                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                        <i class="fas fa-angle-double-right"></i>
                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form id="start-form" method="GET" action="{{ url()->current() }}" class="hidden">
                <input type="hidden" name="started_at" id="start-input">
            </form>
        </div>

        <!-- Coupons Table -->
        <div class="bg-white rounded-lg shadow">
            <!-- Card Header -->
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Coupons List</h2>
                <a href="{{ route('admin.'.$databaseName.'.coupons_new') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Coupon
                </a>
            </div>

            <!-- Table Controls -->
            <div class="px-6 py-4 flex justify-between items-center border-b border-gray-200">
                <form id="entries-form" method="GET" action="{{ url()->current() }}" class="flex items-center">
                    <label class="text-sm text-gray-600">Show</label>
                    <input type="hidden" name="search_coupon" value="{{ $search_coupon }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="time_used" value="{{ $time_used }}">
                    <select id="entries-select" name="per_page_coupon" class="mx-2 border-2 border-gray-200 rounded-lg text-sm px-3 py-1 pr-8 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none" style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27M6 8l4 4 4-4%27/%3e%3c/svg%3e'); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em;" onchange="this.form.submit()">
                        <option value="5" {{ $per_page_coupon == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $per_page_coupon == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $per_page_coupon == 20 ? 'selected' : '' }}>20</option>
                        <option value="-1" {{ $per_page_coupon == -1 ? 'selected' : '' }}>All</option>
                    </select>
                    <label class="text-sm text-gray-600">entries</label>
                </form>
                <div class="flex items-center">
                    <form id="search-form" class="flex items-center">
                        <input type="hidden" name="per_page_coupon" value="{{ $per_page_coupon }}">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="hidden" name="time_used" value="{{ $time_used }}">
                        <label for="table-search" class="text-sm text-gray-600 mr-2">Search:</label>
                        <input type="search"
                               name="search_coupon"
                               id="table-search"
                               value="{{ $search_coupon }}"
                               class="border-2 border-gray-200 rounded-lg text-sm px-3 py-1 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Search...">
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="p-6">
                <div class="max-h-[70vh] overflow-y-auto relative  border border-black">
                    <table id="data" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <span>Times used</span>
                                    <div class="relative inline-block text-left">
                                        <div>
                                            <button type="button"
                                                    onclick="toggleDropdownTime()"
                                                    class="inline-flex justify-center items-center px-3 py-1 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div id="timeDropdown"
                                             class="hidden origin-top-right absolute right-0 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10">
                                            <div class="py-1">
                                                <button onclick="submitFormTime('asc')"
                                                        class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 w-full text-left">
                                                    <span class="h-2 w-2 bg-green-500 rounded-full mr-2"></span>
                                                    Ascending
                                                </button>
                                                <button onclick="submitFormTime('desc')"
                                                        class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 w-full text-left">
                                                    <span class="h-2 w-2 bg-red-500 rounded-full mr-2"></span>
                                                    Descending
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <span>Status</span>
                                    <div class="relative inline-block text-left">
                                        <div>
                                            <button type="button"
                                                    onclick="toggleDropdown()"
                                                    class="inline-flex justify-center items-center px-3 py-1 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div id="statusDropdown"
                                             class="hidden origin-top-right absolute right-0 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10">
                                            <div class="py-1">
                                                <button onclick="submitForm(1)"
                                                        class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 w-full text-left">
                                                    <span class="h-2 w-2 bg-green-500 rounded-full mr-2"></span>
                                                    Active
                                                </button>
                                                <button onclick="submitForm(0)"
                                                        class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 w-full text-left">
                                                    <span class="h-2 w-2 bg-red-500 rounded-full mr-2"></span>
                                                    Disable
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($coupons as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('admin.' . $databaseName . '.get_edit_coupon', $item->id) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $item->id }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900" >
                                    <a href="{{ route('admin.' . $databaseName . '.get_edit_coupon', $item->id) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $item->code }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->shop }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" >
                                    <a href="{{ route('admin.'.$databaseName.'.get_edit_discount',$item->discount->id) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $item->discount->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->times_used }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $item->status == '1' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $item->status == "1" ? 'Active' : 'Disable' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Info and Links -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <!-- Showing entries info -->
                            <div class="text-sm text-gray-600">
                                Showing {{ ($current_pages_coupon - 1) * $per_page_coupon + 1 }} to {{ min($current_pages_coupon * $per_page_coupon, $total_items_coupon) }} of {{ $total_items_coupon }} entries
                            </div>

                            <!-- Pagination controls -->
                            <div class="flex items-center space-x-1">
                                <!-- First Page -->
                                @if ($current_pages_coupon > 1)
                                    <a href="?page=1&per_page_coupon={{ $per_page_coupon }}&search_coupon={{ $search_coupon }}&status={{ $status }}&time_used={{ $time_used }}"
                                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                        <i class="fas fa-angle-double-left"></i>
                    </span>
                                @endif

                                <!-- Previous Page -->
                                @if ($current_pages_coupon > 1)
                                    <a href="?page={{ $current_pages_coupon - 1 }}&per_page_coupon={{ $per_page_coupon }}&search_coupon={{ $search_coupon }}&status={{ $status }}&time_used={{ $time_used }}"
                                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                        <i class="fas fa-angle-left"></i>
                    </span>
                                @endif

                                <!-- Current Page Indicator -->
                                <span class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md">
                    {{ $current_pages_coupon }}/{{ $total_pages_coupon }}
                </span>

                                <!-- Next Page -->
                                @if ($current_pages_coupon < $total_pages_coupon)
                                    <a href="?page={{ $current_pages_coupon + 1 }}&per_page_coupon={{ $per_page_coupon }}&search_coupon={{ $search_coupon }}&status={{ $status }}&time_used={{ $time_used }}"
                                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                        <i class="fas fa-angle-right"></i>
                    </span>
                                @endif

                                <!-- Last Page -->
                                @if ($current_pages_coupon < $total_pages_coupon)
                                    <a href="?page={{ $total_pages_coupon }}&per_page_coupon={{ $per_page_coupon }}&search_coupon={{ $search_coupon }}&status={{ $status }}&time_used={{ $time_used }}"
                                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                        <i class="fas fa-angle-double-right"></i>
                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form id="status-form" method="GET" action="{{ url()->current() }}" class="hidden">
                <input type="hidden" name="status" id="status-input">
            </form>
            <form id="times-used-form" method="GET" action="{{ url()->current() }}" class="hidden">
                <input type="hidden" name="time_used" id="times-used-input">
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paginationSection = document.getElementById('pagination-section');
            if (paginationSection) {
                setTimeout(() => {
                    paginationSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 100);
            }
        });

        function toggleDropdown() {
            const dropdown = document.getElementById('statusDropdown');
            dropdown.classList.toggle('hidden');

            document.addEventListener('click', function(event) {
                const dropdown = document.getElementById('statusDropdown');
                const button = event.target.closest('button');
                if (!button && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        function submitForm(status) {
            document.getElementById('status-input').value = status;
            document.getElementById('status-form').submit();
        }

        function toggleDropdownTime() {
            const dropdown = document.getElementById('timeDropdown');
            dropdown.classList.toggle('hidden');

            document.addEventListener('click', function(event) {
                const dropdown = document.getElementById('timeDropdown');
                const button = event.target.closest('button');
                if (!button && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        function submitFormTime(order) {
            document.getElementById('times-used-input').value = order;
            document.getElementById('times-used-form').submit();
        }

        let searchTimeout;
        document.getElementById("table-discount-search").addEventListener("input", function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById("search-discount-form").submit();
            }, 500);
        });

        document.getElementById("table-search").addEventListener("input", function() {
            document.getElementById("search-form").submit();
        });

        @if (session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'animate__animated animate__fadeInDown'
            }
        });
        @endif
    </script>
@endpush
