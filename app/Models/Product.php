<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

// Model tabel produk: menyimpan nama, jenis, harga, stok, dan gambar.
class Product extends Model {
 protected $fillable=['name','platform','category','description','price','stock','image'];
 protected $casts=['price'=>'integer','stock'=>'integer'];
 public function items(){return $this->hasMany(OrderItem::class);}
}
