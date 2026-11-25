@extends('layout')

@section('title', 'Katalog | Rexel Games')

@section('content')
<section class="hero mb-5">
    <span class="pill">PLAY YOUR WAY · PS3, PS4 & AKSESORIS</span>
    <h1 class="display-5 fw-bold mt-3 text-white">Game, aksesoris, dan jasa<br><span class="accent">PlayStation lengkap.</span></h1>
    <p class="text-white-50 fs-5 mt-3">Satu tempat untuk membeli game, perlengkapan PS, dan memperbaiki konsol atau stik.</p>
    <a class="btn btn-success btn-lg mt-3" href="#catalog">Jelajahi katalog →</a>
</section>

<form class="row g-2 mb-5">
    <div class="col-md-8"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari game, aksesoris, atau jasa..."></div>
    <div class="col-md-2"><input class="form-control" name="max" type="number" min="0" placeholder="Harga maksimal"></div>
    <div class="col-md-2"><button class="btn btn-success w-100">Cari</button></div>
</form>

<div id="catalog"></div>

<section class="mb-5">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <div class="accent small fw-bold">Konsol PS3 & PS4</div>
            <h2 class="fw-bold mb-0 text-white">Katalog PlayStation</h2>
        </div>
        <span class="text-white-50">{{ $consoles->count() }} tipe</span>
    </div>
    <div class="row g-3">
        @forelse($consoles as $console)
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100 rounded-4 overflow-hidden">
                    <img class="w-100 product-img" src="{{ $console->image ? asset('storage/'.$console->image) : 'https://placehold.co/600x400/172033/9cff57?text='.urlencode($console->platform) }}" alt="{{ $console->name }}">
                    <div class="card-body d-flex flex-column">
                        <span class="badge badge-soft align-self-start">{{ $console->platform }}</span>
                        <h5 class="mt-2 fw-bold text-white">{{ $console->name }}</h5>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="accent">Rp {{ number_format($console->price, 0, ',', '.') }}</strong>
                                <small class="text-white-50">Stok {{ $console->stock }} unit</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a class="btn btn-sm btn-outline-light flex-fill" href="{{ route('product', $console) }}">Detail</a>
                                @if($console->price > 0 && $console->stock > 0 && (session('guest') || auth()->check()))
                                    <form method="post" action="{{ route('cart.add', $console) }}" class="flex-fill">
                                        @csrf
                                        <button class="btn btn-sm btn-success w-100">+ Keranjang</button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-secondary flex-fill" disabled>{{ $console->stock < 1 || $console->price < 1 ? 'Tidak tersedia' : 'Pilih tamu' }}</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col"><div class="card p-5 text-center text-white-50">Belum ada stok konsol PlayStation.</div></div>
        @endforelse
    </div>
</section>

@foreach([
    'inventory' => ['title' => 'Katalog Game & Aksesoris', 'accent' => 'Game PS3 & PS4 · Perlengkapan & Sparepart PS', 'empty' => 'Belum ada game atau aksesoris cocok dengan pencarian.', 'items' => $inventory],
    'services' => ['title' => 'Katalog Jasa', 'accent' => 'Perbaikan & Perawatan PlayStation', 'empty' => 'Belum ada jasa cocok dengan pencarian.', 'items' => $services],
] as $key => $section)
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <div class="accent small fw-bold">{{ $section['accent'] }}</div>
                <h2 class="fw-bold mb-0 text-white">{{ $section['title'] }}</h2>
            </div>
            <span class="text-white-50">{{ $section['items']->total() }} item</span>
        </div>
        <div class="row g-3">
            @forelse($section['items'] as $product)
                @php($unlimitedStock = $product->platform === 'Jasa' || (in_array($product->platform, ['PS3', 'PS4'], true) && strcasecmp(trim($product->category), 'Konsol') !== 0))
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="card h-100 rounded-4 overflow-hidden">
                        <img class="w-100 product-img" src="{{ $product->image ? asset('storage/'.$product->image) : 'https://placehold.co/600x400/172033/9cff57?text='.urlencode($product->platform) }}" alt="{{ $product->name }}">
                        <div class="card-body d-flex flex-column">
                            <div>
                                <span class="badge badge-soft">{{ $product->platform }}</span>
                                <span class="text-white-50 small ms-2">{{ $product->category }}</span>
                            </div>
                            <h5 class="mt-2 fw-bold text-white">{{ $product->name }}</h5>
                            <p class="text-white-50 small">{{ Str::limit($product->description ?: 'Pilihan Rexel Games.', 70) }}</p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between mb-3">
                                    <strong class="accent">{{ $product->platform === 'Jasa' ? 'Tanpa harga' : ($product->price ? 'Rp '.number_format($product->price, 0, ',', '.') : 'Harga belum diatur') }}</strong>
                                    <small class="text-white-50">{{ $unlimitedStock ? 'Stok tak terbatas' : 'Stok '.$product->stock }}</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-sm btn-outline-light flex-fill" href="{{ route('product', $product) }}">Detail</a>
                                    @if(($product->platform === 'Jasa' || ($product->price > 0 && ($unlimitedStock || $product->stock > 0))) && (session('guest') || auth()->check()))
                                        <form method="post" action="{{ route('cart.add', $product) }}" class="flex-fill">
                                            @csrf
                                            <button class="btn btn-sm btn-success w-100">+ Keranjang</button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary flex-fill" disabled>{{ (!$unlimitedStock && $product->stock < 1) || ($product->platform !== 'Jasa' && $product->price < 1) ? 'Tidak tersedia' : 'Pilih tamu' }}</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col"><div class="card p-5 text-center text-white-50">{{ $section['empty'] }}</div></div>
            @endforelse
        </div>
        @if($section['items']->hasPages())
            <div class="mt-4">{{ $section['items']->withQueryString()->links() }}</div>
        @endif
    </section>
@endforeach
@endsection
