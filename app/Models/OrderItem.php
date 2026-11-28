<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

// Model item pesanan: menyimpan detail produk yang dibeli beserta jumlah dan subtotal.
class OrderItem extends Model {
 public $timestamps=false; protected $fillable=['order_id','product_id','product_name','platform','quantity','unit_price','subtotal'];
}
