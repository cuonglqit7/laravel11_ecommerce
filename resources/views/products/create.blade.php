@extends('layouts.main')
@section('title', 'Thêm mới')
@section('navbar')
    <x-component-navbar active="product" />
@endsection
@section('content')
    <div class="max-w-7xl mx-auto bg-white p-3 rounded-lg shadow-md text-sm">
        <div class="flex items-center mb-5 justify-between">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                    <li class="inline-flex items-center">
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <svg class="w-4 h-4 me-2.5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                            </svg>
                            Danh sách sản phẩm
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 9 4-4-4-4" />
                            </svg>
                            <span class="ms-1 text-sm font-medium text-gray-500">Thêm mới</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <a href="{{ route('products.index') }}"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-xs">Về trước</a>
        </div>
        <hr class="h-px my-2 bg-gray-200 border-0 dark:bg-gray-700">
        <form class="max-w-4xl mx-auto grid grid-cols-2 gap-5" action="{{ route('products.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="col-span-2 mt-2">
                <label for="product_name" class="block text-sm font-medium text-gray-700">
                    Tên sản phẩm (tối đa 100 ký tự)
                </label>
                <input type="text" name="product_name" id="product_name" maxlength="100"
                    class="mt-1 block w-full bg-gray-100 rounded-md border-gray-400 p-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Nhập tên sản phẩm..." required oninput="updateCharCount()">
                <p id="char-count" class="text-xs text-gray-500 mt-1">0/100 ký tự</p>
            </div>
            @error('product_name')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Giá</label>
                <input type="number" name="price" id="price" min="1"
                    class="mt-1 block w-full bg-gray-100 rounded-md border-gray-400 p-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Nhập giá sản phẩm..." required oninput="formatPrice(this)">
                <p id="formatted-price" class="text-xs text-gray-500 mt-1"></p>
            </div>
            @error('price')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Danh mục</label>
                <select name="category" id="category"
                    class="mt-1 block w-full bg-gray-100 rounded-md border-2 border-gray-400 p-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="" disabled selected>Chọn danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
            @error('category')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Loại giảm giá</label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center">
                            <input type="radio" name="discount_type" value="Percentage" checked
                                onchange="toggleDiscountInputs()">
                            <span class="ml-2">Phần trăm (%)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="discount_type" value="Fixed Amount"
                                onchange="toggleDiscountInputs()">
                            <span class="ml-2">Số tiền cố định</span>
                        </label>
                    </div>
                </div>


                <!-- Select danh sách giảm giá -->
                <div id="discount_value_wrapper" class="mt-3">
                    <label for="discount_value" class="block text-sm font-medium text-gray-700">Giá trị giảm</label>
                    <select id="discount_value" name="discount_value"
                        class="mt-1 block w-full bg-gray-100 rounded-md border-gray-400 p-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </select>
                </div>
            </div>
            @error('discount_value')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror


            <div class="">
                <label for="attribute_name" class="block text-sm font-medium text-gray-700">Thuộc tính sản phẩm</label>
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="attribute_name[]" id="attribute_name"
                        class="mt-1 block w-full bg-gray-100 rounded-md border-gray-400 p-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Nhập tên thuộc tính (VD: Màu sắc, Kích thước)" required>
                    <input type="text" name="attribute_value[]" id="attribute_value"
                        class="mt-1 block w-full bg-gray-100 rounded-md border-gray-400 p-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Nhập giá trị (VD: Đỏ, XL, Cotton)" required>
                </div>
                <!-- Hiển thị danh sách thuộc tính đã thêm -->
                <div id="attribute-list" class="mt-3 space-y-2"></div>

                <!-- Nút thêm thuộc tính -->
                <button type="button" onclick="addAttribute()"
                    class="mt-2 bg-green-600 text-white px-3 py-2 rounded-md text-sm hover:bg-green-700 transition">
                    + Thêm thuộc tính
                </button>
            </div>
            @error('attribute_name')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <div class="col-span-2">
                <label for="images" class="block text-sm font-medium text-gray-700">Ảnh sản phẩm</label>
                <input type="file" id="images" name="images[]" multiple
                    class="mt-1 block w-full p-1 text-sm border border-gray-300 cursor-pointer rounded-lg bg-gray-50 focus:outline-none"
                    onchange="handleFiles(event)">
                <div id="file-list" class="mt-2 text-sm text-gray-700"></div>
                <p id="file-warning" class="text-red-500 text-sm mt-2 hidden">Bạn chỉ được chọn tối đa 5 hình!</p>
            </div>
            @error('images')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <!-- Mô tả -->
            <div class="col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Mô tả</label>
                <textarea name="description" id="description" rows="4"
                    class="mt-1 p-3 block w-full bg-gray-100 rounded-lg border-2 border-gray-300 bg-blue-20 focus:ring-blue-600 focus:border-blue-300 sm:text-sm"
                    required></textarea>
            </div>
            @error('desciption')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <div>
                <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                <div class="flex gap-4 mt-2">
                    <label class="flex items-center">
                        <input type="radio" name="status" value="1" checked class="hidden peer">
                        <span
                            class="peer-checked:bg-blue-600 peer-checked:text-white px-4 py-2 rounded-md border border-gray-400 cursor-pointer">
                            ✅ Hiển thị
                        </span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="status" value="0" class="hidden peer">
                        <span
                            class="peer-checked:bg-red-600 peer-checked:text-white px-4 py-2 rounded-md border border-gray-400 cursor-pointer">
                            🚫 Ẩn
                        </span>
                    </label>
                </div>
            </div>
            @error('status')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

            <!-- Nút xác nhận -->
            <div class="col-span-2 flex justify-start">
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg px-5 py-2.5 text-center">Thêm
                    sản phẩm</button>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script>
        let selectedFiles = [];
        const maxFiles = 5;

        function handleFiles(event) {
            const input = event.target;
            const newFiles = Array.from(input.files);

            if (selectedFiles.length + newFiles.length > maxFiles) {
                document.getElementById('file-warning').classList.remove('hidden');
                return;
            } else {
                document.getElementById('file-warning').classList.add('hidden');
            }

            // Thêm file mới vào danh sách
            selectedFiles = selectedFiles.concat(newFiles);

            // Tạo DataTransfer để cập nhật input.files
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files; // Cập nhật input.files để gửi tất cả file

            updateFileList();
        }

        function updateFileList() {
            const fileList = document.getElementById('file-list');
            fileList.innerHTML = '';

            if (selectedFiles.length > 0) {
                const ul = document.createElement('ul');
                ul.classList.add('list-disc', 'pl-5');

                selectedFiles.forEach((file, index) => {
                    const li = document.createElement('li');
                    li.classList.add('flex', 'justify-between', 'items-center', 'mb-1');

                    li.innerHTML = `
                    <span>${file.name}</span>
                    <button onclick="removeFile(${index})" class="ml-3 text-red-500 text-xs hover:underline">
                        Xóa
                    </button>
                `;

                    ul.appendChild(li);
                });

                fileList.appendChild(ul);
            }
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);

            // Tạo lại DataTransfer để cập nhật input.files
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            document.getElementById("images").files = dataTransfer.files;

            updateFileList();
        }
    </script>


    <script>
        function updateCharCount() {
            let input = document.getElementById('product_name');
            let count = document.getElementById('char-count');
            count.textContent = `${input.value.length}/100 ký tự`;
        }
    </script>
    <script>
        function formatPrice(input) {
            let value = input.value.replace(/\D/g, '');
            input.value = value;
            document.getElementById('formatted-price').textContent =
                value ? `Giá: ${parseInt(value).toLocaleString('vi-VN')} VND` : '';
        }
    </script>
    <script>
        let discounts = @json($discounts);

        function toggleDiscountInputs() {
            let discountType = document.querySelector('input[name="discount_type"]:checked').value;
            let discountSelect = document.getElementById("discount_value");

            // Xóa các option cũ
            discountSelect.innerHTML = "";

            // Lọc danh sách giảm giá theo loại được chọn
            let filteredDiscounts = discounts.filter(d => d.discount_type === discountType);

            // Thêm option vào select
            filteredDiscounts.forEach(d => {
                let option = document.createElement("option");
                option.value = d.id;
                option.textContent = discountType === "Percentage" ? `${d.discount_value}%` :
                    `${parseInt(d.discount_value).toLocaleString()}đ`;
                discountSelect.appendChild(option);
            });
        }

        // Gọi khi trang load
        document.addEventListener("DOMContentLoaded", toggleDiscountInputs);
    </script>

    <script>
        function addAttribute() {
            let container = document.getElementById("attribute-list");

            let div = document.createElement("div");
            div.classList.add("flex", "gap-3", "items-center");

            let inputName = document.createElement("input");
            inputName.type = "text";
            inputName.name = "attribute_name[]";
            inputName.placeholder = "Tên thuộc tính";
            inputName.required = true;
            inputName.classList.add("block", "w-full", "rounded-md", "border-gray-400", "p-2", "shadow-sm",
                "focus:ring-blue-500", "focus:border-blue-500", "sm:text-sm", "bg-gray-100");

            let inputValue = document.createElement("input");
            inputValue.type = "text";
            inputValue.name = "attribute_value[]";
            inputValue.placeholder = "Giá trị";
            inputValue.required = true;
            inputValue.classList.add("block", "w-full", "rounded-md", "border-gray-400", "p-2", "shadow-sm",
                "focus:ring-blue-500", "focus:border-blue-500", "sm:text-sm", "bg-gray-100");

            let removeBtn = document.createElement("button");
            removeBtn.innerHTML = "❌";
            removeBtn.type = "button";
            removeBtn.classList.add("text-red-500", "hover:text-red-700", "text-sm", "font-semibold");
            removeBtn.onclick = function() {
                container.removeChild(div);
            };

            div.appendChild(inputName);
            div.appendChild(inputValue);
            div.appendChild(removeBtn);

            container.appendChild(div);
        }
    </script>
@endpush
