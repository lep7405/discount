@extends('admin.layouts.admin')

@section("li_breadcumb")
    <li>
        <a href="{{ route('admin.'.$databaseName.'.reports') }}"
           class="text-blue-600 hover:text-blue-800 transition-colors">
            {{ $appName }}
        </a>
    </li>
    <li class="text-gray-400">/</li>
    <li>
        <a href="{{ route('admin.'.$databaseName.'.coupons') }}"
           class="text-blue-600 hover:text-blue-800 transition-colors">
            {{ 'Coupons' }}
        </a>
    </li>
@endsection

@section('mainContent')
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <!-- Card Header -->
        <div class="border-b border-gray-100 px-6 py-5 flex justify-between items-center bg-gradient-to-r from-white to-gray-50">
            <h2 class="text-2xl font-semibold text-gray-800 tracking-tight">Coupons List</h2>

            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.'.$databaseName.'.createCoupon') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Coupon
                </a>
                <button id="clear-filters-btn"
                        class="ml-4 px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-all duration-200 flex items-center">
                    <i class="fas fa-filter-slash mr-1"></i> Clear filters
                </button>
            </div>
        </div>

        <!-- Table Controls -->
        <div class="px-6 py-4 flex justify-between items-center border-b border-gray-100 bg-gray-50/50">
            <form id="coupon-entries-form" method="GET" action="{{ url()->current() }}" class="flex items-center">
                <label class="text-sm font-medium text-gray-600">Show</label>
                <input type="hidden" name="searchCoupon" value="{{ $searchCoupon }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="timeUsed" value="{{ $timeUsed }}">
                <div class="relative inline-block">
                    <select id="discountEntriesSelect" name="perPageCoupon"
                            class="mx-2 appearance-none bg-white border-2 border-gray-200 rounded-lg text-sm px-3 py-1.5 pr-8 hover:border-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            onchange="this.form.submit()">
                        <option value="5" {{ $perPageCoupon == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPageCoupon == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPageCoupon == 20 ? 'selected' : '' }}>20</option>
                        <option value="-1" {{ $perPageCoupon == -1 ? 'selected' : '' }}>All</option>
                    </select>

                    <!-- Custom Dropdown Arrow -->
                    <div class="absolute top-0 right-3 flex items-center justify-center w-8 h-full pointer-events-none">
                        <div class="absolute top-0 right-0 flex items-center justify-center h-full pr-3 pointer-events-none text-gray-500 group-hover:text-blue-500 transition-colors duration-200">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <label class="text-sm font-medium text-gray-600">entries</label>

            </form>
            <div class="flex items-center">
                <form id="coupon-search-form" method="GET" action="{{ url()->current() }}" class="flex items-center">
                    <input type="hidden" name="perPageCoupon" value="{{ $perPageCoupon }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="timeUsed" value="{{ $timeUsed }}">
                    <label for="coupon-table-search" class="text-sm font-medium text-gray-600 mr-2">Search:</label>
                    <input type="search"
                           name="searchCoupon"
                           id="coupon-table-search"
                           value="{{ $searchCoupon }}"
                           class="min-w-[200px] border-2 border-gray-200 rounded-lg text-sm px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           placeholder="Search coupons...">
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="p-6">
            <div class="max-h-[70vh] overflow-y-auto relative scrollbar-hide">
                <table id="coupon-data" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Id</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Shop</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Discount</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
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
                                         class="hidden origin-top-right absolute right-0 mt-2 w-32 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10 transform transition-all duration-200">
                                        <div class="py-1">
                                            <button onclick="submitFormTime('asc')"
                                                    class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 w-full text-left transition-colors duration-200">
                                                <span class="h-2 w-2 bg-green-500 rounded-full mr-2"></span>
                                                Ascending
                                            </button>
                                            <button onclick="submitFormTime('desc')"
                                                    class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 w-full text-left transition-colors duration-200">
                                                <span class="h-2 w-2 bg-red-500 rounded-full mr-2"></span>
                                                Descending
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
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
                                         class="hidden origin-top-right absolute right-0 mt-2 w-32 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10 transform transition-all duration-200">
                                        <div class="py-1">
                                            <button onclick="submitForm(1)"
                                                    class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 w-full text-left transition-colors duration-200">
                                                <span class="h-2 w-2 bg-green-500 rounded-full mr-2"></span>
                                                Active
                                            </button>
                                            <button onclick="submitForm(0)"
                                                    class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 w-full text-left transition-colors duration-200">
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
                    <tbody class="bg-white divide-y divide-gray-100">
                    @if (count($couponData) > 0)
                        @foreach ($couponData as $item)
                            <tr onclick="window.location='{{ route('admin.'.$databaseName.'.editCoupon', $item->id) }}';"
                                class="hover:bg-blue-50/50 transition-colors duration-150 hover:cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('admin.'.$databaseName.'.editCoupon', $item->id) }}"
                                       class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">
                                        {{ $item->id }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <a href="{{ route('admin.'.$databaseName.'.editCoupon', $item->id) }}"
                                       class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">
                                        {{ $item->code }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->shop }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('admin.'.$databaseName.'.editDiscount', $item->discount->id) }}"
                                       class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">
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
                    @else
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-4">No data found</td>
                        </tr>
                    @endif
                    </tbody>
                </table>

                <!-- Pagination Info and Links -->
                <div id="coupon-pagination-section" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <!-- Showing entries info -->
                        <div class="text-sm text-gray-600 font-medium">
                            @if ($totalItemsCoupon == $totalCoupons)
                                Showing {{ ($currentPagesCoupon - 1) * $perPageCoupon + 1 }} to {{ min($currentPagesCoupon * $perPageCoupon, $totalItemsCoupon) }} of {{ $totalItemsCoupon }} entries
                            @else
                                Showing {{ ($currentPagesCoupon - 1) * $perPageCoupon + 1 }} to {{ min($currentPagesCoupon * $perPageCoupon, $totalItemsCoupon) }} of {{ $totalItemsCoupon }} entries of {{ $totalCoupons }} total items
                            @endif
                        </div>

                        <!-- Pagination controls -->
                        <div class="flex items-center space-x-1">
                            <!-- First Page -->
                            @if ($currentPagesCoupon > 1)
                                <a href="?pageCoupon=1&perPageCoupon={{ $perPageCoupon }}&searchCoupon={{ $searchCoupon }}&status={{ $status }}&timeUsed={{ $timeUsed }}"
                                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            @else
                                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </span>
                            @endif

                            <!-- Previous Page -->
                            @if ($currentPagesCoupon > 1)
                                <a href="?pageCoupon={{ $currentPagesCoupon - 1 }}&perPageCoupon={{ $perPageCoupon }}&searchCoupon={{ $searchCoupon }}&status={{ $status }}&timeUsed={{ $timeUsed }}"
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
                                {{ $currentPagesCoupon }}/{{ $totalPagesCoupon }}
                            </span>

                            <!-- Next Page -->
                            @if ($currentPagesCoupon < $totalPagesCoupon)
                                <a href="?pageCoupon={{ $currentPagesCoupon + 1 }}&perPageCoupon={{ $perPageCoupon }}&searchCoupon={{ $searchCoupon }}&status={{ $status }}&timeUsed={{ $timeUsed }}"
                                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            @else
                                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </span>
                            @endif

                            <!-- Last Page -->
                            @if ($currentPagesCoupon < $totalPagesCoupon)
                                <a href="?pageCoupon={{ $totalPagesCoupon }}&perPageCoupon={{ $perPageCoupon }}&searchCoupon={{ $searchCoupon }}&status={{ $status }}&timeUsed={{ $timeUsed }}"
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
        <form id="status-form" method="GET" action="{{ url()->current() }}" class="hidden">
            <input type="hidden" name="status" id="status-input">
            <input type="hidden" name="searchCoupon" value="{{ $searchCoupon }}">
            <input type="hidden" name="perPageCoupon" value="{{ $perPageCoupon }}">
        </form>
        <form id="times-used-form" method="GET" action="{{ url()->current() }}" class="hidden">
            <input type="hidden" name="timeUsed" id="times-used-input">
            <input type="hidden" name="searchCoupon" value="{{ $searchCoupon }}">
            <input type="hidden" name="perPageCoupon" value="{{ $perPageCoupon }}">
        </form>
    </div>
@endsection

@push('scripts')
    <script>

        function toggleDropdownTime() {
            const dropdown = document.getElementById('timeDropdown');
            dropdown.classList.toggle('hidden');

            // Đóng dropdown khi click ra ngoài
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
        function submitForm(order) {
            document.getElementById('status-input').value = order;
            document.getElementById('status-form').submit();
        }

        let searchTimeout;
        document.getElementById("coupon-table-search").addEventListener("input", function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById("coupon-search-form").submit();
            }, 2000);
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
        @if (session('error'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: "{{ session('error') }}",
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clearFiltersBtn = document.getElementById('clear-filters-btn');

            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    // Lấy đường dẫn cơ bản không có query params
                    const baseUrl = window.location.href.split('?')[0];

                    // Chuyển hướng đến URL không có tham số
                    window.location.href = baseUrl;
                });
            }

            // Kiểm tra nếu đang có bộ lọc để hiển thị/ẩn nút
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams1.toString()) {
                clearFiltersBtn.classList.remove('opacity-50');
            } else {
                clearFiltersBtn.classList.add('opacity-50');
            }

          if (urlParams.has('pageCoupon') || urlParams.has('perPageCoupon') || urlParams.has('searchCoupon') || urlParams.has('status') || urlParams.has('timeUsed')) {
                const couponSection = document.getElementById('coupon-data');
                if (couponSection) {
                    setTimeout(() => {
                        couponSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start',
                            inline: 'nearest'
                        });
                    }, 100);
                }
            }
        });
    </script>
@endpush
