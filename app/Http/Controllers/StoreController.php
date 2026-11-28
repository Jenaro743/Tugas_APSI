<?php
namespace App\Http\Controllers;

use App\Models\{Order,OrderItem,Product,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Hash};
use Illuminate\Validation\Rule;

// Controller utama aplikasi toko: katalog, keranjang, checkout, dan dashboard.
class StoreController extends Controller
{
 // KATALOG: cari produk, lalu pisahkan konsol, inventaris, dan jasa.
 public function home(Request $r){
  $base=Product::query();
  if($r->filled('q'))$base->where('name','like','%'.$r->q.'%');
  if($r->filled('max'))$base->where('price','<=',$r->integer('max'));
  $inventory=(clone $base)->where(function($query){$query->where(function($query){$query->whereIn('platform',['PS3','PS4'])->where('category','!=','Konsol');})->orWhere('platform','Aksesoris');})->orderBy('platform')->orderBy('category')->orderBy('name')->paginate(12,['*'],'inventory_page');
  $consoles=(clone $base)->whereIn('platform',['PS3','PS4'])->where('category','Konsol')->orderBy('platform')->get();
  $services=(clone $base)->where('platform','Jasa')->orderBy('name')->paginate(12,['*'],'service_page');
  return view('store',['inventory'=>$inventory,'consoles'=>$consoles,'services'=>$services]);
 }
 // PRIORITAS STOK: hitung ranking aksesoris dari stok rendah dan jumlah penjualan.
 private function stockPriorities(){
  $products=Product::query()->where('platform','Aksesoris')->orderBy('category')->orderBy('name')->get();
  $sales=collect();
  if($products->isNotEmpty()){
   $sales=DB::table('order_items')
    ->join('orders','orders.id','=','order_items.order_id')
    ->whereIn('order_items.product_id',$products->modelKeys())
    ->where('orders.payment_status','Lunas')
    ->where('orders.status','!=','Dibatalkan')
    ->groupBy('order_items.product_id')
    ->select('order_items.product_id',DB::raw('SUM(order_items.quantity) as units_sold'))
    ->pluck('units_sold','product_id');
  }
  $minPositiveStock=$products->where('stock','>',0)->min('stock');
  $maxSales=$sales->max() ?? 0;
  return $products->map(function($product) use($sales,$minPositiveStock,$maxSales){
   $unitsSold=(int)($sales[$product->id] ?? 0);
   $stockScore=$product->stock===0 ? 1 : $minPositiveStock/$product->stock;
   $salesScore=$maxSales>0 ? $unitsSold/$maxSales : 0;
   $product->units_sold=$unitsSold;
   $product->saw_score=round(($stockScore*0.6)+($salesScore*0.4),4);
   return $product;
  })->sortByDesc('saw_score')->values();
 }
 // ATURAN STOK: game, jasa, dan aksesoris punya aturan ketersediaan berbeda.
 private function hasUnlimitedStock(Product $product): bool {
  return $product->platform==='Jasa'||(in_array($product->platform,['PS3','PS4'],true)&&strcasecmp(trim($product->category),'Konsol')!==0);
 }
 public function product(Product $product){return view('product',['product'=>$product]);}
 public function cartGuest(){abort_unless(session('guest')||Auth::check(),403);return view('customer',['orders'=>collect()]);}
 public function guest(Request $r){$r->session()->regenerate();$r->session()->put('guest',true);return redirect()->route('home');}
 public function exitGuest(Request $r){$r->session()->forget(['guest','cart']);$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('home');}
 public function login(){return view('login');}
 public function authenticate(Request $r){$data=$r->validate(['staff_id'=>'required|string','password'=>'required|string']);$u=User::where(fn($q)=>$q->where('staff_id',$data['staff_id'])->orWhere('email',$data['staff_id']))->where('active',true)->first();if(!$u||!Hash::check($data['password'],$u->password))return back()->withErrors(['staff_id'=>'ID, email, atau password tidak sesuai.'])->onlyInput('staff_id');Auth::login($u);$r->session()->regenerate();return redirect()->route('dashboard');}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('home');}
 // DASHBOARD: tampilkan data sesuai role: tamu, pelanggan, karyawan, atau admin.
 public function dashboard(){ $u=Auth::user();if(!$u&&session('guest'))return view('customer',['orders'=>collect()]);abort_unless($u,403);if($u->role==='customer')return view('customer',['orders'=>Order::where('user_id',$u->id)->latest()->get()]);abort_unless(in_array($u->role,['admin','employee']),403);$orders=Order::with('items')->latest()->paginate(15);$availableStock=Product::where('platform','Aksesoris')->orWhere(function($query){$query->whereIn('platform',['PS3','PS4'])->where('category','Konsol');})->sum('stock');$stats=['products'=>Product::count(),'stock'=>$availableStock];if($u->role==='admin'){$paidToday=Order::whereDate('created_at',today())->where('payment_status','Lunas')->where('status','!=','Dibatalkan');$stats['daily_sales']=(clone $paidToday)->sum('total');$stats['daily_paid_orders']=$paidToday->count();}return view('staff',['orders'=>$orders,'products'=>Product::orderBy('name')->get(),'stockPriorities'=>$this->stockPriorities(),'stats'=>$stats]);}
 // KERANJANG: tambah satu item dan cegah jumlah melebihi stok.
 public function addCart(Request $r,Product $product){
  abort_unless(session('guest')||Auth::check(),403);
  $service=$product->platform==='Jasa';
  $unlimitedStock=$this->hasUnlimitedStock($product);
  if(!$service&&($product->price<1||(!$unlimitedStock&&$product->stock<1)))return back()->withErrors(['cart'=>'Barang tidak tersedia untuk dibeli.']);
  $cart=session('cart',[]);
  $qty=($cart[$product->id]??0)+1;
  if(!$unlimitedStock&&$qty>$product->stock)return back()->withErrors(['cart'=>'Jumlah melebihi stok.']);
  $cart[$product->id]=$qty;
  $r->session()->put('cart',$cart);
  return back()->with('ok','Ditambahkan ke keranjang.');
 }
 public function updateCart(Request $r,Product $product){
  $d=$r->validate(['quantity'=>'required|integer|min:1']);
  if(!$this->hasUnlimitedStock($product))abort_if($d['quantity']>$product->stock,422);
  $c=session('cart',[]);
  $c[$product->id]=$d['quantity'];
  $r->session()->put('cart',$c);
  return back();
 }
 public function removeCart(Request $r,Product $product){$c=session('cart',[]);unset($c[$product->id]);$r->session()->put('cart',$c);return back();}
 // CHECKOUT: validasi data pembeli, kurangi stok, lalu buat pesanan dan item pesanan.
 public function checkout(Request $r){
  abort_unless(session('guest')||Auth::check(),403);
  $d=$r->validate(['customer_name'=>'required|string|max:120','whatsapp'=>'required|string|max:30','payment_method'=>['required',Rule::in(['COD','Virtual Account (DEMO)','QRIS (DEMO)'])]]);
  $cart=session('cart',[]);
  abort_if(!$cart,422,'Keranjang kosong');
  $order=DB::transaction(function()use($cart,$d,$r){
   $items=[];
   $total=0;
   foreach($cart as $id=>$qty){
    $p=Product::whereKey($id)->lockForUpdate()->firstOrFail();
    $service=$p->platform==='Jasa';
    $unlimitedStock=$this->hasUnlimitedStock($p);
    if(!$service)abort_if((!$unlimitedStock&&$qty>$p->stock)||$p->price<1,422,'Stok atau harga berubah.');
    if(!$unlimitedStock)$p->decrement('stock',$qty);
    $sub=$service?0:$p->price*$qty;
    $total+=$sub;
    $items[]=[$p,$qty,$sub];
   }
   $o=Order::create(['number'=>'RX-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)),'customer_name'=>$d['customer_name'],'whatsapp'=>$d['whatsapp'],'total'=>$total,'payment_method'=>$d['payment_method'],'payment_status'=>'Belum Dibayar','status'=>'Menunggu','user_id'=>Auth::id()]);
   foreach($items as [$p,$qty,$sub]){
    $unitPrice=$p->platform==='Jasa'?0:$p->price;
    OrderItem::create(['order_id'=>$o->id,'product_id'=>$p->id,'product_name'=>$p->name,'platform'=>$p->platform,'quantity'=>$qty,'unit_price'=>$unitPrice,'subtotal'=>$sub]);
   }
   return $o;
  });
  $r->session()->forget('cart');
  if(in_array($d['payment_method'],['Virtual Account (DEMO)','QRIS (DEMO)'],true))return redirect()->route('payment',$order);
  return redirect()->route('receipt',$order);
 }
 public function payment(Order $order){$order->load('items');return view('payment',['order'=>$order]);}
 public function receipt(Order $order){if(Auth::check()&&Auth::user()->role==='customer')abort_unless($order->user_id===Auth::id(),403);$order->load('items');return view('receipt',['order'=>$order,'printMode'=>false]);}
 public function printReceipt(Order $order){abort_unless($order->payment_status==='Lunas',403);$order->load('items');return view('receipt',['order'=>$order,'printMode'=>true]);}
 // PRODUK BARU: tentukan aturan harga/stok berdasarkan jenis dan kategori.
 public function createProduct(Request $r){
  $isPlaystation=in_array($r->input('platform'),['PS3','PS4'],true);
  $isConsole=$isPlaystation&&strcasecmp(trim($r->input('category','')),'Konsol')===0;
  $isGame=$isPlaystation&&!$isConsole;
  $d=$r->validate(['name'=>'required|string|max:150','platform'=>['required',Rule::in(['PS3','PS4','Aksesoris','Jasa'])],'category'=>'required|string|max:80','price'=>$isGame?'required|integer|min:1':'nullable|integer|min:0','stock'=>'nullable|integer|min:0','description'=>'nullable|string','image'=>'nullable|image|max:2048']);
  if($d['platform']==='Jasa'){
   $d['price']=0;
   $d['stock']=2147483647;
  }elseif($isConsole){
   $d['category']='Konsol';
   if(!isset($d['price'])||!isset($d['stock']))return back()->withErrors(['stock'=>'Harga dan stok konsol wajib diisi.']);
  }elseif($isGame){
   $d['stock']=2147483647;
  }elseif(!isset($d['price'])||!isset($d['stock'])){
   return back()->withErrors(['price'=>'Harga dan stok wajib diisi untuk produk.']);
  }
  if($r->hasFile('image'))$d['image']=$r->file('image')->store('products','public');
  Product::create($d);
  return back()->with('ok','Produk disimpan.');
 }
 public function editProduct(Request $r,Product $product){
  $isPlaystation=in_array($r->input('platform'),['PS3','PS4'],true);
  $isConsole=$isPlaystation&&strcasecmp(trim($r->input('category','')),'Konsol')===0;
  $isGame=$isPlaystation&&!$isConsole;
  $d=$r->validate(['name'=>'required|string|max:150','platform'=>['required',Rule::in(['PS3','PS4','Aksesoris','Jasa'])],'category'=>'required|string|max:80','price'=>$isGame?'required|integer|min:1':'nullable|integer|min:0','stock'=>'nullable|integer|min:0','description'=>'nullable|string','image'=>'nullable|image|max:2048']);
  if($d['platform']==='Jasa'){
   $d['price']=0;
   $d['stock']=2147483647;
  }elseif($isConsole){
   $d['category']='Konsol';
   if(!isset($d['price'])||!isset($d['stock']))return back()->withErrors(['stock'=>'Harga dan stok konsol wajib diisi.']);
  }elseif($isGame){
   $d['stock']=2147483647;
  }elseif(!isset($d['price'])||!isset($d['stock'])){
   return back()->withErrors(['price'=>'Harga dan stok wajib diisi untuk produk.']);
  }
  if($r->hasFile('image'))$d['image']=$r->file('image')->store('products','public');
  $product->update($d);
  return back()->with('ok','Produk diperbarui.');
 }
 public function deleteProduct(Product $product){$product->delete();return back()->with('ok','Produk atau jasa berhasil dihapus.');}
 public function stock(Request $r,Product $product){abort_unless($product->platform==='Aksesoris',404);$d=$r->validate(['change'=>'required|integer|not_in:0']);DB::transaction(function()use($product,$d,$r){$p=Product::whereKey($product->id)->lockForUpdate()->first();$after=$p->stock+$d['change'];abort_if($after<0,422,'Stok tidak boleh negatif.');$p->update(['stock'=>$after]);DB::table('stock_logs')->insert(['product_id'=>$p->id,'user_id'=>Auth::id(),'before_stock'=>$p->stock-$d['change'],'change'=>$d['change'],'after_stock'=>$after,'type'=>$d['change']>0?'Penambahan':'Pengurangan','created_at'=>now(),'updated_at'=>now()]);});return back()->with('ok','Stok diperbarui.');}
 public function orderStatus(Request $r,Order $order){$d=$r->validate(['status'=>['required',Rule::in(['Diproses','Siap Diambil','Selesai','Dibatalkan'])],'payment_status'=>['nullable',Rule::in(['Lunas'])]]);if(isset($d['payment_status'])&&!in_array($order->payment_method,['COD','Virtual Account (DEMO)','QRIS (DEMO)'],true))abort(422,'Metode pembayaran tidak mendukung verifikasi manual.');$order->update(['status'=>$d['status'],'payment_status'=>$d['payment_status']??$order->payment_status]);return back()->with('ok','Pesanan diperbarui.');}
}
