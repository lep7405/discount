@extends('admin.layouts.admin')

@section('title_admin')
    Generates
@endsection

@section("li_breadcumb")
    <li><a href="{{ route('admin.get_generate') }}">{{ 'Generate Coupon' }}</a></li>
@endsection

@section('title_admin_breadcumb')
    {{ 'Update' }}
@endsection

@section('main_content')
    <div x-data="{ showModal: false }" x-cloak class="container mx-auto px-4 py-8">
        <div class="flex flex-wrap -mx-4">
            <div class="w-full px-4">
                <form role="form" action="{{ route('admin.post_edit_generate', $generate->id) }}" method="POST" class="space-y-8">
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
                                    @if (count($errors) > 0)
                                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                                            <ul class="list-disc list-inside">
                                                @foreach ($errors->all() as $error)
                                                    <li>{!! $error !!}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Discount Selection -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">Discount</label>
                                        <p class="text-sm text-gray-600 current_discount_status" id="discountInfo"></p>
                                        <select id="discount_id" @if (!$status_del) disabled @endif class="discount_select2 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" name="discount_app">
                                            @foreach ($discountData as $item)
                                                <option value="{{ $item->id.'&'.$item->databaseName }}"
                                                        @if ($generate->discount_id == $item->id && $generate->app_name == $item->databaseName) selected @endif>
                                                    {{ $item->name.' / '.$item->appName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Conditions -->
                                    <div x-data="conditionManager()" class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="condition">Conditions</label>
                                        <ul id="more_condition" class="mb-2 space-y-2">
                                            <template x-for="condition in conditions" :key="condition.id">
                                                <li class="p-2 border rounded">
                                                    <template x-for="(app, index) in condition.apps" :key="index">
                                                        <div class="flex items-center space-x-2 mb-2">
                                                            <span x-show="index > 0">OR</span>
                                                            <select
                                                                x-model="app.name"
                                                                @change="updateAppValue(condition.id, index, 'name', $event.target.value)"
                                                                class="form-select mt-1 block w-40"
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
                                                                class="form-select mt-1 block w-40"
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
                                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-sm"
                                                            >
                                                                Remove
                                                            </button>
                                                            <button
                                                                x-show="index === condition.apps.length - 1"
                                                                @click="addOr(condition.id)"
                                                                type="button"
                                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-sm"
                                                            >
                                                                OR
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <button
                                                        @click="removeCondition(condition.id)"
                                                        type="button"
                                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-sm"
                                                    >
                                                        Remove Condition
                                                    </button>
                                                </li>
                                            </template>
                                        </ul>
                                        <button type="button" @click="addCondition" class="btnAnd bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">And</button>
                                        <input type="hidden" name="condition_object" x-model="conditionJSON" class="condition_object">
                                    </div>

                                    <!-- Other Fields -->
                                    <div class="space-y-4">
                                        <div>
                                            <label for="range" class="block text-sm font-medium text-gray-700">Expired Range (Days)</label>
                                            <input type="number" name="expired_range" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ $generate->expired_range }}" id="range" placeholder="Expired Range (Days)">
                                        </div>
                                        <div>
                                            <label for="limit" class="block text-sm font-medium text-gray-700">Limit Coupons</label>
                                            <input type="number" name="limit" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ $generate->limit }}" id="limit" placeholder="Limit Coupons Generate">
                                        </div>
                                        <div>
                                            <label for="inputApp" class="block text-sm font-medium text-gray-700">App URL</label>
                                            <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="inputApp" name="app_url" value="{{ $generate->app_url }}" placeholder="Enter App URL">
                                        </div>
                                    </div>

                                    <!-- Auto Generate Coupon URL Info -->
                                    <div class="text-sm text-gray-600">
                                        <p>Auto Generate Coupon URL: <strong>{{ $generate_url.'{timestamp}/{shop_id}' }}</strong></p>
                                        <p>Example for date 01-01-2018, shop id = 1: <strong>{{ $generate_url."1514764800/1" }}</strong></p>
                                        <p><strong>PRIVATE: {{ $private_generate_url.'{shop_name}' }}</strong></p>
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
                                            $arr = json_decode($generate->success_message, true);
                                            if (is_array($arr)) {
                                                $success_message = $arr['message'] ?? null;
                                                $extend_message = $arr['extend'] ?? null;
                                            }
                                        }

                                        if (!empty($generate->fail_message)) {
                                            $arr = json_decode($generate->fail_message, true);
                                            if (is_array($arr)) {
                                                $fail_message = $arr['message'] ?? null;
                                                $reason_expired = $arr['reason_expired'] ?? null;
                                                $reason_limit = $arr['reason_limit'] ?? null;
                                                $reason_condition = $arr['reason_condition'] ?? null;
                                            }
                                        }
                                    @endphp

                                    <div>
                                        <label for="inputHeader" class="block text-sm font-medium text-gray-700">Header Message</label>
                                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="inputHeader" name="header_message" value="{{ $generate->header_message }}" placeholder="Default: Welcome to Secomapp special offer!">
                                    </div>
                                    <div>
                                        <label for="inputSuccess" class="block text-sm font-medium text-gray-700">Success Message</label>
                                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="inputSuccess" name="success_message" value="{{ $success_message ?? '' }}" placeholder="Default: Your offer was created! Please install app to active the offer!">
                                    </div>
                                    <div>
                                        <label for="inputUsed" class="block text-sm font-medium text-gray-700">Used Message</label>
                                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="inputUsed" name="used_message" value="{{ $generate->used_message }}" placeholder="Default: You have already claimed this offer!">
                                    </div>
                                    <div>
                                        <label for="inputFail" class="block text-sm font-medium text-gray-700">Fail Message</label>
                                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="inputFail" name="fail_message" value="{{ $fail_message ?? '' }}" placeholder="Default: Offer can't be created because of following reasons:">
                                    </div>
                                    <div>
                                        <label for="inputExtend" class="block text-sm font-medium text-gray-700">Extend Message</label>
                                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="inputExtend" name="extend_message" value="{{ $extend_message ?? '' }}" placeholder="Default: Just install app then offer will be applied automatically!">
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Fail Reasons -->
                            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                                <div class="bg-blue-600 text-white px-6 py-4">
                                    <h3 class="text-lg font-semibold">Custom Fail Reasons</h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div>
                                        <label for="inputReasonTime" class="block text-sm font-medium text-gray-700">Time Expired</label>
                                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="inputReasonTime" name="reason_expired" value="{{ $reason_expired ?? '' }}" placeholder="Default: This offer was expired!">
                                    </div>
                                    <div>
                                        <label for="inputReasonLimit" class="block text-sm font-medium text-gray-700">Limited Coupon</label>
                                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="inputReasonLimit" name="reason_limit" value="{{ $reason_limit ?? '' }}" placeholder="Default: Offers were reached the limited!">
                                    </div>
                                    <div>
                                        <label for="inputReasonCondition" class="block text-sm font-medium text-gray-700">Not Match Conditions</label>
                                        <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="inputReasonCondition" name="reason_condition" value="{{ $reason_condition ?? '' }}" placeholder="Default: Your store doesn't match app conditions!">
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
                <form id="deleteGenerate" method="POST" action="{{ route('admin.destroy_generate', $generate->id) }}">
                    @csrf
                    @method('DELETE')
                </form>
                <form id="changeStatus" action="{{ route('admin.change_status_generate', $generate->id) }}" method="POST">
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
            const discountInfo = document.getElementById('discountInfo');

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
                let url = "";

                if (id && storedDiscountId !== id) {
                    url = `http://localhost:8000/admin/${database}/discounts/${id}`;
                } else {
                    url = `http://localhost:8000/admin/${database}/discounts/${storedDiscountId}`;
                }
                console.log('url',url);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ db: database })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data || data.error) {
                            discountInfo.innerHTML = '<li>Error fetching discount data</li>';
                            return;
                        }

                        let element = `
                <li>${data.value}${data.type === 'amount' ? " USD" : "%"} discount</li>
                <li>${data.usage_limit == 0 ? "Unlimited" : data.usage_limit + " times usage"}</li>
                <li>${data.trial_days ? data.trial_days : 0} days trial</li>
                <li>Start: ${data.started_at ? formatDate(data.started_at) : "N/A"}</li>
                <li>End: ${data.expired_at ? formatDate(data.expired_at) : "N/A"}</li>
            `;
                        discountInfo.innerHTML = element;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        discountInfo.innerHTML = '<li>Failed to load discount info</li>';
                    });
            }

            function formatDate(dateString) {
                if (!dateString) return "N/A";
                let date = new Date(dateString);
                return date.toLocaleDateString('en-GB');
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
        console.log(convertArray(@json($generate->conditions) ? JSON.parse(@json($generate->conditions)) : []));
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
