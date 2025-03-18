@extends('admin.layouts.admin')

@section('title_admin')
    Create New Coupon
@endsection

@section("li_breadcumb")
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.reports') }}">{{ $appName }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.discounts') }}"><span class="mr-2">/</span>{{ 'Discounts' }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.editDiscount', $discount->id) }}"><span class="mr-2">/</span>{{ $discount->name }}</a></li>
@endsection

@section('title_admin_breadcumb')
    <span class="mr-2">/</span>{{ 'Create Coupon' }}
@endsection

@section('mainContent')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Column - Form -->
                <div class="lg:w-2/3">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <x-section-header title="Create Coupon" />
                        <form role="form"  method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" name="_db" value="{{ $databaseName }}">

                            <div class="p-6 space-y-6">
                                <div class="space-y-4">
                                    <div>
                                        <label for="inputCode" class="block text-sm font-medium text-gray-700">
                                            Code <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="inputCode"
                                            name="code"
                                            value="{{ old('code') }}"
                                            placeholder="Enter code"
                                            class="form-input {{ $errors->has('code') ? 'border-red-500' : 'border-gray-300' }}"
                                        >
                                        @if ($errors->has('code'))
                                            <div class="text-red-500">
                                                {{ $errors->first('code') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <label for="inputShop" class="block text-sm font-medium text-gray-700">Shop</label>
                                        <input
                                            type="text"
                                            id="inputShop"
                                            name="shop"
                                            value="{{ old('shop') }}"
                                            placeholder="Enter shop"
                                            class="form-input {{ $errors->has('shop') ? 'border-red-500' : 'border-gray-300' }}"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-4">
                                <button
                                    type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Create
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column - Discount Info -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <x-section-header title="Discount Info" />
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg shadow-sm">
                            <ul class="space-y-3">
                                <li class="flex items-center space-x-2">
                                    <span class="text-gray-700 font-medium">{{ $discount->name }}</span>
                                </li>
                                <li class="flex items-center space-x-2">
        <span class="text-blue-500">
            <i class="fas fa-money-bill-wave w-5 h-5"></i>
        </span>
                                    <span class="text-gray-700 font-medium">{{ $discount->value }}{{ $discount->type === 'amount' ? ' USD' : '%' }} discount</span>
                                </li>
                                <li class="flex items-center space-x-2">
        <span class="text-green-500">
            <i class="fas fa-check-circle w-5 h-5"></i>
        </span>
                                    <span class="text-gray-700">{{ $discount->usage_limit == 0 ? 'Unlimited usage' : $discount->usage_limit . ' times usage' }}</span>
                                </li>
                                <li class="flex items-center space-x-2">
        <span class="text-purple-500">
            <i class="fas fa-clock w-5 h-5"></i>
        </span>
                                    <span class="text-gray-700">{{ $discount->trial_days ? $discount->trial_days : 0 }} days trial</span>
                                </li>
                                <li class="flex items-center space-x-2">
        <span class="text-yellow-500">
            <i class="fas fa-calendar-plus w-5 h-5"></i>
        </span>
                                    <span class="text-gray-700">Start: {{ $discount->started_at ? formatDate($discount->started_at) : 'N/A' }}</span>
                                </li>
                                <li class="flex items-center space-x-2">
        <span class="text-red-500">
            <i class="fas fa-calendar-times w-5 h-5"></i>
        </span>
                                    <span class="text-gray-700">End: {{ $discount->expired_at ? formatDate($discount->expired_at) : 'N/A' }}</span>
                                </li>
                            </ul>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



