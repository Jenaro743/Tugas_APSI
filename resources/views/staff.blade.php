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
        'Stok tersedia' => $stats['stock'],
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
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card rounded-4 p-3">
            <h4 class="text-white">Tambah produk atau aksesoris</h4>
            <form method="post" action="{{ route('products.create') }}" enctype="multipart/form-data" class="row g-2">
                @csrf
                <input class="form-control" name="name" placeholder="Nama game atau aksesoris" required>
                <div class="col-6">
                    <select name="platform" class="form-select" onchange="toggleServiceFields(this,'create')">
                        <option value="PS3">PS3</option>
                        <option value="PS4">PS4</option>
                        <option value="Aksesoris">Aksesoris PS</option>
                        <option value="Jasa">Jasa Repair</option>
                    </select>
                </div>
                <div class="col-6">
                    <input name="category" class="form-control" placeholder="Kategori" required>
                </div>
                <div class="col-6" id="create-price">
                    <input name="price" type="number" min="0" class="form-control" placeholder="Harga" required>
                </div>
                <div class="col-6" id="create-stock">
                    <input name="stock" type="number" min="0" class="form-control" placeholder="Stok" required>
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
                            <td class="fw-bold">{{ $product->platform==='Jasa' ? 'Tak terbatas' : $product->stock }}</td>
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
                            <select name="platform" class="form-select" onchange="toggleServiceFields(this,'edit{{ $product->id }}')">
                                <option value="PS3" @selected($product->platform === 'PS3')>PS3</option>
                                <option value="PS4" @selected($product->platform === 'PS4')>PS4</option>
                                <option value="Aksesoris" @selected($product->platform === 'Aksesoris')>Aksesoris PS</option>
                                <option value="Jasa" @selected($product->platform === 'Jasa')>Jasa Repair</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white">Kategori</label>
                            <input class="form-control" name="category" value="{{ $product->category }}" required>
                        </div>
                        <div class="col-md-4" id="edit{{ $product->id }}-price" @if($product->platform==='Jasa') style="display:none" @endif>
                            <label class="form-label text-white">Harga</label>
                            <input class="form-control" name="price" type="number" min="0" value="{{ $product->price }}" @if($product->platform!=='Jasa') required @endif>
                        </div>
                        <div class="col-md-4" id="edit{{ $product->id }}-stock" @if($product->platform==='Jasa') style="display:none" @endif>
                            <label class="form-label text-white">Stok</label>
                            <input class="form-control" name="stock" type="number" min="0" value="{{ $product->stock }}" @if($product->platform!=='Jasa') required @endif>
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
                    <td><a class="accent" href="{{ route('receipt', $order) }}">{{ $order->number }}</a></td>
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
function toggleServiceFields(select, suffix) {
    const isService = select.value === 'Jasa';
    const price = document.getElementById(suffix + '-price');
    const stock = document.getElementById(suffix + '-stock');
    if (price) {
        price.style.display = isService ? 'none' : '';
        const priceInput = price.querySelector('input');
        if (priceInput) priceInput.required = !isService;
    }
    if (stock) {
        stock.style.display = isService ? 'none' : '';
        const stockInput = stock.querySelector('input');
        if (stockInput) stockInput.required = !isService;
    }
}
</script>
@endsection
