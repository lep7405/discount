@extends('admin.layouts.admin')

@section('title_admin')

@endsection

@section('title_admin_breadcumb')
    {{ 'User' }}
@endsection

@section('main_content')
    <div class="p-6">
        <div class="max-w-3xl mx-auto">
            <!-- Card -->
            <div class="bg-white rounded-lg shadow-md">
                <!-- Card Header -->
                <div class="bg-blue-600 px-6 py-4">
                    <h3 class="text-xl font-semibold text-white">{{ $user->name }}</h3>
                </div>
                <!-- Form -->
                <form role="form" action="{{ route('admin.user.changePassword') }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                    <div class="p-6 space-y-6">
                        <!-- Email Field -->
                        <div class="space-y-1">
                            <label for="exampleInputEmail1" class="block text-sm font-medium text-gray-700">
                                Email address
                            </label>
                            <input
                                type="email"
                                disabled
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-500"
                                id="exampleInputEmail1"
                                value="{{ $user->email }}"
                                placeholder="Enter email"
                            >
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-1">
                            <label for="exampleInputPassword1" class="block text-sm font-medium text-gray-700">
                                Password
                            </label>
                            <input
                                type="password"
                                name="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                id="exampleInputPassword1"
                                placeholder="Password"
                            >
                            @if ($errors->has('password'))
                                <span class="text-red-500">{{ $errors->first('password') }}</span>
                            @endif
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="space-y-1">
                            <label for="exampleInputPassword2" class="block text-sm font-medium text-gray-700">
                                Confirm Password
                            </label>
                            <input
                                type="password"
                                name="password_confirmation"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                id="exampleInputPassword2"
                                placeholder="Confirm Password"
                            >
                        </div>
                    </div>

                    <!-- Form Footer -->
                    <div class="bg-gray-50 px-6 py-4">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
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
    </script>
@endpush
