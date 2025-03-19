@extends('admin.layouts.admin')

@section('title_admin')
    Edit Discount
@endsection

@section("li_breadcumb")
    <li>
        <a href="{{ route('admin.'.$databaseName.'.reports') }}"
           class="text-blue-600 hover:text-blue-800 transition-colors">
            {{ $appName }}
        </a>
    </li>
    <li class="text-gray-400">/</li>
    <li>
        <a href="{{ route('admin.'.$databaseName.'.discounts') }}"
           class="text-blue-600 hover:text-blue-800 transition-colors">
            {{ 'Discounts' }}
        </a>
    </li>
    <li class="text-gray-400">/</li>
@endsection

@section('title_admin_breadcumb')
    <a href="{{ route('admin.'.$databaseName.'.discounts') }}"
       class="text-blue-600 hover:text-blue-800 transition-colors">
        {{ 'Edit' }}
    </a>
@endsection

@section('mainContent')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <div x-data="{ showModal: false }" x-cloak class="container mx-auto px-4 mt-2">
        <h1 class="text-xl font-bold">Edit Discount</h1>

        <div class="flex flex-wrap">
            <div class="w-full lg:w-2/3 px-4">
                @if ($errors->has('error'))
                    <div class="text-red-500">
                        {{ $errors->first('error') }}
                    </div>
                @endif
                    @if ($errors->has('discount_month'))
                        <span class="text-red-500">{{ $errors->first('discount_month') }}</span>
                    @endif
                    <form role="form" action="{{ route('admin.'.$databaseName.'.updateDiscount', ['id' => $discountData->id]) }}" method="POST" class="space-y-6">
                    @csrf
                        @method('PUT')
                    {{-- General Section --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <x-section-header title="General" />
                        <div class="px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="inputName" class="block text-sm font-bold text-gray-700">Name  <span class="text-red-400">*</span></label>
                                    <input type="text"
                                           id="inputName"
                                           name="name"
                                           value="{{ $discountData->name }}"
                                           placeholder="Enter name"
                                           class="form-input {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}">
                                    @if ($errors->has('name'))
                                        <span class="text-red-500">{{ $errors->first('name') }}</span>
                                    @endif
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
                                    <div x-data="{ showDiscountMonth: @if (isset($discountData->discount_month) && $discountData->discount_month > 0) true @else false @endif }" class="grid grid-cols-2 gap-4">
                                        <div class="flex flex-col">
                                            <label class="mb-2 text-sm font-bold text-gray-700">Discount for first X months</label>
                                            <select
                                                name="discount_for_x_month"
                                                x-on:change="showDiscountMonth = $event.target.value === '1'"
                                                class="form-input disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:opacity-75 disabled:cursor-not-allowed"
                                                @if ($discountStatus) disabled @endif
                                            >
                                                <option value="0" @if (isset($discountData->discount_month) && $discountData->discount_month == null) selected @endif >No</option>
                                                <option value="1" @if (isset($discountData->discount_month) && $discountData->discount_month > 0) selected @endif>Yes</option>
                                            </select>
                                        </div>
                                        <div x-show="showDiscountMonth" x-cloak class="flex flex-col">
                                            <label class="mb-2 text-sm font-bold text-gray-700">Months</label>
                                            <input
                                                type="number"
                                                min="0"
                                                name="discount_month"
                                                value="@if (isset($discountData->discount_month)){{ $discountData->discount_month }}@endif"
                                                placeholder="Enter value"
                                                class="form-input {{ $errors->has('discount_month') ? 'border-red-500' : 'border-gray-300' }} disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:opacity-75 disabled:cursor-not-allowed""
                                                @if ($discountStatus) disabled @endif
                                            >
                                        </div>
                                    </div>
                                @endif
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700">Type <span class="text-red-400">*</span></label>
                                        <select name="type"
                                                class="form-input disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:opacity-75 disabled:cursor-not-allowed"
                                                @if ($discountStatus) disabled @endif
                                        >
                                            <option @if ($discountData->type == 'percentage') selected @endif value="percentage">Percentage</option>
                                            <option @if ($discountData->type == 'amount') selected @endif value="amount">Amount</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700">Value</label>
                                        <input type="number"
                                               min="0"
                                               step="0.01"
                                               name="value"
                                               value="{{ $discountData->value }}"
                                               placeholder="Enter value"
                                               class="form-input {{ $errors->has('value') ? 'border-red-500' : 'border-gray-300' }} disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:opacity-75 disabled:cursor-not-allowed"
                                               @if ($discountStatus) disabled @endif>
                                        @error('value')
                                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('value') }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Trial days</label>
                                    <input type="number"
                                           name="trial_days"
                                           value="{{ $discountData->trial_days }}"
                                           placeholder="Enter trial days"
                                           class="form-input {{ $errors->has('trial_days') ? 'border-red-500' : 'border-gray-300' }} disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:opacity-75 disabled:cursor-not-allowed"
                                           @if ($discountStatus) disabled @endif>
                                    @error('trial_days')
                                    <p class="mt-2 text-sm text-red-600">{{ $errors->first('trial_days') }}</p>
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
                                    <label for="inputUsage" class="block text-sm font-medium text-gray-700">Usage limit</label>
                                    <input type="number"
                                           id="inputUsage"
                                           name="usage_limit"
                                           value="{{ $discountData->usage_limit }}"
                                           placeholder="Enter usage limit"
                                           class="form-input {{ $errors->has('usage_limit') ? 'border-red-500' : 'border-gray-300' }}">
                                    @error('usage_limit')
                                    <p class="mt-2 text-sm text-red-600">{{  $errors->first('usage_limit') }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="inputStarted" class="block text-sm font-medium text-gray-700">Start</label>
                                        <input type="date"
                                               id="inputStarted"
                                               name="started_at"
                                               value="{{ $discountData->started_at ? date('Y-m-d', strtotime($discountData->started_at)) : '' }}"
                                               class="form-input {{ $errors->has('started_at') ? 'border-red-500' : 'border-gray-300' }}">

                                        @error('started_at')
                                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('started_at') }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="inputExpired" class="block text-sm font-medium text-gray-700">End</label>
                                        <input type="date"
                                               id="inputExpired"
                                               name="expired_at"
                                               value="{{ $discountData->expired_at ? date('Y-m-d', strtotime($discountData->expired_at)) : '' }}"
                                               class="form-input {{ $errors->has('expired_at') ? 'border-red-500' : 'border-gray-300' }}">
                                        @error('expired_at')
                                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('expired_at') }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- Submit Button --}}
                    <div class="flex items-center justify-between" >
                        <button type="button"  @click="showModal = true" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" data-toggle="modal" data-target="#exampleModal">
                            Delete
                        </button>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Update
                        </button>
                    </div>
                </form>
            </div>
            <div class="w-full lg:w-1/3 px-4 mt-6">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <x-section-header title="Status" />
                    <div class="px-4 py-5 sm:p-6">
                        <ul class="space-y-3">
                            <li class="flex items-center space-x-2">
        <span class="text-blue-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </span>
                                <span class="text-gray-700 font-medium">{{ $discountData->type == 'percentage' ? $discountData->value .'%' : $discountData->value." USD" }}</span>
                            </li>
                            <li class="flex items-center space-x-2">
        <span class="text-purple-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </span>
                                <span class="text-gray-700">{{ $discountData->trial_days ?? 0 }} days trial</span>
                            </li>
                        </ul>
                    </div>

                </div>
                <div class="bg-white shadow rounded-lg overflow-hidden mt-4">
                    <x-section-header title="Coupon" />
                    <div class="px-6 py-5 sm:p-6 bg-gray-50 rounded-lg shadow-inner">
                        <ul class="space-y-3">
                            <li class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                </svg>
                                <a href="{{ route('admin.'.$databaseName.'.createCouponInDiscount', $discountData->id) }}"
                                   class="text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out font-medium">
                                    Add Coupon
                                </a>
                            </li>
                            <li class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                </svg>
                                <a href="{{ route('admin.'.$databaseName.'.allCouponInDiscount', $discountData->id) }}"
                                   class="text-blue-600 hover:text-blue-800 transition duration-150 ease-in-out font-medium">
                                    List Coupons
                                </a>
                            </li>
                        </ul>
                    </div>


                </div>
            </div>
        </div>
        <div x-show="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="my-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Xác nhận xóa Discount</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">Bạn có chắc chắn muốn xóa?</p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button @click="showModal = false" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Đóng
                        </button>
                        <button @click="showModal = false; document.getElementById('deleteDiscount').submit();" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal -->
    <form id="deleteDiscount" method="POST" action="{{ route('admin.'.$databaseName.'.destroyDiscount', $discountData->id) }}">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.btn_delete_discount').click(function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this discount?')) {
                    $('#deleteDiscount').submit();
                }
            });

            @if ($databaseName == 'affiliate' || $databaseName == 'freegifts_new')
            var updateDiscountForXMonth = function () {
                if ($('select[name="discount_for_x_month"]').val() == 0) {
                    $('.discount-for-x-months-wrapper').hide();
                } else {
                    $('.discount-for-x-months-wrapper').show();
                }
            };

            updateDiscountForXMonth();
            $('select[name="discount_for_x_month"]').change(function() {
                updateDiscountForXMonth();
            });
            @endif
        });
    </script>
@endpush
@push('scripts')
    <script>
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

