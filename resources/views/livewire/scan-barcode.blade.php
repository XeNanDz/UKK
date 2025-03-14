<div>
    <input type="text" wire:model="barcode" placeholder="Scan barcode" wire:keydown.enter="scanBarcode">

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($produk)
        <div>
            <h3>{{ $produk->nama_produk }}</h3>
            <p>Harga: {{ $produk->harga_jual }}</p>
            <p>Stok: {{ $produk->stok }}</p>
        </div>
    @endif
</div>
