@extends('admin.layouts.admin')

@section('title_admin')
    Generates
@endsection
@section("li_breadcumb")
    <li><a href="{{ route('admin.get_generate') }}">{{ 'Generate Coupon' }}</a></li>
@endsection
@section('title_admin_breadcumb')
    <span class="mr-2">/</span>{{ 'Create' }}
@endsection
@section('main_content')
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap -mx-4">
            <div class="w-full px-4">
                <!-- form start -->
                <form role="form" action={{ route('admin.post_new_generate') }} method="POST">
                    @csrf
                    <div class="flex flex-wrap -mx-4">
                        <div class="w-full lg:w-2/3 px-4 mb-8">
                            <!-- general form elements -->
                            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                                <div class="bg-blue-600 text-white px-6 py-4">
                                    <h3 class="text-lg font-semibold">General</h3>
                                </div>
                                <div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg">
                                    {{-- Error Messages --}}
                                    <div class="mb-6">
                                        @if ($errors->any())
                                            <div class="text-red-400">
                                                <ul>
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Discount Selection --}}
                                    <div class="mb-6">
                                        <label for="discount_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Discount<span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <p id="discountInfo" class="text-sm text-gray-600 mb-2"></p>
                                        <select
                                            id="discount_id"
                                            name="discount_app"
                                            class="discount_select2 w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        >
                                            <option value="">-- Select Discount --</option>
                                            @foreach ($discountData as $item)
                                                <option value="{{ $item->id.'&'.$item->databaseName }}">{{ $item->name.' / '.$item->appName }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Conditions --}}
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
                                                                class="form-select px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
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
                                                                class="form-select px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
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

                                    {{-- Expired Range --}}
                                    <div class="mb-6">
                                        <label for="range" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Expired Range (Days)<span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            name="expired_range"
                                            id="range"
                                            placeholder="Expired Range (Days)"
                                            class="w-full px-3 py-2 text-gray-700 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent {{ $errors->has('expired_range') ? 'border-red-500' : 'border-gray-300' }}"
                                            value="{{ old('expired_range') }}"
                                        >
                                    </div>

                                    {{-- Limit Coupons --}}
                                    <div class="mb-6">
                                        <label for="limit" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Limit Coupons
                                        </label>
                                        <input
                                            type="number"
                                            name="limit"
                                            id="limit"
                                            placeholder="Limit Coupons Generate"
                                            class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ old('limit') }}"
                                        >
                                    </div>

                                    {{-- App URL --}}
                                    <div class="mb-6">
                                        <label for="inputApp" class="block text-sm font-semibold text-gray-700 mb-2">
                                            App URL<span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="inputApp"
                                            name="app_url"
                                            placeholder="Enter App URL"
                                            class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ old('app_url') }}"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded submit_config">Save Config</button>
                            </div>
                        </div>
                        <div class="w-full lg:w-1/3 px-4">
                            <div class="mb-8">
                                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                                    <div class="bg-blue-600 text-white px-6 py-4">
                                        <h3 class="text-lg font-semibold">Custom Messages</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="inputHeader">Header Message</label>
                                            <input type="text" class="form-input mt-1 block w-full" id="inputHeader" name="header_message" value="{{ old('header_message') }}" placeholder="Default: Welcome to Secomapp special offer!">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="inputSuccess">Success Message</label>
                                            <input type="text" class="form-input mt-1 block w-full" id="inputSuccess" name="success_message" value="{{ old('success_message') }}" placeholder="Default: Your offer was created! Please install app to active the offer!">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="inputUsed">Used Message</label>
                                            <input type="text" class="form-input mt-1 block w-full" id="inputUsed" name="used_message" value="{{ old('used_message') }}" placeholder="Default: You have already claimed this offer!">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="inputFail">Fail Message</label>
                                            <input type="text" class="form-input mt-1 block w-full" id="inputFail" name="fail_message" value="{{ old('fail_message') }}" placeholder="Default: Offer can't be created because of following reasons:">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="inputExtend">Extend Message</label>
                                            <input type="text" class="form-input mt-1 block w-full" id="inputExtend" name="extend_message" value="{{ old('extend_message') }}" placeholder="Default: Just install app then offer will be applied automatically!">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-8">
                                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                                    <div class="bg-blue-600 text-white px-6 py-4">
                                        <h3 class="text-lg font-semibold">Custom Fail Reasons</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="inputReasonTime">Time Expired</label>
                                            <input type="text" class="form-input mt-1 block w-full" id="inputReasonTime" name="reason_expired" value="{{ old('reason_expired') }}" placeholder="Default: This offer was expired!">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="inputReasonLimit">Limited Coupon</label>
                                            <input type="text" class="form-input mt-1 block w-full" id="inputReasonLimit" name="reason_limit" value="{{ old('reason_limit') }}" placeholder="Default: Offers were reached the limited!">
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="inputReasonCondition">Not Match Conditions</label>
                                            <input type="text" class="form-input mt-1 block w-full" id="inputReasonCondition" name="reason_condition" value="{{ old('reason_condition') }}" placeholder="Default: Your store doesn't match app conditions!">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    function conditionManager() {
        console.log(1);
        return {
            conditions: [],
            conditionJSON: '',
            count_condition: 0,
            addCondition() {
                console.log(2);
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
                let url = `http://localhost:8000/admin/${database}/discounts/${id}`;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ db: database }),
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
