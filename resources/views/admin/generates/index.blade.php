@extends('admin.layouts.admin')

@section('title_admin')
    Generates
@endsection

@section('title_admin_breadcumb')
    {{ 'Generate Coupon' }}
@endsection

@section('mainContent')
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <!-- Card Header -->
        <div class="border-b border-gray-100 px-6 py-5 flex justify-between items-center bg-gradient-to-r from-white to-gray-50">
            <h2 class="text-2xl font-semibold text-gray-800 tracking-tight">Generate Coupon URL</h2>
            <a href="{{ route('admin.createGenerate') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 hover:shadow-md">
                <i class="fas fa-plus mr-2"></i>
                Add New
            </a>
        </div>

        <!-- Table Controls -->
        <div class="px-6 py-4 flex justify-between items-center border-b border-gray-100 bg-gray-50/50">
            <form id="entries-form" method="GET" action="{{ url()->current() }}" class="flex items-center">
                <label class="text-sm font-medium text-gray-600">Show</label>
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <select id="entries-select" name="perPage"
                        class="mx-2 appearance-none bg-white border-2 border-gray-200 rounded-lg text-sm px-3 py-1.5 pr-8 hover:border-blue-500 transition-colors duration-200 bg-no-repeat bg-[length:1.5em_1.5em] bg-[right_0.5rem_center] bg-[url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e')]"
                        onchange="this.form.submit()">
                    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                    <option value="-1" {{ $perPage == -1 ? 'selected' : '' }}>All</option>
                </select>
                <label class="text-sm font-medium text-gray-600">entries</label>
            </form>
            <div class="flex items-center">
                <form id="search-form" method="GET" action="{{ url()->current() }}" class="flex items-center">
                    <input type="hidden" name="perPage" value="{{ $perPage }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <label for="table-search" class="text-sm font-medium text-gray-600 mr-2">Search:</label>
                    <input type="search"
                           name="search"
                           id="table-search"
                           value="{{ $search }}"
                           class="min-w-[200px] border-2 border-gray-200 rounded-lg text-sm px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                           placeholder="Search...">
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="p-6">
            <div class="max-h-[70vh] overflow-y-auto relative scrollbar-hide">
                <table id="data" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Id</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">App Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Discount</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Conditions</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Expired</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Url</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <div class="flex items-center space-x-2">
                                <span>Status</span>
                                <div class="relative inline-block text-left">
                                    <div>
                                        <button type="button"
                                                onclick="toggleDropdown()"
                                                class="inline-flex justify-center items-center px-3 py-1 text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
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
                    @if (count($generateData) > 0)
                        @foreach ($generateData as $item)
                            <tr class="hover:bg-blue-50/50 transition-colors duration-150 hover:cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('admin.editGenerate',$item->id) }}"
                                       class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">
                                        {{ $item->id }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 break-words whitespace-normal max-w-[16rem]">
                                    <a href="{{ route('admin.' . $item->db_name . '.reports') }}"
                                       class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">
                                        {{ $item->app_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 break-words whitespace-normal max-w-[16rem]">
                                    <a href="{{ route('admin.' . $item->db_name . '.editDiscount',$item->discount_id) }}"
                                       class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">
                                        {{ $item->discount_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 break-words whitespace-normal max-w-[16rem]">
                                    @if (is_array($item->conditions) && !empty($item->conditions))
                                        {{ json_encode($item->conditions) }}
                                    @else
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $item->expired ? 'Discount Expired!' : 'After '.$item->expired_range. " days" }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 break-words whitespace-normal max-w-[16rem]">
                                    {{ $item->app_url }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $item->status == '1' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $item->status == "1" ? 'Active' : 'Disable' }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center text-gray-500 py-4">No data found</td>
                        </tr>
                    @endif
                    </tbody>
                </table>

                <!-- Pagination Info and Links -->
                <div id="generate-pagination-section" class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <!-- Showing entries info -->
                        <div class="text-sm text-gray-600 font-medium">
                            Showing {{ ($currentPage - 1) * $perPage + 1 }} to {{ min($currentPage * $perPage, $totalItem) }} of {{ $totalItem }} entries
                        </div>

                        <!-- Pagination controls -->
                        <div class="flex items-center space-x-1">
                            <!-- First Page -->
                            @if ($currentPage > 1)
                                <a href="?page=1&perPage={{ $perPage }}&search={{ $search }}&status={{ $status }}"
                                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            @else
                                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </span>
                            @endif

                            <!-- Previous Page -->
                            @if ($currentPage > 1)
                                <a href="?page={{ $currentPage - 1 }}&perPage={{ $perPage }}&search={{ $search }}&status={{ $status }}"
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
                                {{ $currentPage }}/{{ $totalPages }}
                            </span>

                            <!-- Next Page -->
                            @if ($currentPage < $totalPages)
                                <a href="?page={{ $currentPage + 1 }}&perPage={{ $perPage }}&search={{ $search }}&status={{ $status }}"
                                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all duration-200">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            @else
                                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-50 border border-gray-200 rounded-md cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </span>
                            @endif

                            <!-- Last Page -->
                            @if ($currentPage < $totalPages)
                                <a href="?page={{ $totalPages }}&perPage={{ $perPage }}&search={{ $search }}&status={{ $status }}"
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
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="perPage" value="{{ $perPage }}">
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const paginationSection = document.getElementById('generate-pagination-section');
            const perPageSelect = document.getElementById('entries-select');

            // Check if we need to scroll (when page or perPage changes)
            if ((urlParams.has('page') || urlParams.has('perPage') || urlParams.has('search') || urlParams.has('status'))
                && paginationSection && perPageSelect.value !== '-1') {
                setTimeout(() => {
                    // Scroll to the table section
                    document.getElementById('data').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                        inline: 'nearest'
                    });
                }, 100);
            }
        });

        function toggleDropdown() {
            const dropdown = document.getElementById('statusDropdown');
            dropdown.classList.toggle('hidden');

            // Close dropdown when clicking outside
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

        let searchTimeout;
        document.getElementById("table-search").addEventListener("input", function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById("search-form").submit();
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
