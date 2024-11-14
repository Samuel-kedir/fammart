{{-- @vite('resources/css/app.css') --}}

<div class="w-full" style="width: 70vw">
    <h3 class="mb-2 text-lg font-semibold">Existing Products</h3>
    <table class="w-full border border-gray-200 dark:border-gray-700">
        <thead class="bg-gray-100 dark:bg-gray-800">
            <tr>
                <th class="px-4 py-3 font-medium text-left text-red-700 border-b border-gray-200 dark:border-gray-700 dark:text-gray-100">Name</th>
                <th class="px-4 py-3 font-medium text-left text-red-700 border-b border-gray-200 dark:border-gray-700 dark:text-gray-100">Size</th>
                {{-- <th class="px-4 py-3 font-medium text-left text-red-700 border-b border-gray-200 dark:border-gray-700 dark:text-gray-100">Price</th> --}}
                <th class="px-4 py-3 font-medium text-left text-red-700 border-b border-gray-200 dark:border-gray-700 dark:text-gray-100">Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50 dark:bg-gray-900' : 'bg-white dark:bg-gray-800' }} hover:bg-gray-100 dark:hover:bg-gray-700">
                    <td class="px-4 py-2 text-gray-800 border-b border-gray-200 dark:border-gray-700 dark:text-gray-200">{{ $product->name }}</td>
                    <td class="px-4 py-2 text-gray-800 border-b border-gray-200 dark:border-gray-700 dark:text-gray-200">{{ $product->size }}</td>
                    {{-- <td class="px-4 py-2 text-gray-800 border-b border-gray-200 dark:border-gray-700 dark:text-gray-200">${{ number_format($product->price, 2) }}</td> --}}
                    <td class="px-4 py-2 text-gray-800 border-b border-gray-200 dark:border-gray-700 dark:text-gray-200">{{ $product->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
