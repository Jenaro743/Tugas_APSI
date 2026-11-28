@extends('layout')

@section('title', 'Dashboard staf')

@section('content')
{{-- DASHBOARD STAF: CSS, navigasi panel, statistik, produk, stok, dan pesanan. --}}
<style>
.dashboard-shell{display:grid;grid-template-columns:220px minmax(0,1fr);min-height:100vh;background:#0b0f1a}
.dashboard-sidebar{position:sticky;top:0;height:100vh;padding:22px 12px 18px;background:#101522;border-right:1px solid #263149}
.dashboard-brand{display:flex;align-items:center;gap:10px;padding:2px 10px 26px;font-size:19px;font-weight:800;color:#f5f7ff}
.brand-mark{display:grid;place-items:center;width:31px;height:31px;border-radius:9px;background:#9cff57;color:#101522;font-size:14px}
.dashboard-nav{display:flex;flex-direction:column;gap:5px}
.dashboard-nav a{display:flex;align-items:center;gap:11px;height:46px;padding:0 13px;border-radius:8px;color:#a9afca;text-decoration:none;font-size:12px}
.dashboard-nav a.active{background:#9cff57;color:#101522;box-shadow:0 8px 20px rgba(156,255,87,.16)}
.settings-toggle{display:flex;align-items:center;gap:11px;width:100%;height:46px;padding:0 13px;border:0;border-radius:8px;background:transparent;color:#a9afca;text-align:left;font-size:12px}
.settings-toggle:hover,.settings-toggle[aria-expanded="true"]{background:#1d342d;color:#9cff57}
.settings-subnav{display:none;flex-direction:column;gap:4px;padding-left:14px}
.settings-subnav.open{display:flex}
.dashboard-nav .settings-subnav a{height:38px;font-size:11px}
.settings-subnav form{margin:0}
.settings-subnav .sidebar-logout{width:100%;height:38px;padding:0 13px;border:0;border-radius:8px;background:transparent;color:#a9afca;text-align:left;font-size:11px}
.settings-subnav .sidebar-logout:hover{background:#1d342d;color:#9cff57}
.nav-icon{width:16px;text-align:center;opacity:.9}
.dashboard-main{min-width:0;padding:28px 26px 40px}
.dashboard-topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
.dashboard-topbar h1{font-size:24px;margin:0;color:#f7f8ff}
.dashboard-topbar p{margin:3px 0 0;color:#9da8bb;font-size:12px}
.dashboard-user{display:flex;align-items:center;gap:9px;color:#bac6d8;font-size:12px}
.dashboard-user b{display:block;color:#fff;font-size:13px}
.avatar{display:grid;place-items:center;width:36px;height:36px;border-radius:50%;background:#1d342d;color:#9cff57;font-weight:800}
.summary-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}
.summary-grid.employee{grid-template-columns:repeat(2,minmax(0,1fr))}
.summary-card{position:relative;overflow:hidden;height:136px;border:1px solid #263149!important;border-radius:10px;padding:18px 20px;background:#131a29!important}
.summary-card.primary{background:linear-gradient(135deg,#1d342d 0%,#17271f 100%)!important}
.summary-card .summary-label{display:block;color:#9da8bb;font-size:11px}
.summary-card.primary .summary-label{color:#d6e8cd}
.summary-card strong{display:block;margin-top:7px;font-size:22px;color:#fff}
.summary-card small{display:block;margin-top:8px;color:#9da8bb;font-size:10px}
.summary-card.primary small{color:#b9d1ab}
.mini-bars{position:absolute;right:18px;bottom:13px;height:62px;display:flex;align-items:flex-end;gap:7px}
.mini-bars i{display:block;width:10px;border-radius:3px 3px 1px 1px;background:rgba(156,255,87,.38)}
.mini-bars i:nth-child(1){height:22px}.mini-bars i:nth-child(2){height:36px}.mini-bars i:nth-child(3){height:30px}.mini-bars i:nth-child(4){height:48px}.mini-bars i:nth-child(5){height:53px}
.summary-card.primary .mini-bars i{background:rgba(156,255,87,.48)}
.dashboard-card{border:1px solid #263149!important;border-radius:12px!important;background:#131a29!important;box-shadow:none}
.dashboard-card h4{font-size:16px}
.dashboard-section{padding:22px!important}
.section-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}
.section-heading h2{margin:0;color:#fff;font-size:20px;font-weight:700}
.section-heading p{margin:6px 0 0;color:#9da8bb;font-size:13px}
.section-count{flex-shrink:0;border:1px solid #303b51;border-radius:20px;padding:6px 11px;color:#bac6d8;font-size:12px}
.product-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.product-form-field{min-width:0}
.product-form-field.full-width{grid-column:1/-1}
.product-form-field .form-label{margin-bottom:7px;color:#dce2ed;font-size:13px;font-weight:600}
.product-form-field .form-text{display:block;color:#9da8bb;font-size:11px}
.product-form-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding-top:4px}
.product-table th{color:#9da8bb;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.product-table td{vertical-align:middle}
.product-name{color:#fff;font-weight:600}
.product-meta{display:block;margin-top:3px;color:#9da8bb;font-size:12px}
.stock-pill{display:inline-block;border-radius:20px;background:#1d342d;padding:4px 9px;color:#9cff57;font-size:11px;font-weight:600}
.product-actions{display:flex;gap:6px;justify-content:flex-end}
.dashboard-main .text-white-50{color:#9da8bb!important}
.dashboard-main .form-control,.dashboard-main .form-select{background:#0e1421;border-color:#303b51}
.dashboard-main .table{--bs-table-bg:#131a29;--bs-table-color:#eef1f7;--bs-table-border-color:#263149}
.dashboard-main .card{color:#eef1f7}
.donut{position:absolute;right:20px;bottom:15px;width:70px;height:70px;border-radius:50%;background:conic-gradient(#9cff57 0 31%,#34415a 31% 76%,#131a29 76%)}
.donut:after{content:'';position:absolute;inset:15px;border-radius:50%;background:#131a29}
.sparkline{position:absolute;right:12px;bottom:12px;width:92px;height:48px;overflow:visible}
.dashboard-panel{display:none}
.dashboard-panel.active{display:block}
.profile-menu{position:relative}
.profile-toggle{display:flex;align-items:center;gap:9px;padding:0;border:0;background:transparent;color:#bac6d8;text-align:left;font-size:12px}
.profile-toggle b{display:block;color:#fff;font-size:13px}
.profile-toggle:after{content:'⌄';margin-left:3px;color:#9da8bb}
.profile-menu .dropdown-menu{min-width:190px;padding:8px;border:1px solid #263149;background:#131a29}
.profile-menu .dropdown-item{border-radius:6px;color:#eef1f7;font-size:13px}
.profile-menu .dropdown-item:hover{background:#1d342d;color:#9cff57}
@media(max-width:992px){.dashboard-shell{grid-template-columns:78px minmax(0,1fr)}.dashboard-sidebar{padding-inline:8px}.dashboard-brand{padding-inline:8px}.brand-name,.nav-label{display:none}.dashboard-nav a{justify-content:center;padding:0}.summary-grid,.summary-grid.employee{grid-template-columns:1fr}.summary-card{height:120px}}
@media(max-width:680px){.dashboard-shell{display:block}.dashboard-sidebar{position:relative;height:auto;width:100%;padding:10px}.dashboard-brand{display:none}.dashboard-nav{flex-direction:row;overflow:auto}.dashboard-nav a{min-width:46px}.dashboard-main{padding:18px 12px}.summary-grid{grid-template-columns:1fr}}
</style>

<div class="dashboard-shell">
<aside class="dashboard-sidebar">
    <div class="dashboard-brand"><span class="brand-mark">R</span><span class="brand-name">Rexel Games</span></div>
    <nav class="dashboard-nav">
        <a href="#dashboard-overview" class="active dashboard-link" data-panel="overview"><span class="nav-icon">♙</span><span class="nav-label">Dashboard</span></a>
        <a href="#products-panel" class="dashboard-link" data-panel="product-data" title="Data Produk" aria-label="Data Produk"><span class="nav-icon">▣</span><span class="nav-label">Data Produk</span></a>
        <a href="#add-product-panel" class="dashboard-link" data-panel="product-create" title="Tambah Produk" aria-label="Tambah Produk"><span class="nav-icon">＋</span><span class="nav-label">Tambah Produk</span></a>
        <a href="#stock-priorities" class="dashboard-link" data-panel="stock"><span class="nav-icon">▤</span><span class="nav-label">Prioritas Pembaruan Stok </span></a>
        <a href="#orders" class="dashboard-link" data-panel="orders"><span class="nav-icon">◫</span><span class="nav-label">Pesanan</span></a>
        <button type="button" class="settings-toggle" aria-expanded="false" aria-controls="settings-subnav">
            <span class="nav-icon">⚙</span><span class="nav-label">Setting</span>
        </button>
        <div class="settings-subnav" id="settings-subnav">
            <button type="button" class="sidebar-logout" data-bs-toggle="modal" data-bs-target="#aboutModal"><span class="nav-icon">ⓘ</span><span class="nav-label">Tentang</span></button>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout"><span class="nav-icon">↪</span><span class="nav-label">Logout</span></button>
            </form>
        </div>
    </nav>
</aside>
<main class="dashboard-main" id="dashboard-overview">
    <header class="dashboard-topbar">
        <div>
            <h1>Halo, {{ auth()->user()->name }}</h1>
            <p>{{ strtoupper(auth()->user()->role) }} PANEL · REXEL GAMES</p>
        </div>
        <div class="dashboard-user">
            <div class="dropdown profile-menu">
                <button class="profile-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span><b>{{ auth()->user()->name }}</b>{{ ucfirst(auth()->user()->role) }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-white-50 small">{{ ucfirst(auth()->user()->role) }} · {{ auth()->user()->name }}</span></li>
                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#aboutModal">Tentang Rexel Games</button></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

@php
    $cards = [
        'Produk' => number_format($stats['products']),
        'Stok tersedia' => number_format($stats['stock']) . ' unit',
    ];
    if (auth()->user()->role === 'admin') {
        $cards['Omzet hari ini'] = 'Rp ' . number_format($stats['daily_sales'], 0, ',', '.');
    }
@endphp
<div class="summary-grid {{ auth()->user()->role === 'employee' ? 'employee' : '' }}">
    <div class="card summary-card primary">
        <span class="summary-label">Generic Playing</span><strong>{{ $cards['Produk'] }}</strong><small>Produk terdaftar</small>
        <div class="mini-bars"><i></i><i></i><i></i><i></i><i></i></div>
    </div>
    <div class="card summary-card">
        <span class="summary-label">Total Case Today</span><strong>{{ $cards['Stok tersedia'] }}</strong><small>Stok tersedia</small><div class="donut"></div>
    </div>
    @if(auth()->user()->role === 'admin')
        <div class="card summary-card">
            <span class="summary-label">Omzet hari ini</span><strong>{{ $cards['Omzet hari ini'] }}</strong><small>{{ $stats['daily_paid_orders'] }} transaksi lunas hari ini</small>
            <svg class="sparkline" viewBox="0 0 92 48" fill="none"><path d="M2 38L18 30L35 34L50 23L66 27L78 16L90 20" stroke="#9cff57" stroke-width="2"/><path d="M2 38L18 30L35 34L50 23L66 27L78 16L90 20V48H2Z" fill="#9cff57" opacity=".10"/><circle cx="78" cy="16" r="4" fill="#9cff57"/><circle cx="78" cy="16" r="7" stroke="#9cff57" opacity=".45"/></svg>
        </div>
    @endif
</div>

<div class="card dashboard-card dashboard-panel p-3 mb-3" id="stock-priorities" data-dashboard-panel="stock">
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

<section class="card dashboard-card dashboard-section dashboard-panel mb-3" id="products-panel" data-dashboard-panel="product-data">
    <div class="section-heading">
        <div>
            <h2>Data Produk</h2>
            <p>Lihat dan kelola katalog, harga, serta ketersediaan stok.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="section-count">{{ $products->count() }} produk</span>
            <a href="#add-product-panel" class="btn btn-sm btn-success dashboard-link" data-panel="product-create">+ Tambah Produk</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle text-white product-table mb-0">
            <thead>
                <tr><th>Produk</th><th>Jenis & Kategori</th><th>Harga</th><th>Stok</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                @php($unlimitedStock = $product->platform === 'Jasa' || (in_array($product->platform, ['PS3', 'PS4'], true) && strcasecmp(trim($product->category), 'Konsol') !== 0))
                <tr>
                    <td><span class="product-name">{{ $product->name }}</span></td>
                    <td>{{ $product->platform }}<span class="product-meta">{{ $product->category }}</span></td>
                    <td>{{ $product->platform === 'Jasa' ? 'Tanpa harga' : 'Rp '.number_format($product->price, 0, ',', '.') }}</td>
                    <td><span class="stock-pill">{{ $unlimitedStock ? 'Tak terbatas' : $product->stock.' unit' }}</span></td>
                    <td>
                        <div class="product-actions">
                            <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#editProduct{{ $product->id }}">Edit</button>
                            <form method="post" action="{{ route('products.delete', $product) }}" onsubmit="return confirm('Hapus {{ $product->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-4 text-center text-white-50">Belum ada produk. Pilih “Tambah Produk” untuk mulai mengisi katalog.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="card dashboard-card dashboard-section dashboard-panel mb-3" id="add-product-panel" data-dashboard-panel="product-create">
    <div class="section-heading">
        <div>
            <h2>Tambah Produk</h2>
            <p>Lengkapi detail produk baru. Kolom bertanda * wajib diisi.</p>
        </div>
    </div>
    <form method="post" action="{{ route('products.create') }}" enctype="multipart/form-data">
        @csrf
        <div class="product-form-grid">
            <div class="product-form-field full-width">
                <label class="form-label" for="create-name">Nama produk *</label>
                <input id="create-name" class="form-control" name="name" placeholder="Contoh: Controller DualShock 4" required>
            </div>
            <div class="product-form-field">
                <label class="form-label" for="create-platform">Jenis produk *</label>
                <select id="create-platform" name="platform" class="form-select" onchange="toggleProductFields(this.form,'create')">
                    <option value="PS3">PS3</option>
                    <option value="PS4">PS4</option>
                    <option value="Aksesoris">Aksesoris PS</option>
                    <option value="Jasa">Jasa Repair</option>
                </select>
            </div>
            <div class="product-form-field">
                <label class="form-label" for="create-category">Kategori *</label>
                <input id="create-category" name="category" class="form-control" placeholder="Contoh: Controller atau Konsol" oninput="toggleProductFields(this.form,'create')" required>
            </div>
            <div class="product-form-field" id="create-price">
                <label class="form-label" for="create-price-input">Harga (Rp) *</label>
                <input id="create-price-input" name="price" type="number" min="1" class="form-control" placeholder="Contoh: 250000" required>
            </div>
            <div class="product-form-field" id="create-stock">
                <label class="form-label" for="create-stock-input">Stok unit *</label>
                <input id="create-stock-input" name="stock" type="number" min="0" class="form-control" placeholder="Contoh: 10" style="display:none" disabled>
                <span data-unlimited-stock class="form-text">Stok tak terbatas untuk game dan jasa.</span>
            </div>
            <div class="product-form-field full-width">
                <label class="form-label" for="create-description">Deskripsi</label>
                <textarea id="create-description" name="description" class="form-control" rows="4" placeholder="Tambahkan informasi singkat tentang produk"></textarea>
            </div>
            <div class="product-form-field full-width">
                <label class="form-label" for="create-image">Foto produk</label>
                <input id="create-image" name="image" type="file" accept="image/*" class="form-control">
                <span class="form-text">Format gambar umum, ukuran maksimal 2 MB.</span>
            </div>
            <div class="product-form-field full-width product-form-actions">
                <button type="reset" class="btn btn-outline-light">Kosongkan</button>
                <button type="submit" class="btn btn-success">Simpan Produk</button>
            </div>
        </div>
    </form>
</section>

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

<div class="card dashboard-card dashboard-panel rounded-4 p-3 mt-3" id="orders" data-dashboard-panel="orders">
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
</main>
</div>

<div class="modal fade" id="aboutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">Tentang Rexel Games</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-white-50">
                Sistem dashboard untuk mengelola katalog produk, stok PlayStation, pesanan, dan akun karyawan.
                <div class="small mt-3">Rexel Games · {{ date('Y') }}</div>
            </div>
        </div>
    </div>
</div>

<script>
    // JS DASHBOARD: pindah panel, buka submenu setting, dan atur kolom harga/stok.
document.querySelectorAll('.dashboard-link').forEach(function(link) {
    link.addEventListener('click', function(event) {
        event.preventDefault();

        document.querySelectorAll('.dashboard-link').forEach(function(item) {
            item.classList.remove('active');
        });
        link.classList.add('active');

        document.querySelectorAll('[data-dashboard-panel]').forEach(function(panel) {
            panel.classList.remove('active');
        });

        if (link.dataset.panel !== 'overview') {
            const panel = document.querySelector('[data-dashboard-panel="' + link.dataset.panel + '"]');
            if (panel) panel.classList.add('active');
        }
    });
});

const settingsToggle = document.querySelector('.settings-toggle');
const settingsSubnav = document.getElementById('settings-subnav');
if (settingsToggle && settingsSubnav) {
    settingsToggle.addEventListener('click', function() {
        const isExpanded = settingsToggle.getAttribute('aria-expanded') === 'true';
        settingsToggle.setAttribute('aria-expanded', String(!isExpanded));
        settingsSubnav.classList.toggle('open', !isExpanded);
    });
}

const createProductForm = document.querySelector('#add-product-panel form');
if (createProductForm) {
    createProductForm.addEventListener('reset', function() {
        window.setTimeout(function() {
            toggleProductFields(createProductForm, 'create');
        }, 0);
    });
}

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
