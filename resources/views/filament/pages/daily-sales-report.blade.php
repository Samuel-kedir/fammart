<div class="p-6 space-y-6">

    <!-- Page Title with Line -->
    <div class="">
        <h2 class="text-xl font-semibold text-gray-800">Daily Sales Report</h2>
        <div class="w-full h-px mt-4 bg-gray-300"></div>
    </div>

    {{$this->table}}


    <!-- Date Picker to select the date -->
    {{-- <div class="w-1/2 mb-6">
        <label for="date" class="block mb-2 text-lg font-medium text-gray-700">Select Date:</label>
        <input type="date" id="date" wire:model="selectedDate" class="block w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
    </div> --}}

    <!-- Sales Table -->
    <div class="overflow-hidden bg-white rounded-lg shadow">
        <div class="text-lg text-gray-600">
            <p>Today's Date: {{ $selectedDate }}</p>
        </div>

    </div>

    <!-- Total Sales -->
    <div class="p-4 bg-gray-100 rounded-lg">
        <div class="mb-2 text-xl font-semibold text-gray-800">Total Sales:</div>
        <div class="text-2xl font-bold text-gray-900">
            {{ number_format($data['totalSales'], 2) }} ETB
        </div>
    </div>

    <!-- Sales Breakdown by Payment Method -->
    <div class="space-y-4">
        <h3 class="text-xl font-semibold text-gray-800">Sales Breakdown by Payment Method:</h3>
        <ul class="space-y-2">
            @foreach($data['salesByMethod'] as $paymentMethod => $total)
                <li class="text-lg text-gray-700">
                    <strong class="font-semibold capitalize">{{ $paymentMethod }}:</strong>
                    <span class="text-gray-900">{{ number_format($total, 2) }} ETB</span>
                </li>
            @endforeach
        </ul>
    </div>



</div>
