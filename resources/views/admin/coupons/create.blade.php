@extends('admin.layouts.admin')

@section('title_admin')
    Create New Coupon
@endsection
@section("li_breadcumb")
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.reports') }}">{{ $appName }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.coupons') }}"><span class="mr-2">/</span>{{ 'Coupons' }}</a></li>
@endsection

@section('title_admin_breadcumb')
    <span class="mr-2">/</span>{{ 'Create' }}
@endsection
@section('mainContent')
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap -mx-4">
            <div class="w-full lg:w-2/3 px-4 mb-8 mt-3">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <x-section-header title="Create New Coupon" />

                    <div class="p-6">
                        <form role="form" action="{{ route('admin.'.$databaseName.'.storeCoupon') }}" method="POST">
                            @csrf
                            <input type="hidden" name="_db" value="{{ $databaseName }}">

                            @if (session()->has('message'))
                                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                                    <p>{{ session()->get('message') }}</p>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label for="inputCode" class="block text-gray-700 text-sm font-bold mb-2">Code</label>
                                <input type="text" class="form-input" id="inputCode" name="code" value="{{ old('code') }}" placeholder="Enter code">
                                @if ($errors->has('code'))
                                    <span class="text-red-500">{{ $errors->first('code') }}</span>
                                @endif
                            </div>
                            <div class="mb-4">
                                <label for="inputShop" class="block text-gray-700 text-sm font-bold mb-2">Shop</label>
                                <input type="text" class="form-input" id="inputShop" name="shop" value="{{ old('shop') }}" placeholder="Enter shop">
                                @if ($errors->has('shop'))
                                    <span class="text-red-500">{{ $errors->first('shop') }}</span>
                                @endif
                            </div>
                            <div class="space-y-2 mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Discount</label>
                                <select id="discount_id" name="discount_id" class="discount_select2 form-input" name="discount_app">
                                    <option value="">-- Select Discount --</option>
                                    @foreach ($discountData as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('discount_id'))
                                    <span class="text-red-500">{{ $errors->first('discount_id') }}</span>
                                @endif

                            </div>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mt-2">Create</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-1/3 px-4 mb-8 mt-3">
                <div class="bg-white shadow rounded-lg overflow-hidden">

                    <x-section-header title="Discount Info" />
                    <div class="p-6">
                        <ul id="discountInfo" class="list-disc list-inside text-gray-700"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const discountSelect = $('#discount_id');
            const discountInfo = document.getElementById('discountInfo');

            // discountSelect.addEventListener('change', function() {
            //     const selectedId = this.value;
            //     if (selectedId) {
            //         fetchDiscountInfo(selectedId);
            //     } else {
            //         discountInfo.innerHTML = '<li>No discount selected</li>';
            //     }
            // });
            if (!discountSelect.length) {
                console.error("Không tìm thấy phần tử có ID 'discount_id'");
                return;
            }
            // Khởi tạo Select2 với tìm kiếm
            discountSelect.select2({
                placeholder: "Search discount...",
                allowClear: false,
                width: '100%'
            });

            // Xử lý khi chọn discount
            discountSelect.on('change', function() {
                console.log("Discount changed!");
                const selectedValue = $(this).val();
                console.log("Selected Discount:", selectedValue);
                console.log("Selected Discount:", selectedValue);

                if (selectedValue) {
                    fetchDiscountInfo(selectedValue);
                } else {
                    discountInfo.innerHTML = '<li>No discount selected</li>';
                }
            });

            function fetchDiscountInfo(id) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const dbName = document.querySelector('input[name="_db"]').value;
                const url = `http://localhost:8000/admin/${dbName}/discounts/${id}`
                console.log(url);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.text();  // Nhận HTML từ server
                    })
                    .then(html => {
                        discountInfo.innerHTML = html;  // Chèn HTML vào phần tử discountInfo
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
    </script>
@endpush

