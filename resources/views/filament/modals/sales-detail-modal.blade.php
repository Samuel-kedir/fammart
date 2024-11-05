<!-- Modal content displaying sale items -->
<div>
    <h3 class="text-lg font-medium leading-6 text-gray-900">Sale Items</h3>
    <div class="mt-2">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
            </thead>
            <tbody class="bg-black divide-y divide-gray-200">
                @foreach($saleItems as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->price }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->item_total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
