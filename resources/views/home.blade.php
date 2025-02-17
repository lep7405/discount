{{--@extends('layouts.app')--}}

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Discount Manager</div>

                    <div class="card-body">
                        @auth
                            <p>You are logged in!</p>
                            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-primary">Go to Dashboard</a>
                        @else
                            <p>You must login!</p>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
