@extends('layout')
@section('content')
<div class="d-flex justify-content-between">
    <div><span class="accent fw-bold">DASHBOARD PELANGGAN</span><h1 class="fw-bold text-white">Pesanan saya</h1></div>
    <a class="btn btn-success align-self-center" href="{{ route('home') }}">Belanja</a>
</div>
<div class="card rounded-4 p-3 mt-3">
    <h4 class="text-white">Riwayat transaksi</h4>
    @forelse($orders as $order)
        <div class="d-flex justify-content-between border-bottom border-secondary py-3">
            <div><strong class="text-white">{{ $order->number }}</strong><div class="text-white-50">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->status }} · {{ $order->payment_status }}</div></div>
            <a href="{{ route('receipt',$order) }}" class="btn btn-sm btn-outline-light align-self-center">Lihat struk</a>
        </div>
    @empty
        <p class="text-white-50">Belum ada pesanan. Pesanan tamu dilacak dengan nomor struk.</p>
    @endforelse
</div>
<div class="card rounded-4 p-3 mt-4">
    <h4 class="text-white">Keranjang</h4>
    @php($cart=session('cart',[]))
    @php($sum=0)
    @foreach($cart as $id=>$qty)
        @php($product=\App\Models\Product::find($id))
        @if($product)
            @php($unlimitedStock = $product->platform === 'Jasa' || (in_array($product->platform, ['PS3', 'PS4'], true) && strcasecmp(trim($product->category), 'Konsol') !== 0))
            @php($sum += $product->platform==='Jasa' ? 0 : $product->price*$qty)
            <div class="d-flex align-items-center gap-3 border-bottom border-secondary py-2">
                <div class="flex-grow-1 text-white">
                    {{ $product->name }}
                    <span class="text-white-50">· {{ $product->platform==='Jasa' ? 'Jasa · Tanpa harga' : 'Rp '.number_format($product->price,0,',','.') }}</span>
                    <small class="text-white-50">{{ $unlimitedStock ? '· Stok tak terbatas' : '· Stok '.$product->stock }}</small>
                </div>
                <form method="post" action="{{ route('cart.update',$product) }}" class="d-flex gap-2">
                    @csrf @method('PATCH')
                    <input class="form-control" style="width:86px" name="quantity" type="number" min="1" @if(!$unlimitedStock) max="{{ $product->stock }}" @endif value="{{ $qty }}">
                    <button class="btn btn-sm btn-outline-light">Ubah</button>
                </form>
                <form method="post" action="{{ route('cart.remove',$product) }}">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </div>
        @endif
    @endforeach
    @if($cart)
        <div class="text-end fw-bold fs-5 my-3 text-white">Total: Rp {{ number_format($sum,0,',','.') }}</div>
        <form method="post" action="{{ route('checkout') }}" class="row g-2">
            @csrf
            <div class="col-md-4"><input class="form-control" name="customer_name" placeholder="Nama pembeli" required></div>
            <div class="col-md-3"><input class="form-control" name="whatsapp" placeholder="Nomor WhatsApp" required></div>
            <div class="col-md-3"><select class="form-select" name="payment_method"><option>COD</option><option>Virtual Account (DEMO)</option><option>QRIS (DEMO)</option></select></div>
            <div class="col-md-2"><button class="btn btn-success w-100">Checkout</button></div>
        </form>
        <small class="text-white-50">VA dan QRIS menampilkan kode demo sebelum struk. Pembayaran demo dapat ditandai lunas oleh staf.</small>
    @else
        <p class="text-white-50">Keranjang masih kosong.</p>
    @endif
</div>
@endsection
