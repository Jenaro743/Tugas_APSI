<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {
 protected $fillable=['number','customer_name','whatsapp','total','payment_method','payment_status','status','user_id'];
 protected $casts=['total'=>'integer'];
 public function items(){return $this->hasMany(OrderItem::class);}
}
