@extends('admin.layouts.admin')

@section('titleAdmin')
    Discounts
@endsection

@section("li_breadcumb")
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.reports') }}">{{ $appName }}</a></li>
    <li class=""><a href="{{ route('admin.'.$databaseName.'.discounts') }}"><span class="mr-2">/</span>{{ 'Discounts' }}</a></li>
@endsection

@section('mainContent')
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <!-- Card Header -->
        <div class="border-b border-gray-100 px-6 py-5 flex justify-between items-center bg-gradient-to-r from-white to-gray-50">
            <h2 class="text-2xl font-semibold text-gray-800 tracking-tight">Discounts List</h2>

            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.'.$databaseName.'.createDiscount') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Discount
                </a>
                <button id="clear-filters-btn"
                        class="ml-4 px-3 py-1 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-all duration-200 flex items-center">
                    <i class="fas fa-filter-slash mr-1"></i> Clear filters
                </button>
            </div>
        </div>
        <!-- Table Controls -->
        <div class="px-6 py-4 flex justify-between items-center border-b border-gray-100 bg-gray-50/50">
            @if ($errors->has('error'))
                <div class="text-red-500">
                    <h1>erorr</h1>
                    {{ $errors->first('error') }}
                </div>
            @endif
            <form id="discountEntriesForm" method="GET" action="{{ url()->current() }}" class="flex items-center">
                <label class="text-sm font-medium text-gray-600">Show</label>
                <input type="hidden" name="searchDiscount" value="{{ $searchDiscount }}">
                <input type="hidden" name="startedAt" value="{{ $startedAt }}">
                <div class="relative inline-block">
                    <select id="discountEntriesSelect" name="perPageDiscount"
                            class="mx-2 appearance-none bg-white border-2 border-gray-200 rounded-lg text-sm px-3 py-1.5 pr-8 hover:border-blue-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            onchange="this.form.submit()">
                        <option value="5" {{ $perPageDiscount == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPageDiscount == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPageDiscount == 20 ? 'selected' : '' }}>20</option>
                        <option value="-1" {{ $perPageDiscount == -1 ? 'selected' : '' }}>All</option>
                    </select>

                    <!-- Custom Dropdown Arrow -->
                    <div class="absolute top-0 right-0 flex items-center justify-center w-8 h-full pointer-events-none">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <label class="text-sm font-medium text-gray-600">entries</label>
            </form>
            <div class="flex items-center">
                <form id="discountSearchForm" method="GET" action="{{ url()->current() }}" class="flex items-center">
                    <input type="hidden" name="perPageDiscount" value="{{ $perPageDiscount }}">
                    <input type="hidden" name="startedAt" value="{{ $startedAt }}">
                    <label for="discountTableSearch" class="text-sm font-medium text-gray-600 mr-2">Search:</label>
                    <input type="search"
                           name="searchDiscount"
                           id="discountTableSearch"
                           value="{{ $searchDiscount }}"
                           class="min-w-[200px] border-2 border-gray-200 rounded-lg text-sm px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           placeholder="Search discounts...">
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="p-6">
            <div class="max-h-[70vh] overflow-y-auto relative scrollbar-hide">
                <table id="discountData" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">id</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <div class="flex items-center space-x-2">
                                <span>Start</span>
                                <div class="relative inline-block text-left">
                                    <div>
                                        <button type="button"
                                                onclick="toggleDropdown('discountStartDropdown')"
                                                class="inline-flex justify-center items-center px-3 py-1 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div id="discountStartDropdown"
                                         class="hidden origin-top-right absolute right-0 mt-2 w-32 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10 transform transition-all duration-200">
                                        <div class="py-1">
                                            <button onclick="submitDiscountForm('asc')"
                                                    class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 w-full text-left transition-colors duration-200">
                                                <span class="h-2 w-2 bg-green-500 rounded-full mr-2"></span>
                                                Ascending
                                            </button>
                                            <button onclick="submitDiscountForm('desc')"
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
                    @if (count($discountData) > 0)
                        @foreach ($discountData as $item)
                            <tr onclick="window.location='{{ route('admin.'.$databaseName.'.editDiscount', $item->id) }}';"
                                class="hover:bg-blue-50/50 transition-colors duration-150 hover:cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a
                                        class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">{{ $item->id }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <a
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

                    @else
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 py-4">No data found</td>
                        </tr>
                    @endif
                </table>

                <!-- Pagination Info and Links -->
                <div id="discountPaginationSection" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <!-- Showing entries info -->
                        <div class="text-sm text-gray-600 font-medium">
                            @if ($totalItemsDiscount == $totalItems)
                                Showing {{ ($currentPagesDiscount - 1) * $perPageDiscount + 1 }} to {{ min($currentPagesDiscount * $perPageDiscount, $totalItemsDiscount) }} of {{ $totalItemsDiscount }} entries
                            @else
                                Showing {{ ($currentPagesDiscount - 1) * $perPageDiscount + 1 }} to {{ min($currentPagesDiscount * $perPageDiscount, $totalItemsDiscount) }} of {{ $totalItemsDiscount }} entries of {{ $totalItems }} total items
                            @endif
                        </div>

                        <!-- Pagination controls -->
                        <div class="flex items-center space-x-1">
                            <!-- First Page -->
                            @if ($currentPagesDiscount > 1)
                                <a href="?pageDiscount=1&perPageDiscount={{ $perPageDiscount }}&searchDiscount={{ $searchDiscount }}&startedAt={{ $startedAt }}"
                                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            @else
                                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </span>
                            @endif

                            <!-- Previous Page -->
                            @if ($currentPagesDiscount > 1)
                                <a href="?pageDiscount={{ $currentPagesDiscount - 1 }}&perPageDiscount={{ $perPageDiscount }}&searchDiscount={{ $searchDiscount }}&startedAt={{ $startedAt }}"
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
                                {{ $currentPagesDiscount }}/{{ $totalPagesDiscount }}
                            </span>

                            <!-- Next Page -->
                            @if ($currentPagesDiscount < $totalPagesDiscount)
                                <a href="?pageDiscount={{ $currentPagesDiscount + 1 }}&perPageDiscount={{ $perPageDiscount }}&searchDiscount={{ $searchDiscount }}&startedAt={{ $startedAt }}"
                                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            @else
                                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </span>
                            @endif

                            <!-- Last Page -->
                            @if ($currentPagesDiscount < $totalPagesDiscount)
                                <a href="?pageDiscount={{ $totalPagesDiscount }}&perPageDiscount={{ $perPageDiscount }}&searchDiscount={{ $searchDiscount }}&startedAt={{ $startedAt }}"
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
        <form id="discountStartForm" method="GET" action="{{ url()->current() }}" class="hidden">
            <input type="hidden" name="startedAt" id="discountStartInput">
            <input type="hidden" name="searchDiscount" value="{{ $searchDiscount }}">
            <input type="hidden" name="perPageDiscount" value="{{ $perPageDiscount }}">
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // const paginationSection = document.getElementById('discountPaginationSection');
            // const perPageSelect = document.getElementById('discountEntriesSelect');
            //
            // if (paginationSection && perPageSelect.value !== '-1') {
            //     setTimeout(() => {
            //         paginationSection.scrollIntoView({
            //             behavior: 'smooth',
            //             block: 'center'
            //         });
            //     }, 100);
            // }

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
            if (urlParams.toString()) {
                clearFiltersBtn.classList.remove('opacity-50');
            } else {
                clearFiltersBtn.classList.add('opacity-50');
            }
        });

        function toggleDropdown() {
            const dropdown = document.getElementById('discountStartDropdown');
            dropdown.classList.toggle('hidden');

            // Đóng dropdown khi click ra ngoài
            document.addEventListener('click', function(event) {
                const dropdown = document.getElementById('discountStartDropdown');
                const button = event.target.closest('button');
                if (!button && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            });
        }
        function submitDiscountForm(order) {
            document.getElementById('discountStartInput').value = order;
            document.getElementById('discountStartForm').submit();
        }

        let searchTimeout;
        document.getElementById("discountTableSearch").addEventListener("input", function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById("discountSearchForm").submit();
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
    </script>
@endpush
