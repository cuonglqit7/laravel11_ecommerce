@extends('layouts.main')
@section('title', 'Dashboard')
@section('checked')
    <nav class="flex flex-col space-y-2">
        <a href="{{ route('home') }}" class="px-3 py-2 bg-gray-700 rounded text-sm">Dashboard</a>
        <a href="{{ route('products.index') }}" class="px-3 py-2 hover:bg-gray-700 rounded text-sm">Sản
            phẩm</a>

        <a href="{{ route('users.index') }}" class="px-3 py-2 hover:bg-gray-700 rounded text-sm">Khách
            hàng</a>
        <a href="{{ route('roles.index') }}" class="px-3 py-2 hover:bg-gray-700 rounded text-sm">Quyền hạng</a>
        <a href="#" class="px-3 py-2 hover:bg-gray-700 rounded text-sm">Cài đặt</a>
    </nav>
@endsection
@section('content')
    <div class="max-w-7xl mx-auto bg-white p-3 rounded-lg shadow-md text-sm">
        dashboard
    </div>
@endsection
