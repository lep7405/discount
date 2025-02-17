@extends('admin.layouts.admin')

@section('title_admin')
    Coupons
@endsection

@section("li_breadcumb")
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.reports') }}">{{ $appName }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.discounts') }}">{{ 'Discounts' }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.'.$databaseName.'.get_edit_discount', $discount->id) }}">{{ $discount->name }}</a></li>
@endsection

@section('title_admin_breadcumb')
    {{ 'List Coupons' }}
@endsection

@section('main_content')
    <div class="w-full">
        <div class="w-full">
            <div class="bg-white shadow-md rounded-lg">
                <div class="p-4 border-b">
                    <div class="w-full">
                        <a href="{{ route('admin.'.$databaseName.'.show_create_coupon_in_discount', $discount->id) }}" class="float-right bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add New Coupon
                        </a>
                    </div>
                </div>

                @if (session()->has('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session()->get('message') }}</span>
                    </div>
                @endif

                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table id="data" class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 border-b">#</th>
                                <th class="py-2 px-4 border-b">Code</th>
                                <th class="py-2 px-4 border-b">Shop</th>
                                <th class="py-2 px-4 border-b">Discount</th>
                                <th class="py-2 px-4 border-b">Times used</th>
                                <th class="py-2 px-4 border-b">Status</th>
                                <th class="py-2 px-4 border-b">Created Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($couponData as $item)
                                <tr>
                                    <td class="py-2 px-4 border-b">{{ $item->id }}</td>
                                    <td class="py-2 px-4 border-b">
                                        <a href="{{ route('admin.'.$databaseName.'.get_edit_coupon', $item->id) }}" class="text-blue-500 hover:text-blue-700">{{ $item->code }}</a>
                                    </td>
                                    <td class="py-2 px-4 border-b">{{ $item->shop }}</td>
                                    <td class="py-2 px-4 border-b">
                                        <a href="{{ route('admin.'.$databaseName.'.get_edit_discount', $item->discount->id) }}" class="text-blue-500 hover:text-blue-700">{{ $item->discount->name }}</a>
                                    </td>
                                    <td class="py-2 px-4 border-b">{{ $item->times_used == null ? '0' : $item->times_used }}</td>
                                    <td class="py-2 px-4 border-b">{{ $item->status == 1 ? 'Active' : 'Disable' }}</td>
                                    <td class="py-2 px-4 border-b">{{ date_format(date_create($item->created_at), "d-m-Y") }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 border-b">#</th>
                                <th class="py-2 px-4 border-b">Code</th>
                                <th class="py-2 px-4 border-b">Shop</th>
                                <th class="py-2 px-4 border-b">Discount</th>
                                <th class="py-2 px-4 border-b">Times used</th>
                                <th class="py-2 px-4 border-b">Status</th>
                                <th class="py-2 px-4 border-b">Created Date</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



