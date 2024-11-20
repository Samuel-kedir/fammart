<div class="p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Sale Details</h3>

    <div class="mt-6">
        <h4 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-200">Sale Items</h4>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-amber-500 dark:text-gray-100">Product</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-amber-500 dark:text-gray-100">Quantity</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-amber-500 dark:text-gray-100">Price</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-amber-500 dark:text-gray-100">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @foreach($saleItems as $item)
                    @php
                        $product = $item->purchase->product ?? null;
                        $pur = App\Models\PurchaseItem::find($item->product_id);
                        $pro = App\Models\Product::find($pur->product_id);
                    @endphp
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                           {{$pro->name}}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">{{ number_format($item->price, 2) }} ETB</td>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">{{ number_format($item->item_total, 2) }} ETB</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        <div class="flex items-center justify-between text-lg font-semibold text-gray-800 dark:text-gray-200">
            <span>Total Price:</span>
            <span class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($record->sum_total, 2) }} ETB</span>
        </div>
        <div class="flex items-center justify-between mt-4 text-lg font-semibold text-gray-800 dark:text-gray-200">
            <span>Payment Method:</span>
            <span class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($record->payment_method) }}</span>
        </div>
    </div>
</div>
