@extends('admin.layouts.admin')

@section('title_admin', 'Edit Discount')

@section("li_breadcumb")
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.reports') }}">{{ $appName }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.discounts') }}">{{ 'Discounts' }}</a></li>
@endsection

@section('title_admin_breadcumb')
    {{ 'Edit' }}
@endsection

@section('main_content')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <div x-data="{ showModal: false }" x-cloak class="container mx-auto px-4">
        <div class="flex flex-wrap -mx-4">

            <div class="w-full lg:w-2/3 px-4">
                <h1 class="text-xl font-bold">Edit Discount</h1>
                <form role="form" action="{{ route('admin.'.$databaseName.'.update_discount', $data->id) }}" method="POST" class="space-y-6">
                    @csrf
                    {{-- General Section --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-4 py-5 bg-[#027BFF] border-b border-blue-200 sm:px-6">
                            <h3 class="text-lg font-medium leading-6 text-white">General</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="inputName" class="block text-sm font-bold text-gray-700">Name</label>
                                    <input type="text"
                                           id="inputName"
                                           name="name"
                                           value="{{ $data->name }}"
                                           placeholder="Enter name"
                                           class="mt-1 block w-full py-2 px-2 rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
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
                                    <div x-data="{ showDiscountMonth: @if (isset($data->discount_month) && $data->discount_month > 0) true @else false @endif }" class="grid grid-cols-2 gap-4">
                                        <div class="flex flex-col">
                                            <label class="mb-2 text-sm font-bold text-gray-700">Discount for first X months</label>
                                            <select
                                                name="discount_for_x_month"
                                                x-on:change="showDiscountMonth = $event.target.value === '1'"
                                                class="h-10 rounded-md border-2 border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                @if ($discount_status) disabled @endif
                                            >
                                                <option value="0" @if (isset($data->discount_month) && $data->discount_month == null) selected @endif>No</option>
                                                <option value="1" @if (isset($data->discount_month) && $data->discount_month > 0) selected @endif>Yes</option>
                                            </select>
                                        </div>
                                        <div x-show="showDiscountMonth" x-cloak class="flex flex-col">
                                            <label class="mb-2 text-sm font-bold text-gray-700">Months</label>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                name="discount_month"
                                                value="@if (isset($data->discount_month)){{ $data->discount_month }}@endif"
                                                placeholder="Enter value"
                                                class="h-10 rounded-md border-2 border-gray-200 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                @if ($discount_status) disabled @endif
                                            >
                                            @error('discount_month')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700">Type</label>
                                        <select name="type"
                                                class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                @if ($discount_status) disabled @endif>
                                            <option @if ($data->type == 'percentage') selected @endif value="percentage">Percentage</option>
                                            <option @if ($data->type == 'amount') selected @endif value="amount">Amount</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700">Value</label>
                                        <input type="number"
                                               min="0"
                                               step="0.01"
                                               name="value"
                                               value="{{ $data->value }}"
                                               placeholder="Enter value"
                                               class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                               @if ($discount_status) disabled @endif>
                                        @error('value')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Trial days</label>
                                    <input type="number"
                                           name="trial_days"
                                           value="{{ $data->trial_days }}"
                                           placeholder="Enter trial days"
                                           class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                           @if ($discount_status) disabled @endif>
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
                                    <label for="inputUsage" class="block text-sm font-medium text-gray-700">Usage limit</label>
                                    <input type="number"
                                           id="inputUsage"
                                           name="usage_limit"
                                           value="{{ $data->usage_limit }}"
                                           placeholder="Enter usage limit"
                                           class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    @error('usage_limit')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="inputStarted" class="block text-sm font-medium text-gray-700">Start</label>
                                        <input type="date"
                                               id="inputStarted"
                                               name="started_at"
                                               value="{{ date('Y-m-d', strtotime($data->started_at)) }}"
                                               class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('started_at')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="inputExpired" class="block text-sm font-medium text-gray-700">End</label>
                                        <input type="date"
                                               id="inputExpired"
                                               name="expired_at"
                                               value="{{ date('Y-m-d', strtotime($data->expired_at)) }}"
                                               class="mt-1 p-2 block w-full rounded-md border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('expired_at')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
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
            <div class="w-full lg:w-1/3 px-4">
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <h3 class="text-lg font-semibold mb-4">Status</h3>
                    <ul class="list-disc list-inside">
                        <li>{{ $data->type == 'percentage' ? $data->value .'%' : $data->value." USD" }}</li>
                        <li>{{ $data->trial_days ?? 0 }} days trial</li>
                    </ul>
                </div>
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <h3 class="text-lg font-semibold mb-4">Coupon</h3>
                    <ul class="list-disc list-inside">
                        <li><a href="{{ route('admin.'.$databaseName.".show_create_coupon_in_discount", $data->id) }}" class="text-blue-600 hover:text-blue-800">Add Coupon</a></li>
                        <li><a href="{{ route('admin.'.$databaseName.".show_all_coupon_in_discount", $data->id) }}"  class="text-blue-600 hover:text-blue-800">Coupons Created</a></li>
                    </ul>
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
    <form id="deleteDiscount" method="POST" action="{{ route('admin.'.$databaseName.'.delete_discount', $data->id) }}">
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
