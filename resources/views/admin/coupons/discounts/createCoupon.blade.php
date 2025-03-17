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
                                    <span class="font-medium text-gray-700">Name:</span>
                                    <span class="ml-2">{{ $discount->name }}</span>
                                </li>

                                <li class="flex items-center space-x-2">
                    <span class="text-blue-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                                    <span class="text-gray-700 font-medium">{{ $discount->value . ($discount->type == 'amount' ? " USD" : "%") }} discount</span>

                                </li>
                                <li class="flex items-center space-x-2">
                    <span class="text-green-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                                    <span>{{ $discount->usage_limit == 0 ? "Unlimited" : $discount->usage_limit }} times usage</span>
                                </li>
                                <li class="flex items-center space-x-2">
                    <span class="text-purple-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                                    <span>{{ $discount->trial_days ? $discount->trial_days : "0" }} days trial</span>
                                </li>
                                <li class="flex items-center space-x-2">
                    <span class="text-yellow-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                                    <span class="ml-2">{{ date_format(date_create($discount->started_at), "d-m-Y") }}</span>
                                </li>
                                <li class="flex items-center space-x-2">
                    <span class="text-red-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                                    <span class="ml-2">{{ date_format(date_create($discount->expired_at), "d-m-Y") }}</span>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



