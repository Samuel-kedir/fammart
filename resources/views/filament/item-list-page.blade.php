<x-filament::page>
    <div>


        {{-- Render the form --}}
        {{ $this->form }}
        <div>
            {{ $this->getFormActions() }}
        </div>

        Additional content: Existing Products
        <h3>Existing Products</h3>
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Price</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->size }}</td>
                        <td>{{ $product->price }}</td>
                        <td>{{ $product->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament::page>
