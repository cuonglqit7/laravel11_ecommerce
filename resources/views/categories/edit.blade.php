@extends('layouts.main')
@section('title', 'Quản lý sản phẩm')
@section('navbar')
    <x-component-navbar active="category" />
@endsection
@section('content')
    <div class="mx-auto bg-white p-3 rounded-lg shadow-md text-sm">
        <div class="flex items-center mb-3 justify-between">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                    <li class="inline-flex items-center">
                        <a href="{{ route('categories.index') }}"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                            <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                            </svg>
                            Danh sách danh mục
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 9 4-4-4-4" />
                            </svg>
                            <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Chỉnh sửa</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <a href="{{ route('categories.index') }}"
                class="bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600 text-xs">Về trước</a>
        </div>
        <hr class="h-px my-2 bg-gray-200 border-0 dark:bg-gray-700">
        <form action="{{ route('categories.update', $category->slug) }}" method="POST"
            class="bg-white shadow-lg rounded-xl p-6 space-y-6 text-sm">
            @csrf
            @method('PUT')

            <h5 class="text-2xl font-bold text-gray-800 dark:text-white">Chỉnh sửa danh mục</h5>

            <div>
                <label for="category_name" class="block text-gray-700 font-medium">Tên danh mục</label>
                <input type="text" id="category_name" name="category_name" value="{{ $category->category_name }}"
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                @error('category_name')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-gray-700 font-medium">Mô tả</label>
                <textarea id="description" name="description" rows="4"
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">{{ $category->description }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="parent_id" class="block text-gray-700 font-medium">Danh mục cha
                    <span class="italic text-xs">*Có thể bỏ qua nếu là danh mục cha</span>
                </label>
                <select multiple id="parent_id" name="parent_id"
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                    <option selected disabled>Chọn danh mục</option>
                    @foreach ($categories as $categor)
                        <option value="{{ $categor->id }}" {{ $categor->id == $category->parent_id ? 'selected' : '' }}>
                            {{ $categor->category_name }}</option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="position" class="block text-gray-700 font-medium">Chọn vị trí</label>
                <select id="position" name="position"
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                    <option selected disabled>Chọn 1 vị trí</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->position }}">Trước {{ $category->category_name }}</option>
                    @endforeach
                </select>
                @error('position')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Xác
                    nhận</button>
                <a href="{{ route('categories.index') }}"
                    class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition duration-300">Hủy</a>
            </div>
        </form>
    </div>
@endsection
