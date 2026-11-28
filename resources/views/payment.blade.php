@extends('layout')

@section('title', 'Pembayaran '.$order->number)

@section('content')
@php
    // PEMBAYARAN DEMO: buat nomor virtual account palsu untuk tampilan.
    $vaNumber = '8808'.str_pad((string)$order->id, 12, '0', STR_PAD_LEFT);
@endphp
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card rounded-4 p-4 p-md-5">
            <span class="badge badge-soft mb-2">PEMBAYARAN DEMO</span>
            <h1 class="h3 fw-bold text-white">Selesaikan pembayaran</h1>
            <p class="text-white-50">Pesanan <strong class="text-white">{{ $order->number }}</strong> menunggu pembayaran.</p>

            @if($order->payment_method === 'QRIS (DEMO)')
                {{-- QRIS DEMO: pola kotak acak hanya untuk simulasi visual. --}}
                <div class="text-center border border-secondary rounded-4 p-4 my-4">
                    <div class="d-inline-block bg-white p-3 rounded-3">
                        <svg width="240" height="240" viewBox="0 0 21 21" role="img" aria-label="QRIS palsu untuk demo">
                            <rect width="21" height="21" fill="white"/>
                            @for($y=0;$y<21;$y++)
                                @for($x=0;$x<21;$x++)
                                    @php
                                        $finder = ($x < 5 && $y < 5) || ($x > 15 && $y < 5) || ($x < 5 && $y > 15);
                                        $dark = $finder ? !(($x === 1 || $x === 3 || $x === 17 || $x === 19) && ($y === 1 || $y === 3 || $y === 17 || $y === 19)) : ((($x * 7 + $y * 11 + $order->id) % 5) < 2);
                                    @endphp
                                    @if($dark)<rect x="{{ $x }}" y="{{ $y }}" width="1" height="1" fill="#111827"/>@endif
                                @endfor
                            @endfor
                        </svg>
                    </div>
                    <h2 class="h6 text-white mt-3 mb-1">QRIS PALSU - DEMO</h2>
                    <p class="small text-white-50 mb-0">Kode ini tidak dapat digunakan untuk membayar di aplikasi perbankan nyata.</p>
                </div>
            @else
                <div class="border border-secondary rounded-4 p-4 my-4 text-center">
                    <span class="text-white-50">Nomor Virtual Account palsu</span>
                    <h2 class="accent fw-bold my-2" style="letter-spacing:.06em">{{ $vaNumber }}</h2>
                    <span class="badge bg-warning text-dark">DEMO - TIDAK AKTIF</span>
                    <p class="small text-white-50 mt-3 mb-0">Gunakan hanya sebagai simulasi tampilan. Nomor ini bukan nomor pembayaran bank.</p>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center border-top border-secondary pt-4">
                <div>
                    <span class="text-white-50">Total pembayaran</span>
                    <h3 class="accent mb-0">Rp {{ number_format($order->total, 0, ',', '.') }}</h3>
                </div>
                <a class="btn btn-success" href="{{ route('receipt', $order) }}">Lanjut ke struk</a>
            </div>
        </div>
    </div>
</div>
@endsection
