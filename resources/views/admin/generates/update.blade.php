
@extends('admin.layouts.admin')

@section('title_admin')
    Generates
@endsection

@section("li_breadcumb")
    <li><a href="{{ route('admin.indexGenerate') }}">{{ 'Generate Coupon' }}</a></li>
@endsection

@section('title_admin_breadcumb')
    <span class="mr-2">/</span>{{ 'Update' }}
@endsection

@section('mainContent')
    <div x-data="{ showModal: false }" x-cloak class="container mx-auto px-4 mt-4">
        <div class="flex flex-wrap -mx-4">
            <div class="w-full px-4">
                <form role="form" action="{{ route('admin.updateGenerate', $generate->id) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_current_discount" value="{{ $generate->discount_id.'&'.$generate->app_name }}">

                    <div class="flex flex-wrap -mx-4">
                        <!-- Left Column -->
                        <div class="w-full lg:w-2/3 px-4 mb-8">
                            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                                <div class="bg-blue-600 text-white px-6 py-4">
                                    <h3 class="text-xl font-semibold">Edit Config Generate Coupon Url</h3>
                                </div>
                                <div class="p-6 space-y-6">
                                    @if ($errors->any())
                                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                                            <ul class="list-disc list-inside">
                                                @foreach ($errors->all() as $error)
                                                    <li>{!! $error !!}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Discount Selection -->
                                        <div>
                                            <label for="discount_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Discount<span class="text-red-500 ml-1">*</span>
                                            </label>
                                            <div id="discountInfo" class="mb-4 p-4 bg-gray-50 rounded-lg shadow-sm"></div>
                                            <select id="discount_id" @if (!$status_del) disabled @endif
                                            class="discount_select2 form-input disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:opacity-75 disabled:cursor-not-allowed""
                                                    name="discount_app"
                                            >
                                                @foreach ($discountData as $item)
                                                    <option value="{{ $item->id.'&'.$item->databaseName }}"
                                                            @if ($generate->discount_id == $item->id && $generate->app_name == $item->databaseName) selected @endif>
                                                        {{ $item->name.' / '.$item->appName }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>
{{--                                    <div class="space-y-2">--}}
{{--                                        <label for="discount_id" class="block text-sm font-semibold text-gray-700">Discount<span class="text-red-500 ml-1">*</span></label>--}}
{{--                                        <div id="discountInfo" class="mb-4 p-4 bg-gray-50 rounded-lg shadow-sm"></div>--}}
{{--                                        <select id="discount_id" @if (!$status_del) disabled @endif--}}
{{--                                        class="discount_select2 w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"--}}
{{--                                                name="discount_app"--}}
{{--                                        >--}}
{{--                                            @foreach ($discountData as $item)--}}
{{--                                                <option value="{{ $item->id.'&'.$item->databaseName }}"--}}
{{--                                                        @if ($generate->discount_id == $item->id && $generate->app_name == $item->databaseName) selected @endif>--}}
{{--                                                    {{ $item->name.' / '.$item->appName }}--}}
{{--                                                </option>--}}
{{--                                            @endforeach--}}
{{--                                        </select>--}}
{{--                                    </div>--}}

                                    <!-- Conditions -->
                                    <div x-data="conditionManager()" class="mb-6">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Conditions</label>
                                        <ul id="more_condition" class="mb-4 space-y-3">
                                            <template x-for="condition in conditions" :key="condition.id">
                                                <li class="p-4 border border-gray-200 rounded-md shadow-sm">
                                                    <template x-for="(app, index) in condition.apps" :key="index">
                                                        <div class="flex flex-wrap items-center gap-2 mb-3">
                                                            <span x-show="index > 0" class="text-sm font-medium text-gray-500">OR</span>
                                                            <select
                                                                x-model="app.name"
                                                                @change="updateAppValue(condition.id, index, 'name', $event.target.value)"
                                                                class="px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                            >
                                                                <option value="">Select App</option>
                                                                <option value="fg">Free gift</option>
                                                                <option value="qv">Quick View</option>
                                                                <option value="pp">Promotion Popup</option>
                                                                <option value="sl">Store Locator</option>
                                                                <option value="sp">Store Pickup</option>
                                                                <option value="bn">Banner Slider</option>
                                                                <option value="cs">Currency Switcher</option>
                                                                <option value="pl">Product Label</option>
                                                                <option value="ca">Customer Attribute</option>
                                                                <option value="sw">Spin To Win</option>
                                                            </select>
                                                            <select
                                                                x-model="app.status"
                                                                @change="updateAppValue(condition.id, index, 'status', $event.target.value)"
                                                                class="px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                            >
                                                                <option value="">Select Status</option>
                                                                <option value="notinstalledyet">Not Installed yet</option>
                                                                <option value="installed">Installed</option>
                                                                <option value="charged">Charged</option>
                                                                <option value="uninstalled">Uninstalled</option>
                                                            </select>
                                                            <button
                                                                @click="removeInCondition(condition.id,index)"
                                                                type="button"
                                                                class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-3 rounded-md text-sm transition duration-150 ease-in-out"
                                                            >
                                                                Remove
                                                            </button>
                                                            <button
                                                                x-show="index === condition.apps.length - 1"
                                                                @click="addOr(condition.id)"
                                                                type="button"
                                                                class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-3 rounded-md text-sm transition duration-150 ease-in-out"
                                                            >
                                                                OR
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <button
                                                        @click="removeCondition(condition.id)"
                                                        type="button"
                                                        class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-3 rounded-md text-sm transition duration-150 ease-in-out"
                                                    >
                                                        Remove Condition
                                                    </button>
                                                </li>
                                            </template>
                                        </ul>
                                        <button
                                            type="button"
                                            @click="addCondition"
                                            class="btnAnd bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md text-sm transition duration-150 ease-in-out"
                                        >
                                            And
                                        </button>
                                        <input type="hidden" name="condition_object" x-model="conditionJSON" class="condition_object">
                                    </div>

                                    <!-- Other Fields -->
                                    <div class="space-y-4">
                                        <div>
                                            <label for="range" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Expired Range (Days)<span class="text-red-500 ml-1">*</span>
                                            </label>
                                            <input
                                                type="number"
                                                name="expired_range"
                                                class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                value="{{ $generate->expired_range }}"
                                                id="range"
                                                placeholder="Expired Range (Days)"
                                            >
                                        </div>
                                        <div>
                                            <label for="limit" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Limit Coupons
                                            </label>
                                            <input
                                                type="number"
                                                name="limit"
                                                class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                value="{{ $generate->limit }}"
                                                id="limit"
                                                placeholder="Limit Coupons Generate"
                                            >
                                        </div>
                                        <div>
                                            <label for="inputApp" class="block text-sm font-semibold text-gray-700 mb-2">
                                                App URL<span class="text-red-500 ml-1">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                id="inputApp"
                                                name="app_url"
                                                value="{{ $generate->app_url }}"
                                                placeholder="Enter App URL"
                                            >
                                            @if ($errors->has('app_url'))
                                                <span class="text-red-500">{{ $errors->first('app_url') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Auto Generate Coupon URL Info -->
                                        <div class="text-sm text-gray-600 space-y-3">
                                            <p class="text-gray-700">
                                                <span class="font-semibold">Auto Generate Coupon URL:</span>
                                                <strong class="text-indigo-600">{{ $generate_url.'{timestamp}/{shop_id}' }}</strong>
                                            </p>
                                            <p class="text-gray-700">
                                                <span class="font-semibold">Example for date 01-01-2018, shop id = 1:</span>
                                                <strong class="text-green-600">{{ $generate_url."1514764800/1" }}</strong>
                                            </p>
                                            <p class="text-gray-700">
                                                <span class="font-semibold">PRIVATE:</span>
                                                <strong class="text-red-600">{{ $private_generate_url.'{shop_name}' }}</strong>
                                            </p>
                                        </div>

                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between">
                                    <button @click="showModal = true" type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Delete
                                    </button>
                                    <div>
                                        <button type="button" id="changeStatusButton" class="mr-2 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 btn_change_status">
                                            @if ($generate->status) Disable @else Active @endif
                                        </button>
                                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="w-full lg:w-1/3 px-4">
                            <!-- Custom Messages -->
                            <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
                                <div class="bg-blue-600 text-white px-6 py-4">
                                    <h3 class="text-lg font-semibold">Custom Messages</h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    @php
                                        if (!empty($generate->success_message)) {
                                            $arr = $generate->success_message;
                                            if (is_array($arr)) {
                                                $success_message = $arr['message'] ?? null;
                                                $extend_message = $arr['extend'] ?? null;
                                            }
                                        }

                                        if (!empty($generate->fail_message)) {
                                            $arr = $generate->fail_message;
                                            if (is_array($arr)) {
                                                $fail_message = $arr['message'] ?? null;
                                                $reason_expired = $arr['reason_expired'] ?? null;
                                                $reason_limit = $arr['reason_limit'] ?? null;
                                                $reason_condition = $arr['reason_condition'] ?? null;
                                            }
                                        }
                                    @endphp

                                    <div class="mb-4">
                                        <label for="inputHeader" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Header Message
                                        </label>
                                        <input
                                            type="text"
                                            id="inputHeader"
                                            name="header_message"
                                            placeholder="Default: Welcome to Secomapp special offer!"
                                            class="form-input w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ $generate->header_message }}"
                                        >
                                    </div>
                                    <div class="mb-4">
                                        <label for="inputSuccess" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Success Message
                                        </label>
                                        <input
                                            type="text"
                                            id="inputSuccess"
                                            name="success_message"
                                            placeholder="Default: Your offer was created! Please install app to active the offer!"
                                            class="form-input w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ $success_message ?? '' }}"
                                        >
                                    </div>
                                    <div class="mb-4">
                                        <label for="inputUsed" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Used Message
                                        </label>
                                        <input
                                            type="text"
                                            id="inputUsed"
                                            name="used_message"
                                            placeholder="Default: You have already claimed this offer!"
                                            class="form-input w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ $generate->used_message }}"
                                        >
                                    </div>
                                    <div class="mb-4">
                                        <label for="inputFail" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Fail Message
                                        </label>
                                        <input
                                            type="text"
                                            id="inputFail"
                                            name="fail_message"
                                            placeholder="Default: Offer can't be created because of following reasons:"
                                            class="form-input w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ $fail_message ?? '' }}"
                                        >
                                    </div>
                                    <div class="mb-4">
                                        <label for="inputExtend" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Extend Message
                                        </label>
                                        <input
                                            type="text"
                                            id="inputExtend"
                                            name="extend_message"
                                            placeholder="Default: Just install app then offer will be applied automatically!"
                                            class="form-input w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ $extend_message ?? '' }}"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Fail Reasons -->
                            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                                <div class="bg-blue-600 text-white px-6 py-4">
                                    <h3 class="text-lg font-semibold">Custom Fail Reasons</h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="mb-4">
                                        <label for="inputReasonTime" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Time Expired
                                        </label>
                                        <input
                                            type="text"
                                            id="inputReasonTime"
                                            name="reason_expired"
                                            placeholder="Default: This offer was expired!"
                                            class="form-input w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ $reason_expired ?? '' }}"
                                        >
                                    </div>
                                    <div class="mb-4">
                                        <label for="inputReasonLimit" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Limited Coupon
                                        </label>
                                        <input
                                            type="text"
                                            id="inputReasonLimit"
                                            name="reason_limit"
                                            placeholder="Default: Offers were reached the limited!"
                                            class="form-input w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ $reason_limit ?? '' }}"
                                        >
                                    </div>
                                    <div class="mb-4">
                                        <label for="inputReasonCondition" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Not Match Conditions
                                        </label>
                                        <input
                                            type="text"
                                            id="inputReasonCondition"
                                            name="reason_condition"
                                            placeholder="Default: Your store doesn't match app conditions!"
                                            class="form-input w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ $reason_condition ?? '' }}"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Modal delete -->
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
                                <button @click="showModal = false; document.getElementById('deleteGenerate').submit();" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                    Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <form id="deleteGenerate" method="POST" action="{{ route('admin.destroyGenerate', $generate->id) }}">
                    @csrf
                    @method('DELETE')
                </form>
                <form id="changeStatus" action="{{ route('admin.changeStatusGenerate', $generate->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                </form>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const changeStatusButton = document.getElementById('changeStatusButton');
            const changeStatusForm = document.getElementById('changeStatus');

            changeStatusButton.addEventListener('click', function(e) {
                console.log("hello1");
                // Chạy form submit khi nhấn nút
                e.preventDefault();
                changeStatusForm.submit();
            });
        });
    </script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const discountSelect = $('#discount_id');
            let discountInfo = document.getElementById('discountInfo');

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
                let selectedValue = $(this).val();
                console.log("Selected Discount:", selectedValue);

                if (selectedValue) {
                    fetchDiscountInfo(selectedValue);
                } else {
                    discountInfo.innerHTML = '<li>No discount selected</li>';
                }
            });

            // Đảm bảo discount mặc định được chọn khi trang tải
            let currentDiscountId = "{{ $generate->discount_id ?? '' }}";
            let currentDatabaseName = "{{ $generate->app_name ?? '' }}";
            if (currentDiscountId && currentDatabaseName) {
                let defaultValue = `${currentDiscountId}&${currentDatabaseName}`;
                discountSelect.val(defaultValue).trigger('change'); // Chọn discount mặc định
            }

            function fetchDiscountInfo(selectedValue) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Giải mã ID và database từ value của select
                const [id, database] = selectedValue.split("&");
                let storedDiscountId = @json($generate->discount_id); // Lấy discount_id từ Laravel Blade và chuyển thành JS
                let url = `http://localhost:8000/admin/${database}/discounts/${id || storedDiscountId}`;
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
        });
    </script>
@endpush

<script>
    function conditionManager() {
        function convertArray(inputArray) {
            return inputArray.map((condition, index) => {
                const apps = condition.split("||").map(appData => {
                    const [name, status] = appData.split("&");
                    return { name, status };
                });
                return {
                    id: index + 1,
                    apps: apps
                };
            });
        }

        console.log('hello',convertArray(@json($generate->conditions) ? JSON.parse(@json($generate->conditions)) : []));
        return {
            conditions: convertArray(@json($generate->conditions) ? JSON.parse(@json($generate->conditions)) : []),
            conditionJSON: JSON.stringify(convertArray(@json($generate->conditions) ? JSON.parse(@json($generate->conditions)) : [])),
            count_condition: @json($generate->conditions) ? JSON.parse(@json($generate->conditions)).length : 0,
            addCondition() {
                this.count_condition++;
                const newCondition = {
                    id: this.count_condition,
                    apps: [{ name: 'fg', status: 'notinstalledyet' }]
                };
                this.conditions.push(newCondition);
                this.updateConditionJSON();
            },
            addOr(conditionId) {
                const condition = this.conditions.find(c => c.id === conditionId);
                if (condition) {
                    condition.apps.push({ name: 'fg', status: 'notinstalledyet' });
                    this.updateConditionJSON();
                }
            },
            removeInCondition(conditionId, index) {
                console.log(conditionId,index);
                const condition = this.conditions.find(c => c.id === conditionId);
                if (condition) {
                    condition.apps.splice(index, 1); // Xóa phần tử tại vị trí index
                    this.updateConditionJSON();
                }
                console.log(this.conditions);
            },
            removeCondition(conditionId) {
                this.conditions = this.conditions.filter(c => c.id !== conditionId);
                this.updateConditionJSON();
            },
            updateConditionJSON() {
                this.conditionJSON = JSON.stringify(this.conditions);
            },
            updateAppValue(conditionId, appIndex, field, value) {
                const condition = this.conditions.find(c => c.id === conditionId);
                if (condition && condition.apps[appIndex]) {
                    condition.apps[appIndex][field] = value;
                    this.updateConditionJSON();
                }
                console.log(this.conditionJSON);
            }
        }
    }
</script>

@push('scripts')
    <script>
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
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

