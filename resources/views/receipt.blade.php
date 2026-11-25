@extends('layout')

@section('title', 'Struk '.$order->number)

@section('content')
@if($printMode)
    <style>
        @media print {
            .navbar, .footer, .receipt-actions { display: none !important; }
            body, .receipt-card { background: #fff !important; }
            .receipt-card, .receipt-card * { color: #111 !important; }
            .receipt-card { border: 0 !important; box-shadow: none !important; }
            .receipt-card .table,
            .receipt-card .table th,
            .receipt-card .table td { background: #fff !important; color: #111 !important; border-color: #adb5bd !important; }
        }
    </style>
@else
    <style>
        .print-restricted-notice { display: none; }
        @media print {
            .receipt-view-only { display: none !important; }
            .print-restricted-notice { display: block !important; }
        }
    </style>
@endif

<div class="row justify-content-center {{ $printMode ? 'receipt-printable' : 'receipt-view-only' }}">
    <div class="col-md-8">
        <div class="card receipt-card rounded-4 p-4 p-md-5">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="accent fw-bold">REXEL GAMES</div>
                    <h2 class="fw-bold">Struk pembelian</h2>
                </div>
                <span class="pill align-self-start">{{ $order->number }}</span>
            </div>
            <hr>
            <div class="row text-muted">
                <div class="col">Pembeli<br><strong class="text-white">{{ $order->customer_name }}</strong></div>
                <div class="col">WhatsApp<br><strong class="text-white">{{ $order->whatsapp }}</strong></div>
                <div class="col">Tanggal<br><strong class="text-white">{{ $order->created_at->format('d/m/Y H:i') }}</strong></div>
            </div>
            <div class="table-responsive mt-4">
                <table class="table">
                    <thead>
                        <tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }} <small class="text-muted">{{ $item->platform }}</small></td>
                                <td>{{ $item->quantity }}</td>
                                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col text-muted">
                    Metode: {{ $order->payment_method }}<br>
                    Pembayaran: {{ $order->payment_status }}<br>
                    Status: {{ $order->status }}
                </div>
                <div class="col text-end">
                    <span class="text-muted">Total pembayaran</span>
                    <h3 class="accent">Rp {{ number_format($order->total, 0, ',', '.') }}</h3>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4 receipt-actions">
                @if($printMode)
                    <button onclick="window.print()" class="btn btn-success">Cetak struk</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light">Kembali ke dashboard</a>
                @else
                    <a href="{{ route('home') }}" class="btn btn-outline-light">Kembali ke katalog</a>
                @endif
            </div>
        </div>
    </div>
</div>
@unless($printMode)
    <p class="print-restricted-notice">Struk hanya dapat dicetak oleh admin setelah pembayaran lunas.</p>
@endunless
@endsection
