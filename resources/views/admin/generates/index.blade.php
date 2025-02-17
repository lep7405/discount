@extends('admin.layouts.admin')

@section('title_admin')
    Generates
@endsection

@section('title_admin_breadcumb')
    {{ 'Generate Coupon' }}
@endsection

@section('main_content')
    <div class="bg-white rounded-lg shadow">
        <!-- Card Header -->
        <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Generate Coupon URL</h2>
            <a href="{{ route('admin.get_new_generate') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Add New
            </a>
        </div>

        <!-- Table Controls -->
        <div class="px-6 py-4 flex flex-wrap justify-between items-center border-b border-gray-200">
            <form id="entries-form" method="GET" action="{{ url()->current() }}" class="flex items-center mb-2 sm:mb-0">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <label class="text-sm text-gray-600 mr-2">Show</label>
                <select id="entries-select" name="per_page"
                        class="border-2 border-gray-200 rounded-lg text-sm px-3 py-1 pr-8 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none"
                        style="background-image: url('data:image/svg+xml,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 fill=%27none%27 viewBox=%270 0 20 20%27%3e%3cpath stroke=%27%236b7280%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 stroke-width=%271.5%27 d=%27M6 8l4 4 4-4%27/%3e%3c/svg%3e');
                               background-position: right 0.5rem center;
                               background-repeat: no-repeat;
                               background-size: 1.5em 1.5em;"
                        onchange="this.form.submit()">
                    <option value="5" {{ $per_page == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ $per_page == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $per_page == 20 ? 'selected' : '' }}>20</option>
                    <option value="-1" {{ $per_page == -1 ? 'selected' : '' }}>All</option>
                </select>
                <label class="text-sm text-gray-600 ml-2">entries</label>
            </form>

            <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex items-center">
                <input type="hidden" name="per_page" value="{{ $per_page }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <label for="table-search" class="text-sm text-gray-600 mr-2">Search:</label>
                <input type="search"
                       name="search"
                       id="table-search"
                       value="{{ $search }}"
                       class="border-2 border-gray-200 rounded-lg text-sm px-3 py-1 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Search...">
            </form>
        </div>

        <!-- Table -->
        <div class="p-6 overflow-x-auto">
            <table id="data" class="w-full divide-y divide-gray-200 border border-black">
                <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">App Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Discount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Conditions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Expired</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Url</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
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
                @if (count($generateData) > 0)
                    @foreach ($generateData as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="{{ route('admin.get_edit_generate',$item->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $item->id }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 break-words whitespace-normal max-w-[16rem]">{{ $item->app_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 break-words whitespace-normal max-w-[16rem]">
                                <a href="{{ route('admin.' . $item->db_name . '.get_edit_discount',$item->id) }}">{{ $item->discount_name }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 break-words whitespace-normal max-w-[16rem]">{{ $item->conditions }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $item->expired ? 'Discount Expired!' : 'After '.$item->expired_range. " days" }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 break-words whitespace-normal max-w-[16rem]">{{ $item->app_url }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="{{ $item->status == '1' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $item->status == "1" ? 'Active' : 'Disable' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">No data found</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Showing entries info -->
                <div class="text-sm text-gray-600">
                    Showing {{ ($currentPage - 1) * $per_page + 1 }} to {{ min($currentPage * $per_page, $totalItem) }} of {{ $totalItem }} entries
                </div>

                <!-- Pagination controls -->
                <div class="flex items-center space-x-1">
                    <!-- First Page -->
                    @if ($currentPage > 1)
                        <a href="?page=1&per_page={{ $per_page }}&search={{ $search }}&status={{ $status }}"
                           class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0L9 10.414V13a1 1 0 11-2 0V7a1 1 0 011-1h6a1 1 0 110 2h-2.586l5.293 5.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0L9 10.414V13a1 1 0 11-2 0V7a1 1 0 011-1h6a1 1 0 110 2h-2.586l5.293 5.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
                    @endif

                    <!-- Previous Page -->
                    @if ($currentPage > 1)
                        <a href="?page={{ $currentPage - 1 }}&per_page={{ $per_page }}&search={{ $search }}&status={{ $status }}"
                           class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                    @endif

                    <!-- Current Page Indicator -->
                    <span class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md">
                {{ $currentPage }}/{{ $totalPages }}
            </span>

                    <!-- Next Page -->
                    @if ($currentPage < $totalPages)
                        <a href="?page={{ $currentPage + 1 }}&per_page={{ $per_page }}&search={{ $search }}&status={{ $status }}"
                           class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                    @endif

                    <!-- Last Page -->
                    @if ($currentPage < $totalPages)
                        <a href="?page={{ $totalPages }}&per_page={{ $per_page }}&search={{ $search }}&status={{ $status }}"
                           class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 15.707a1 1 0 001.414 0L11 10.414V13a1 1 0 102 0V7a1 1 0 00-1-1H6a1 1 0 100 2h2.586L3.293 13.293a1 1 0 000 1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 15.707a1 1 0 001.414 0L11 10.414V13a1 1 0 102 0V7a1 1 0 00-1-1H6a1 1 0 100 2h2.586L3.293 13.293a1 1 0 000 1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
                    @endif
                </div>
            </div>
        </div>

        <form id="status-form" method="GET" action="{{ url()->current() }}" class="hidden">
            <input type="hidden" name="status" id="status-input">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="per_page" value="{{ $per_page }}">
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function submitForm(status) {
            document.getElementById('status-input').value = status;
            document.getElementById('status-form').submit();
        }

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
            timer: 3000
        });
        @endif
    </script>
@endpush
@push('scripts')
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('statusDropdown');
            dropdown.classList.toggle('hidden');

            // Đóng dropdown khi click ra ngoài
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
            timer: 3000
        });
        @endif
    </script>
@endpush
