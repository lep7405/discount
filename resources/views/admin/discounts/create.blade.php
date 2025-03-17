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

@section('mainContent')
    <div class="max-w-2xl mx-auto">
        @if ($errors->has('error'))
            <div class="text-red-500">
                <h1>erorr</h1>
                {{ $errors->first('error') }}
            </div>
        @endif
        <form method="POST" action="{{ route('admin.'.$databaseName.'.storeDiscount') }}" class="space-y-6">
            @csrf
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <x-section-header title="General" />
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="inputName" class="block text-sm font-semibold text-gray-700">
                                Name <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input type="text"
                                   id="inputName"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Enter name"
                                   class="form-input {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}">
                            @error('name')
                            <div class="text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Detail Section --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <x-section-header title="Detail" />
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        @if ($databaseName == 'affiliate' || $databaseName == 'freegifts_new')
                            <div x-data="{ showDiscountMonth: {{ old('discount_for_x_month') === '1' || $errors->has('discount_month') ? 'true' : 'false' }} }" class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col">
                                    <label class="mb-2 text-sm font-semibold text-gray-700">
                                        Giảm giá cho X tháng đầu tiên
                                    </label>
                                    <select name="discount_for_x_month"
                                            x-on:change="showDiscountMonth = $event.target.value === '1'"
                                            class="form-input {{ $errors->has('discount_for_x_month') ? 'border-red-500' : 'border-gray-300' }}">
                                        <option value="0">Không</option>
                                        <option value="1" {{ old('discount_for_x_month') === '1' ? 'selected' : '' }}>Có</option>
                                    </select>
                                </div>
                                <div x-show="showDiscountMonth" x-cloak class="flex flex-col">
                                    <label class="mb-2 text-sm font-semibold text-gray-700">Số tháng</label>
                                    <input type="number"
                                           min="0"
                                           name="discount_month"
                                           value="{{ old('discount_month') }}"
                                           placeholder="Nhập giá trị"
                                           class="form-input {{ $errors->has('discount_month') ? 'border-red-500' : 'border-gray-300' }}">
                                    @error('discount_month')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>


                        @endif
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Type <span class="text-red-400">*</span> </label>
                                    <select name="type"
                                            class="form-input {{ $errors->has('type') ? 'border-red-500' : 'border-gray-300' }}">
                                        <option value="percentage">Percentage</option>
                                        <option value="amount">Amount</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="value" class="block text-sm font-semibold text-gray-700">
                                        Value
                                    </label>
                                    <input type="number"
                                           min="0"
                                           step="0.01"
                                           name="value"
                                           value="{{ old('value') }}"
                                           placeholder="Enter value"
                                           class="form-input {{ $errors->has('value') ? 'border-red-500' : 'border-gray-300' }}">
                                    @error('value')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>


                            <div>
                                <label for="trial_days" class="block text-sm font-semibold text-gray-700">
                                    Trial days
                                </label>
                                <input type="number"
                                       name="trial_days"
                                       min="0"
                                       value="{{ old('trial_days', 0) }}"
                                       placeholder="Enter trial days"
                                       class="form-input {{ $errors->has('trial_days') ? 'border-red-500' : 'border-gray-300' }}">
                                @error('trial_days')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                    </div>
                </div>
            </div>

            {{-- Time Section --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <x-section-header title="Time" />
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="usage_limit" class="block text-sm font-semibold text-gray-700">
                                Usage limit
                            </label>
                            <input type="number"
                                   name="usage_limit"
                                   min="0"
                                   value="{{ old('usage_limit') }}"
                                   placeholder="Enter usage limit"
                                   class="form-input {{ $errors->has('usage_limit') ? 'border-red-500' : 'border-gray-300' }}">
                            @error('usage_limit')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>


                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="started_at" class="block text-sm font-medium text-gray-700">Start</label>
                                <input type="date"
                                       name="started_at"
                                       value="{{ old('started_at') }}"
                                       class="form-input {{ $errors->has('started_at') ? 'border-red-500' : 'border-gray-300' }}">
                            </div>
                            <div>
                                <label for="expired_at" class="block text-sm font-medium text-gray-700">End</label>
                                <input type="date"
                                       name="expired_at"
                                       value="{{ old('expired_at') }}"
                                       class="form-input {{ $errors->has('expired_at') ? 'border-red-500' : 'border-gray-300' }}">
                                @error('expired_at')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
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

