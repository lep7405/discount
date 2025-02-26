@extends('admin.layouts.admin')

@section('title_admin')
    Edit Coupon
@endsection

@section("li_breadcumb")
    <li>
        <a href="{{ route('admin.'.$databaseName.'.reports') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
            {{ $appName }}
        </a>
    </li>
    <li class="text-gray-400">/</li>
    <li>
        <a href="{{ route('admin.'.$databaseName.'.coupons') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
            Coupons
        </a>
    </li>
    <li class="text-gray-400">/</li>
@endsection

@section('main_content')
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <div x-data="{ showModal1: false, showModal2:false }" x-cloak class="container mx-auto mt-5">
        <div class="flex flex-wrap">
            <div class="w-full lg:w-2/3 px-4 mb-8">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="bg-[#027BFF] px-6 py-4 rounded-t-lg border-b">
                        <h3 class="text-xl font-semibold text-white">Edit Coupon</h3>
                    </div>
                    @if ($errors->has('error'))
                        <div class="text-red-500">
                            {{$errors->first('error')}}
                        </div>
                    @endif

                    <div class="p-6">
                        <form role="form" action="{{ route('admin.'.$databaseName.'.update_coupon', $couponData->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_db" value="{{ $databaseName }}">

                            <div class="mb-4">
                                <label for="inputCode" class="block text-gray-700 text-sm font-bold mb-2">Code</label>
                                <input
                                    type="text"
                                    id="inputCode"
                                    name="code"
                                    value="{{ $couponData->code }}"
                                    placeholder="Enter code"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-gray-700
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
               transition-colors duration-200
               @if ($couponData->times_used)
                   bg-gray-100 cursor-not-allowed opacity-75
               @else
                   bg-white hover:border-gray-400
               @endif"
                                    @if ($couponData->times_used) disabled @endif
                                >
                                @if ($errors->has('code'))
                                    <span class="text-red-500">{{ $errors->first('code') }}</span>
                                @endif
                            </div>

                            <div class="mb-4">
                                <label for="inputShop" class="block text-gray-700 text-sm font-bold mb-2">Shop</label>
                                <input
                                    type="text"
                                    id="inputShop"
                                    name="shop"
                                    value="{{ $couponData->shop }}"
                                    placeholder="Enter shop"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-gray-700
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
               transition-colors duration-200
               @if ($couponData->times_used)
                   bg-gray-100 cursor-not-allowed opacity-75
               @else
                   bg-white hover:border-gray-400
               @endif"
                                    @if ($couponData->times_used) disabled @endif
                                >
                                @if ($errors->has('shop'))
                                    <span class="text-red-500">{{ $errors->first('shop') }}</span>
                                @endif
                            </div>

                            <div class="mb-4">
                                <label for="discount_id" class="block text-gray-700 text-sm font-bold mb-2">Discount</label>
                                <select
                                    id="discount_id"
                                    name="discount_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-gray-700
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
               transition-colors duration-200
               @if ($couponData->times_used)
                   bg-gray-100 cursor-not-allowed opacity-75
               @else
                   bg-white hover:border-gray-400
               @endif"
                                    @if ($couponData->times_used) disabled @endif
                                >
                                    <option value="{{ $currentDiscount->id }}" selected>{{ $currentDiscount->name }}</option>
                                    @foreach ($discountData as $discount)
                                        <option value="{{ $discount->id }}"
                                                @if ($discount->id == $currentDiscount->id) selected @endif>
                                            {{ $discount->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('discount_id'))
                                    <span class="text-red-500">{{ $errors->first('discount_id') }}</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <button
                                    type="button"
                                    @if ($couponData->times_used) disabled @endif
                                    @click="showModal1 = true"
                                    class="bg-red-600 hover:bg-red-400 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors duration-200 ease-in-out
               @if ($couponData->times_used) opacity-50 hover:cursor-not-allowed @endif"
                                    data-toggle="modal"
                                    data-target="#exampleModal"
                                >
                                    Delete
                                </button>

                                <button
                                    type="submit"
                                    @if ($couponData->times_used) disabled @endif
                                    class="bg-blue-600 hover:bg-blue-400 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors duration-200 ease-in-out
               @if ($couponData->times_used) opacity-50 hover:cursor-not-allowed @endif"
                                >
                                    Update
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-1/3 px-4 mb-8">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="bg-[#027BFF] px-6 py-4 rounded-t-lg border-b">
                        <h3 class="text-xl font-semibold text-white">Status</h3>
                    </div>
                    <div class="bg-gray-100 px-6 py-4 rounded-t-lg border-b">

                        <ul>
                            <li><h1 @click="showModal2 = true" class="text-blue-500 hover:cursor-pointer">
                                    {{ $couponData->times_used == null ? '0' : $couponData->times_used }} times used
                                </h1></li>
                            <li><strong>{{ $couponData->status == 1 ? 'Active' : 'Disable' }}</strong></li>
                        </ul>
                    </div>

                </div>
                <div class="bg-white rounded-lg shadow-md mt-5">
                    <div class="bg-[#027BFF] px-6 py-4 rounded-t-lg border-b">
                        <h3 class="text-xl font-semibold text-white">Discount Info</h3>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <ul id="discountInfo" class="space-y-3 text-gray-700"></ul>
                    </div>
                </div>
            </div>
        </div>
        <div x-show="showModal1" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="my-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Xác nhận xóa Discount</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">Bạn có chắc chắn muốn xóa?</p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button @click="showModal1 = false" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Đóng
                        </button>
                        <button @click="showModal1 = false; document.getElementById('deleteDiscount').submit();" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div
            x-show="showModal2"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
                <form id="decrementCoupon" action="{{ route('admin.'.$databaseName.'.decrement_times_used_coupon', $couponData->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="p-6 space-y-6">
                        <!-- Modal Header -->
                        <div class="text-center space-y-2">
                            <h3 class="text-xl font-semibold text-gray-900">
                                Change Times Used
                            </h3>
                            <p class="text-sm font-medium text-gray-600">
                                Num Decrement. Max {{ $couponData->times_used ?? 0 }}
                            </p>
                        </div>

                        <!-- Input Field -->
                        <div class="space-y-2">
                            <label for="inputTimesUsed" class="block text-sm font-medium text-gray-700">
                                Num Decrement. Max {{ $couponData->times_used ?? 0 }}
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    min="0"
{{--                                    max="{{ $couponData->times_used ?? 0 }}"--}}
                                    class="block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors text-gray-900 placeholder-gray-400 text-sm"
                                    id="inputTimesUsed"
                                    name="numDecrement"
                                    value="{{ $couponData->times_used ?? 0 }}"
                                    placeholder="Enter Num Decrement"
                                >
                            </div>
                        </div>

                        <!-- Modal Buttons -->
                        <div class="flex items-center justify-end space-x-3 pt-4">
                            <!-- Cancel Button -->
                            <button
                                @click="showModal2 = false"
                                type="button"
                                class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancel
                            </button>

                            <!-- Decrement Button -->
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                                Decrement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <form id="deleteDiscount" method="POST" action="{{ route('admin.'.$databaseName.'.destroy_coupon', $couponData->id) }}">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const discountSelect = document.getElementById('discount_id');
            const discountInfo = document.getElementById('discountInfo');

            discountSelect.addEventListener('change', function() {
                const selectedId = this.value;
                if (selectedId) {
                    fetchDiscountInfo(selectedId);
                } else {
                    discountInfo.innerHTML = '<li>No discount selected</li>';
                }
            });

            // Fetch discount info based on the current discount initially
            const currentDiscountId = "{{ $currentDiscount->id }}"; // Lấy ID của discount mặc định
            if (currentDiscountId) {
                discountSelect.value = currentDiscountId; // Đảm bảo chọn đúng discount
                fetchDiscountInfo(currentDiscountId); // Gọi hàm để fetch thông tin discount
            }

            function fetchDiscountInfo(id) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const dbName = document.querySelector('input[name="_db"]').value;
                const url = `http://localhost:8000/admin/${dbName}/discounts/${id}`

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    // body: JSON.stringify({ db: dbName }) // Không cần gửi ID trong body nữa vì đã có trong URL
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data || data.error) {
                            discountInfo.innerHTML = `
            <li class="flex items-center text-red-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Error fetching discount data
            </li>`;
                            return;
                        }

                        let element = `
        <li class="flex items-center p-3 bg-blue-50 rounded-lg">
            <span class="flex items-center justify-center w-8 h-8 mr-3 text-blue-500 bg-blue-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span class="font-medium">${data.value}${data.type === 'amount' ? " USD" : "%"} discount</span>
        </li>

        <li class="flex items-center p-3 bg-green-50 rounded-lg">
            <span class="flex items-center justify-center w-8 h-8 mr-3 text-green-500 bg-green-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span class="font-medium">${data.usage_limit == 0 ? "Unlimited" : data.usage_limit + " times usage"}</span>
        </li>

        <li class="flex items-center p-3 bg-purple-50 rounded-lg">
            <span class="flex items-center justify-center w-8 h-8 mr-3 text-purple-500 bg-purple-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span class="font-medium">${data.trial_days ? data.trial_days : 0} days trial</span>
        </li>

        <li class="flex items-center p-3 bg-yellow-50 rounded-lg">
            <span class="flex items-center justify-center w-8 h-8 mr-3 text-yellow-600 bg-yellow-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <span class="font-medium">Start: ${data.started_at ? formatDate(data.started_at) : "N/A"}</span>
        </li>

        <li class="flex items-center p-3 bg-red-50 rounded-lg">
            <span class="flex items-center justify-center w-8 h-8 mr-3 text-red-500 bg-red-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <span class="font-medium">End: ${data.expired_at ? formatDate(data.expired_at) : "N/A"}</span>
        </li>
    `;
                        discountInfo.innerHTML = element;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        discountInfo.innerHTML = '<li>Failed to load discount info</li>';
                    });
            }

            function formatDate(date) {
                let parts = date.split("-");
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
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

        @if ($errors->has('decrement'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: "{{ $errors->first('decrement')}}",
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

