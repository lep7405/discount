@extends('admin.layouts.admin')

@section('title_admin')
    Create New Discount
@endsection

@section("li_breadcumb")
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.reports') }}">{{ $appName }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.discounts') }}"><span class="mr-2">/</span>{{ 'Discounts' }}</a></li>
@endsection

@section('title_admin_breadcumb')
    <span class="mr-2">/</span>{{ 'Create' }}
@endsection

@section('main_content')
    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('admin.'.$databaseName.'.store_discount') }}" class="space-y-6">
            @csrf
{{--            @if ($errors->any())--}}
{{--                <div class="text-red-500">--}}
{{--                    <ul>--}}
{{--                        @foreach ($errors->all() as $error)--}}
{{--                            <li>{{ $error }}</li>--}}
{{--                        @endforeach--}}
{{--                    </ul>--}}
{{--                </div>--}}
{{--            @endif--}}

{{--        @if (session('error'))--}}
{{--                <div class="text-red-500 text-sm">{{ session('error') }}</div>--}}
{{--            @endif--}}
{{--            @if ($errors->has('error'))--}}
{{--                <div class="text-red-500 text-sm">{{ $errors->first('error') }}</div>--}}
{{--            @endif--}}
            {{-- General Section --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 bg-[#027BFF] border-b border-blue-200 sm:px-6">
                    <h3 class="text-lg font-medium leading-6 text-white">General</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="inputName" class="block text-sm font-bold text-gray-700">Name <span class="text-red-400">*</span></label>
                            <input type="text"
                                   id="inputName"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Enter name"
                                   class="mt-1 block w-full py-2 px-2 rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('name')
                            <div class="text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Section --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 bg-[#027BFF] border-b border-blue-200 sm:px-6">
                    <h3 class="text-lg font-medium leading-6 text-white">Detail</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        @if ($databaseName == 'affiliate' || $databaseName == 'freegifts_new')
                            <div x-data="{ showDiscountMonth: false }" class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <label class="mb-2 text-sm font-bold text-gray-700">
                                        Giảm giá cho X tháng đầu tiên
                                    </label>
                                    <select
                                        name="discount_for_x_month"
                                        x-on:change="showDiscountMonth = $event.target.value === '1'"
                                        class="h-10 rounded-md border-2 border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    >
                                        <option value="0">Không</option>
                                        <option value="1">Có</option>
                                    </select>
                                </div>
                                <div x-show="showDiscountMonth" x-cloak class="flex flex-col">
                                    <label class="mb-2 text-sm font-bold text-gray-700">Số tháng</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        name="discount_month"
                                        value="{{ old('discount_month') }}"
                                        placeholder="Nhập giá trị"
                                        class="h-10 rounded-md border-2 border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    >
                                </div>
                            </div>
                        @endif



                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Type <span class="text-red-400">*</span> </label>
                                <select name="type"
                                        class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="percentage">Percentage</option>
                                    <option value="amount">Amount</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Value</label>
                                <input type="number"
                                       min="0"
                                       step="0.01"
                                       name="value"
                                       value="{{ old('value') }}"
                                       placeholder="Enter value"
                                       class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                @error('value')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700">Trial days</label>
                            <input type="number"
                                   name="trial_days"
                                   value="{{ old('trial_days', 0) }}"
                                   placeholder="Enter trial days"
                                   class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('trial_days')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Time Section --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 bg-[#027BFF] border-b border-blue-200 sm:px-6">
                    <h3 class="text-lg font-medium leading-6 text-white">Time</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Usage limit</label>
                            <input type="number"
                                   name="usage_limit"
                                   value="{{ old('usage_limit') }}"
                                   placeholder="Enter usage limit"
                                   class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('usage_limit')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start</label>
                                <input type="date"
                                       name="started_at"
                                       value="{{ old('started_at') }}"
                                       class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End</label>
                                <input type="date"
                                       name="expired_at"
                                       value="{{ old('expired_at') }}"
                                       class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Discount
                </button>
            </div>
        </form>
    </div>
@endsection

@section('admin_css')
    <style>
        .discount-for-x-months-wrapper {
            display: none;
        }
    </style>
@endsection

@section('admin_js')
    <script>
        @if ($databaseName == 'affiliate' || $databaseName == 'freegifts_new')
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.querySelector('select[name="discount_for_x_month"]');
            const wrapper = document.querySelector('.discount-for-x-months-wrapper');

            select.addEventListener('change', function() {
                wrapper.style.display = this.value == '0' ? 'none' : 'block';
            });
        });
        @endif
    </script>
@endsection

