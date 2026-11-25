<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users', function(Blueprint $t){$t->string('staff_id')->nullable()->unique();$t->string('role')->default('employee');$t->boolean('active')->default(true);});
  Schema::create('products',function(Blueprint $t){$t->id();$t->string('name');$t->string('platform',10);$t->string('category')->default('Action');$t->text('description')->nullable();$t->unsignedInteger('price')->default(0);$t->unsignedInteger('stock')->default(0);$t->string('image')->nullable();$t->timestamps();});
  Schema::create('orders',function(Blueprint $t){$t->id();$t->string('number')->unique();$t->string('customer_name');$t->string('whatsapp');$t->unsignedInteger('total');$t->string('payment_method');$t->string('payment_status')->default('Belum Dibayar');$t->string('status')->default('Menunggu');$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->timestamps();});
  Schema::create('order_items',function(Blueprint $t){$t->id();$t->foreignId('order_id')->constrained()->cascadeOnDelete();$t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();$t->string('product_name');$t->string('platform');$t->unsignedInteger('quantity');$t->unsignedInteger('unit_price');$t->unsignedInteger('subtotal');});
  Schema::create('stock_logs',function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->integer('before_stock');$t->integer('change');$t->integer('after_stock');$t->string('type');$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('stock_logs');Schema::dropIfExists('order_items');Schema::dropIfExists('orders');Schema::dropIfExists('products');Schema::table('users',function(Blueprint $t){$t->dropColumn(['staff_id','role','active']);});}
};
