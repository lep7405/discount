@extends('admin.layouts.admin')

@section('title_admin')
    Create New Coupon
@endsection

@section("li_breadcumb")
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.reports') }}">{{ $appName }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.discounts') }}">{{ 'Discounts' }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.edit_discount', $discount->id) }}">{{ $discount->name }}</a></li>
@endsection

@section('title_admin_breadcumb')
    {{ 'Create Coupon' }}
@endsection

@section('main_content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Column - Form -->
                <div class="lg:w-2/3">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-blue-600 px-6 py-4">
                            <h3 class="text-white text-lg font-semibold">&nbsp;</h3>
                        </div>

                        <form role="form"  method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" name="_db" value="{{ $databaseName }}">

                            <div class="p-6 space-y-6">
                                <div class="space-y-4">
                                    <div>
                                        <label for="inputCode" class="block text-sm font-medium text-gray-700">Code</label>
                                        <input
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            id="inputCode"
                                            name="code"
                                            value="{{ old('code') }}"
                                            placeholder="Enter code"
                                        >
                                        @if($errors->has('code'))
                                            <div class="text-red-500">
                                                {{$errors->first('code')}}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <label for="inputShop" class="block text-sm font-medium text-gray-700">Shop</label>
                                        <input
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            id="inputShop"
                                            name="shop"
                                            value="{{ old('shop') }}"
                                            placeholder="Enter shop"
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
                        <div class="bg-blue-600 px-6 py-4">
                            <h3 class="text-white text-lg font-semibold">Discount Info</h3>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-center">
                                    <span class="font-medium">Name:</span>
                                    <span class="ml-2">{{ $discount->name }}</span>
                                </li>
                                <li class="flex items-center">
                                    <span>{{ $discount->value.($discount->type == 'amount' ? " USD" : "%") }} discount</span>
                                </li>
                                <li class="flex items-center">
                                    <span>{{ $discount->usage_limit == 0 ? "Unlimited" : $discount->usage_limit }} times usage</span>
                                </li>
                                <li class="flex items-center">
                                    <span>{{ $discount->trial_days ? $discount->trial_days : "0" }} days trial</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="font-medium">Start:</span>
                                    <span class="ml-2">{{ date_format(date_create($discount->started_at), "d-m-Y") }}</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="font-medium">End:</span>
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



