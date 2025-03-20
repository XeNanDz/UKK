<div class="grid grid-cols-1 gap-4 md:grid-cols-3" style="font-family : poppins;">
    <div class="p-6 bg-white rounded-lg shadow-md md:col-span-2 dark:bg-gray-800">
        <form wire:submit="checkout">
            {{$this->form}}
            @error('pelanggan_id') <span class="text-red-500">{{ $message }}</span> @enderror
            @error('pembayaran_id') <span class="text-red-500">{{ $message }}</span> @enderror
            <x-filament::button
                type="submit"
                class="w-full h-12 py-2 mt-6 text-white rounded-lg bg-primary">Checkout</x-filament::button>
        </form>
        <div class="flex items-center justify-between my-10">
            <input wire:model.live.debounce.300ms='search' type="text" placeholder="Cari produk ..."
                class="w-full p-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <input wire:model.live='barcode' type="text" placeholder="Scan dengan alat scanner ..." autofocus id="barcode"
                class="w-full p-2 ml-2 text-gray-900 bg-white border border-gray-300 rounded-lg dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <x-filament::button x-data="" x-on:click="$dispatch('toggle-scanner')" class="w-20 h-12 px-2 ml-2 text-white rounded-lg bg-primary">
                <i class="fa fa-barcode" style="font-size:36px"></i>
            </x-filament::button>
            <livewire:scanner-modal-component>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
            @foreach($produks as $produk)
                <div wire:click="addToPenjualan({{$produk->id}})" class="p-2 bg-gray-100 rounded-lg shadow cursor-pointer dark:bg-gray-700">
                    <h3 class="text-sm font-semibold text-center">{{$produk->nama_produk}}</h3>
                </div>
            @endforeach
        </div>
        <div class="py-4">
            {{ $produks->links() }}
        </div>
    </div>

    <div class="block p-6 bg-white rounded-lg shadow-md md:col-span-1 dark:bg-gray-800 md:hidden">
        <button wire:click="resetPenjualan" class="w-full h-12 py-2 mt-2 mb-4 text-white bg-red-500 rounded-lg">Reset</button>
        @foreach($penjualan_items as $item)
            <div class="mb-4">
                <div class="flex items-center justify-between p-4 bg-gray-100 rounded-lg shadow dark:bg-gray-700">
                    <div class="flex items-center">

                        <div class="px-2">
                            <h3 class="text-sm font-semibold">{{$item['nama_produk']}}</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Rp {{number_format($item['harga_jual'], 0, ',', '.')}}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <x-filament::button color="warning" wire:click="decreaseQty({{$item['produk_id']}})">-</x-filament::button>
                        <span class="px-4">{{$item['qty']}}</span>
                        <x-filament::button color="success" wire:click="increaseQty({{$item['produk_id']}})">+</x-filament::button>
                    </div>
                </div>
            </div>
        @endforeach
        @if(count($penjualan_items) > 0)
            <div class="py-4">
                <h3 class="text-lg font-semibold text-center">Total: Rp {{number_format($this->calculateTotal(), 0, ',', '.')}}</h3>
            </div>
        @endif
    </div>

    <div class="hidden p-6 bg-white rounded-lg shadow-md md:col-span-1 dark:bg-gray-800 md:block">
        <button wire:click="resetPenjualan" class="w-full h-12 py-2 mt-2 mb-4 text-white bg-red-500 rounded-lg">Reset</button>
        @foreach($penjualan_items as $item)
            <div class="mb-4">
                <div class="flex items-center justify-between p-4 bg-gray-100 rounded-lg shadow dark:bg-gray-700">
                    <div class="flex items-center">
                        <div class="px-2">
                            <h3 class="text-sm font-semibold">{{$item['nama_produk']}}</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Rp {{number_format($item['harga_jual'], 0, ',', '.')}}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <x-filament::button color="warning" wire:click="decreaseQty({{$item['produk_id']}})">-</x-filament::button>
                        <span class="px-4">{{$item['qty']}}</span>
                        <x-filament::button color="success" wire:click="increaseQty({{$item['produk_id']}})">+</x-filament::button>
                    </div>
                </div>
            </div>
        @endforeach
        @if(count($penjualan_items) > 0)
            <div class="py-4 border-t border-gray-100 bg-gray-50 dark:bg-gray-700">
                <h3 class="text-lg font-semibold text-center">Total: Rp {{number_format($this->calculateTotal(), 0, ',', '.')}}</h3>
            </div>
        @endif
    </div>

    <div>
        <!-- Tombol atau trigger untuk cetak struk -->
        @if ($showConfirmationModal)
        <div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50">
            <!-- Modal Content -->
            <div class="bg-white rounded-lg shadow-lg w-11/12 sm:w-96">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-purple-500 text-white rounded-t-lg">
                    <h2 class="text-xl text-center font-semibold">CETAK STRUK</h2>
                </div>
                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <p class="text-gray-800">
                        Apakah Anda ingin mencetak struk untuk pesanan ini?
                    </p>
                </div>
                <!-- Modal Footer -->
                <div class="px-6 py-4 flex justify-center space-x-4">
                        <button wire:click="printStruk({{ $penjualanToPrint }})" class="px-4 py-2 bg-purple-500 text-white rounded-full hover:bg-blue-600 focus:ring-2 focus:ring-blue-400">
                            Cetak
                        </button>
                        <button wire:click="$set('showConfirmationModal', false)" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
