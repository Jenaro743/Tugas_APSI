@extends('layout')

@section('title', 'Dashboard staf')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <span class="accent fw-bold">{{ strtoupper(auth()->user()->role) }} PANEL</span>
        <h1 class="fw-bold text-white">Halo, {{ auth()->user()->name }}</h1>
    </div>
    @if(auth()->user()->role === 'admin')
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#staffModal">+ Tambah karyawan</button>
    @endif
</div>

@php
    $cards = [
        'Produk' => $stats['products'],
        'Stok tersedia' => $stats['stock'] . ' unit',
        'Menunggu pembayaran' => $stats['pending'],
    ];
    if (auth()->user()->role === 'admin') {
        $cards['Omzet lunas'] = 'Rp ' . number_format($stats['sales'], 0, ',', '.');
    }
@endphp
<div class="row g-3 my-3">
    @foreach($cards as $label => $value)
        <div class="col-6 col-lg-3">
            <div class="card rounded-4 p-3 text-white">
                <span class="text-white-50">{{ $label }}</span>
                <h3 class="accent fw-bold mb-0">{{ $value }}</h3>
                @if($label === 'Stok tersedia')
                    <small class="text-white-50">Aksesoris + konsol PS3/PS4; game dan jasa tak terbatas</small>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="card rounded-4 p-3 mb-3">
    <h4 class="text-white">Prioritas Pembaruan Stok (SAW)</h4>
    <p class="text-white-50 mb-3">Aksesoris saja. Bobot contoh: stok saat ini (cost) 60%, penjualan lunas (benefit) 40%. Stok 0 mendapat nilai tertinggi pada kriteria stok.</p>
    <div class="table-responsive">
        <table class="table table-sm align-middle text-white">
            <thead>
                <tr><th>Peringkat</th><th>Produk</th><th>Stok saat ini</th><th>Terjual lunas</th><th>Skor SAW</th><th>Perbarui stok (+/-)</th></tr>
            </thead>
            <tbody>
                @forelse($stockPriorities as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->name }}<br><small class="text-white-50">{{ $product->platform }} · {{ $product->category }}</small></td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ $product->units_sold }} unit</td>
                        <td class="accent fw-bold">{{ number_format($product->saw_score, 4, ',', '.') }}</td>
                        <td>
                            <form method="post" action="{{ route('products.stock', $product) }}" class="d-flex gap-1">
                                @csrf
                                <input class="form-control form-control-sm" name="change" type="number" min="{{ -$product->stock }}" step="1" placeholder="contoh +5" required>
                                <button class="btn btn-sm btn-success text-nowrap">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-white-50">Belum ada game atau aksesoris terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card rounded-4 p-3">
            <h4 class="text-white">Tambah produk atau aksesoris</h4>
            <form method="post" action="{{ route('products.create') }}" enctype="multipart/form-data" class="row g-2">
                @csrf
                <input class="form-control" name="name" placeholder="Nama game, konsol, atau aksesoris" required>
                <div class="col-6">
                    <select name="platform" class="form-select" onchange="toggleProductFields(this.form,'create')">
                        <option value="PS3">PS3</option>
                        <option value="PS4">PS4</option>
                        <option value="Aksesoris">Aksesoris PS</option>
                        <option value="Jasa">Jasa Repair</option>
                    </select>
                </div>
                <div class="col-6">
                    <input name="category" class="form-control" placeholder="Kategori (isi Konsol untuk stok PS3/PS4)" oninput="toggleProductFields(this.form,'create')" required>
                </div>
                <div class="col-6" id="create-price">
                    <input name="price" type="number" min="1" class="form-control" placeholder="Harga" required>
                </div>
                <div class="col-6" id="create-stock">
                    <input name="stock" type="number" min="0" class="form-control" placeholder="Stok unit" style="display:none" disabled>
                    <span data-unlimited-stock class="form-text text-white-50">Stok tak terbatas untuk game dan jasa</span>
                </div>
                <textarea name="description" class="form-control" placeholder="Deskripsi produk"></textarea>
                <input name="image" type="file" accept="image/*" class="form-control">
                <button class="btn btn-success">Simpan produk</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card rounded-4 p-3">
            <h4 class="text-white">Data produk, aksesoris & stok</h4>
            <div class="table-responsive">
                <table class="table table-sm align-middle text-white">
                    <thead>
                        <tr><th>Produk</th><th>Stok</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                {{ $product->name }}<br>
                                <small class="text-white">{{ $product->platform }} · {{ $product->category }} · {{ $product->platform==='Jasa' ? 'Tanpa harga' : 'Rp '.number_format($product->price, 0, ',', '.') }}</small>
                            </td>
                            <td class="fw-bold">{{ $product->platform === 'Jasa' || (in_array($product->platform, ['PS3', 'PS4'], true) && strcasecmp(trim($product->category), 'Konsol') !== 0) ? 'Tak terbatas' : $product->stock }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editProduct{{ $product->id }}">Edit</button>
                                    <form method="post" action="{{ route('products.delete', $product) }}" onsubmit="return confirm('Hapus {{ $product->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($products as $product)
@php($unlimitedStock = $product->platform === 'Jasa' || (in_array($product->platform, ['PS3', 'PS4'], true) && strcasecmp(trim($product->category), 'Konsol') !== 0))
<div class="modal fade" id="editProduct{{ $product->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('products.edit', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title text-white">Edit produk</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-white">Nama produk</label>
                            <input class="form-control" name="name" value="{{ $product->name }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white">Jenis</label>
                            <select name="platform" class="form-select" onchange="toggleProductFields(this.form,'edit{{ $product->id }}')">
                                <option value="PS3" @selected($product->platform === 'PS3')>PS3</option>
                                <option value="PS4" @selected($product->platform === 'PS4')>PS4</option>
                                <option value="Aksesoris" @selected($product->platform === 'Aksesoris')>Aksesoris PS</option>
                                <option value="Jasa" @selected($product->platform === 'Jasa')>Jasa Repair</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white">Kategori</label>
                            <input class="form-control" name="category" value="{{ $product->category }}" oninput="toggleProductFields(this.form,'edit{{ $product->id }}')" required>
                        </div>
                        <div class="col-md-4" id="edit{{ $product->id }}-price" @if($product->platform==='Jasa') style="display:none" @endif>
                            <label class="form-label text-white">Harga</label>
                            <input class="form-control" name="price" type="number" min="{{ in_array($product->platform, ['PS3', 'PS4'], true) && strcasecmp(trim($product->category), 'Konsol') !== 0 ? 1 : 0 }}" value="{{ $product->price }}" @if($product->platform!=='Jasa') required @endif>
                        </div>
                        <div class="col-md-4" id="edit{{ $product->id }}-stock">
                            <label class="form-label text-white">Stok unit</label>
                            <input class="form-control" name="stock" type="number" min="0" value="{{ $product->stock }}" @if($unlimitedStock) style="display:none" disabled @else required @endif>
                            <span data-unlimited-stock class="form-text text-white-50" @unless($unlimitedStock) style="display:none" @endunless>Stok tak terbatas untuk game dan jasa</span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white">Foto baru</label>
                            <input class="form-control" name="image" type="file" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="4">{{ $product->description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success">Simpan perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="card rounded-4 p-3 mt-3">
    <h4 class="text-white">Pesanan masuk</h4>
    <div class="table-responsive">
        <table class="table align-middle text-white">
            <thead><tr><th>Transaksi</th><th>Pembeli</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>
                        <a class="accent" href="{{ route('receipt', $order) }}">{{ $order->number }}</a>
                        @if(auth()->user()->role === 'admin' && $order->payment_status === 'Lunas')
                            <br><a class="btn btn-sm btn-outline-light mt-1" href="{{ route('orders.receipt.print', $order) }}">Cetak struk</a>
                        @endif
                    </td>
                    <td>{{ $order->customer_name }}<br><small class="text-white">{{ $order->whatsapp }}</small></td>
                    <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    <td>{{ $order->payment_method }}<br>{{ $order->payment_status }}</td>
                    <td>{{ $order->status }}</td>
                    <td>
                        <form method="post" action="{{ route('orders.status', $order) }}" class="d-flex gap-1 flex-wrap">
                            @csrf
                            <select class="form-select form-select-sm" name="status">
                                <option @selected($order->status === 'Diproses')>Diproses</option>
                                <option @selected($order->status === 'Siap Diambil')>Siap Diambil</option>
                                <option @selected($order->status === 'Selesai')>Selesai</option>
                                <option @selected($order->status === 'Dibatalkan')>Dibatalkan</option>
                            </select>
                                            @if(in_array($order->payment_method, ['COD', 'Virtual Account (DEMO)', 'QRIS (DEMO)'], true))
                                <label class="small text-white"><input type="checkbox" name="payment_status" value="Lunas" @checked($order->payment_status === 'Lunas')> Lunas</label>
                            @endif
                            <button class="btn btn-sm btn-success">Simpan</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $orders->links() }}
    </div>
</div>

@if(auth()->user()->role === 'admin')
<div class="modal fade" id="staffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('staff.create') }}">
                @csrf
                <div class="modal-header"><h5 class="text-white">Tambah akun karyawan</h5></div>
                <div class="modal-body">
                    <input class="form-control mb-2" name="name" placeholder="Nama" required>
                    <input class="form-control mb-2" name="staff_id" placeholder="ID karyawan" required>
                    <input class="form-control" type="password" name="password" placeholder="Password minimal 8 karakter" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success">Buat akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
<script>
function toggleProductFields(form, suffix) {
    const platform = form.querySelector('[name="platform"]').value;
    const category = form.querySelector('[name="category"]').value.trim().toLowerCase();
    const isService = platform === 'Jasa';
    const isPlaystation = platform === 'PS3' || platform === 'PS4';
    const isGame = isPlaystation && category !== 'konsol';
    const unlimitedStock = isService || isGame;
    const price = document.getElementById(suffix + '-price');
    const stock = document.getElementById(suffix + '-stock');
    if (price) {
        price.style.display = isService ? 'none' : '';
        const priceInput = price.querySelector('input');
        if (priceInput) {
            priceInput.required = !isService;
            priceInput.min = isGame ? '1' : '0';
        }
    }
    if (stock) {
        const stockInput = stock.querySelector('input');
        const unlimitedStockMessage = stock.querySelector('[data-unlimited-stock]');
        if (stockInput) {
            stockInput.style.display = unlimitedStock ? 'none' : '';
            stockInput.required = !unlimitedStock;
            stockInput.disabled = unlimitedStock;
        }
        if (unlimitedStockMessage) unlimitedStockMessage.style.display = unlimitedStock ? '' : 'none';
    }
}
</script>
@endsection
