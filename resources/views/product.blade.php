@extends('layout')

@section('title', $product->name.' | Rexel Games')

@section('content')
@php($unlimitedStock = $product->platform === 'Jasa' || (in_array($product->platform, ['PS3', 'PS4'], true) && strcasecmp(trim($product->category), 'Konsol') !== 0))
<div class="row g-4">
    <div class="col-md-6">
        <img class="w-100 rounded-4" src="{{ $product->image ? asset('storage/'.$product->image) : 'https://placehold.co/800x600/172033/9cff57?text='.urlencode($product->platform) }}" alt="{{ $product->name }}">
    </div>
    <div class="col-md-6 align-self-center">
        <span class="badge badge-soft">{{ $product->platform }} · {{ $product->category }}</span>
        <h1 class="display-5 fw-bold mt-3">{{ $product->name }}</h1>
        <p class="text-muted">{{ $product->description ?: 'Pilihan Rexel Games.' }}</p>
        <h3 class="accent">{{ $product->platform === 'Jasa' ? 'Tanpa harga' : ($product->price ? 'Rp '.number_format($product->price, 0, ',', '.') : 'Harga belum diatur') }}</h3>
        <p>{{ $unlimitedStock ? 'Stok tak terbatas' : 'Stok tersedia: '.$product->stock }}</p>
        @if(($product->platform === 'Jasa' || ($product->price > 0 && ($unlimitedStock || $product->stock > 0))) && (session('guest') || auth()->check()))
            <form method="post" action="{{ route('cart.add', $product) }}">
                @csrf
                <button class="btn btn-success btn-lg">Tambah ke keranjang</button>
            </form>
        @endif
    </div>
</div>
@endsection
