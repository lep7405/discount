@extends('admin.layouts.admin')

@section('title_admin')
    Dashboard
@endsection

@section('main_content')
    <div class="p-6 space-y-6">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Discounts Card --}}
            <div class="bg-[#17a2b8] rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 relative">
                    <div class="relative z-10">
                        <h3 class="text-4xl font-bold text-white mb-2">{{ count($discountData) }}</h3>
                        <p class="text-white/90 text-lg">Discounts</p>
                    </div>
                    <div class="absolute top-6 right-6 text-white/30">
                        <i class="fa fa-money text-5xl"></i>
                    </div>
                </div>
            </div>

            {{-- Coupons Card --}}
            <div class="bg-[#dc3545] rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 relative">
                    <div class="relative z-10">
                        <h3 class="text-4xl font-bold text-white mb-2">{{ count($couponData) }}</h3>
                        <p class="text-white/90 text-lg">Coupons</p>
                    </div>
                    <div class="absolute top-6 right-6 text-white/30">
                        <i class="fa fa-gift text-5xl"></i>
                    </div>
                </div>
            </div>

            {{-- Discounts Used Card --}}
            <div class="bg-[#ffc107] rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 relative">
                    <div class="relative z-10">
                        <h3 class="text-4xl font-bold text-white mb-2">{{ $count_discount_used }}</h3>
                        <p class="text-white/90 text-lg">Discounts Used</p>
                    </div>
                    <div class="absolute top-6 right-6 text-white/30">
                        <i class="fa fa-check-square-o text-5xl"></i>
                    </div>
                </div>
            </div>

            {{-- Coupons Used Card --}}
            <div class="bg-[#28a745] rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 relative">
                    <div class="relative z-10">
                        <h3 class="text-4xl font-bold text-white mb-2">{{ $count_coupon_used }}</h3>
                        <p class="text-white/90 text-lg">Coupons Used</p>
                    </div>
                    <div class="absolute top-6 right-6 text-white/30">
                        <i class="fa fa-ticket text-5xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Apps Table --}}
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="border-b border-gray-200">
                <div class="p-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Apps</h3>
                    <button onclick="toggleTable()" class="text-gray-500 hover:text-gray-700 p-2 rounded-md">
                        <i class="fa fa-chevron-up" id="tableToggleIcon"></i>
                    </button>
                </div>
            </div>
            <div id="appsTableContent" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            App Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Num Discounts
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Num Coupons
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Used Coupons
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($dashboard_apps as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item['app_name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.'.$item['db'].'.discounts') }}"
                                   class="text-blue-500 hover:text-blue-700 hover:underline">
                                    {{ $item['count_discount'] }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item['count_coupon'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item['count_coupon_used'] }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add this script section at the bottom of your blade file --}}
    @push('scripts')
        <script>
            function toggleTable() {
                const content = document.getElementById('appsTableContent');
                const icon = document.getElementById('tableToggleIcon');

                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    content.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
        </script>
    @endpush
@endsection
