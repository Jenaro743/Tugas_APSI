<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        foreach (range(1, 17) as $number) {
            Product::create([
                'name' => "Game $number",
                'platform' => 'PS4',
                'category' => 'Action',
                'price' => 10000,
                'stock' => 1,
            ]);
        }

        $response = $this->get('/');

        $response->assertOk()->assertSee('Next');
    }

    public function test_a_guest_can_checkout_an_available_product(): void
    {
        $product = Product::create([
            'name' => 'Game Test',
            'platform' => 'Aksesoris',
            'category' => 'Action',
            'price' => 12000,
            'stock' => 2,
        ]);

        $this->post(route('guest'))->assertRedirect(route('home'));
        $this->post(route('cart.add', $product))->assertRedirect();

        $response = $this->post(route('checkout'), [
            'customer_name' => 'Pelanggan Test',
            'whatsapp' => '08123456789',
            'payment_method' => 'COD',
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('receipt', $order));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'total' => 12000,
            'payment_method' => 'COD',
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1,
        ]);
    }

    public function test_seeded_games_have_demo_prices_and_unlimited_stock(): void
    {
        $this->seed(DatabaseSeeder::class);

        $games = Product::whereIn('platform', ['PS3', 'PS4'])
            ->where('category', '!=', 'Konsol')
            ->get();

        $this->assertCount(40, $games);
        $this->assertSame(0, $games->filter(
            fn (Product $game) => $game->price !== 45000 || $game->stock !== 2147483647,
        )->count());

        $product = $games->firstOrFail();
        $this->post(route('guest'))->assertRedirect(route('home'));
        $this->post(route('cart.add', $product))->assertRedirect();
        $this->assertSame(1, session('cart')[$product->id]);
    }

    public function test_playstation_catalog_lists_only_consoles_with_available_stock(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Katalog PlayStation')
            ->assertSee('PlayStation 3')
            ->assertSee('Stok 6 unit')
            ->assertSee('Rp 2.500.000')
            ->assertSee('PlayStation 4')
            ->assertSee('Rp 4.800.000');

        $consoles = Product::where('category', 'Konsol')->get();
        $this->assertCount(2, $consoles);
        $this->assertSame([6, 6], $consoles->sortBy('platform')->pluck('stock')->values()->all());
        $this->assertSame([2500000, 4800000], $consoles->sortBy('platform')->pluck('price')->values()->all());
    }

    public function test_console_purchase_decrements_limited_stock(): void
    {
        $this->seed(DatabaseSeeder::class);
        $console = Product::where('name', 'PlayStation 3')->firstOrFail();

        $this->post(route('guest'))->assertRedirect(route('home'));
        foreach (range(1, 6) as $quantity) {
            $this->post(route('cart.add', $console))->assertRedirect();
        }
        $this->post(route('cart.add', $console))->assertSessionHasErrors('cart');
        $this->post(route('checkout'), [
            'customer_name' => 'Pelanggan Test',
            'whatsapp' => '08123456789',
            'payment_method' => 'COD',
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'total' => 15000000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $console->id,
            'quantity' => 6,
            'subtotal' => 15000000,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $console->id,
            'stock' => 0,
        ]);
    }

    public function test_games_can_be_added_repeatedly_and_do_not_consume_stock(): void
    {
        $game = Product::create([
            'name' => 'Game Tak Terbatas',
            'platform' => 'PS4',
            'category' => 'Action',
            'price' => 45000,
            'stock' => 1,
        ]);
        $checkout = [
            'customer_name' => 'Pelanggan Test',
            'whatsapp' => '08123456789',
            'payment_method' => 'COD',
        ];

        $this->post(route('guest'))->assertRedirect(route('home'));
        $this->post(route('cart.add', $game))->assertRedirect();
        $this->post(route('cart.add', $game))->assertRedirect();
        $this->patch(route('cart.update', $game), ['quantity' => 3])->assertRedirect();
        $this->assertSame(3, session('cart')[$game->id]);

        $this->post(route('checkout'), $checkout)->assertRedirect();
        $firstOrder = Order::firstOrFail();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $firstOrder->id,
            'product_id' => $game->id,
            'quantity' => 3,
            'subtotal' => 135000,
        ]);
        $this->assertDatabaseHas('products', ['id' => $game->id, 'stock' => 1]);

        $this->post(route('cart.add', $game))->assertRedirect();
        $this->post(route('checkout'), $checkout)->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $game->id, 'stock' => 1]);
        $this->assertSame(2, Order::count());
    }

    public function test_new_game_products_are_saved_with_unlimited_stock(): void
    {
        $admin = User::create([
            'name' => 'Admin Game',
            'email' => 'admin-game@example.test',
            'staff_id' => 'ADMINGAME',
            'password' => 'TestPassword123!',
            'role' => 'admin',
            'active' => true,
        ]);

        $this->actingAs($admin)->post(route('products.create'), [
            'name' => 'Game Baru',
            'platform' => 'PS3',
            'category' => 'Action',
            'price' => 45000,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'name' => 'Game Baru',
            'platform' => 'PS3',
            'price' => 45000,
            'stock' => 2147483647,
        ]);
    }

    public function test_staff_dashboard_ranks_stock_priorities_for_games_and_accessories(): void
    {
        $employee = User::create([
            'name' => 'Karyawan Test',
            'email' => 'karyawan-saw@example.test',
            'staff_id' => 'KRYSAW',
            'password' => 'TestPassword123!',
            'role' => 'employee',
            'active' => true,
        ]);
        $outOfStock = Product::create([
            'name' => 'Z Aksesoris Habis',
            'platform' => 'Aksesoris',
            'category' => 'Controller',
            'price' => 20000,
            'stock' => 0,
        ]);
        Product::create([
            'name' => 'B Game',
            'platform' => 'PS3',
            'category' => 'Action',
            'price' => 15000,
            'stock' => 5,
        ]);
        Product::create([
            'name' => 'A Aksesoris Stok Rendah',
            'platform' => 'Aksesoris',
            'category' => 'Controller',
            'price' => 25000,
            'stock' => 5,
        ]);
        Product::create([
            'name' => 'C Aksesoris Stok Aman',
            'platform' => 'Aksesoris',
            'category' => 'Controller',
            'price' => 30000,
            'stock' => 20,
        ]);
        $game = Product::where('name', 'B Game')->firstOrFail();
        $service = Product::create([
            'name' => 'Jasa Repair',
            'platform' => 'Jasa',
            'category' => 'Repair',
            'price' => 0,
            'stock' => 2147483647,
        ]);

        $paidOrder = Order::create([
            'number' => 'RX-PAID-TEST',
            'customer_name' => 'Pelanggan Test',
            'whatsapp' => '08123456789',
            'total' => 100000,
            'payment_method' => 'COD',
            'payment_status' => 'Lunas',
            'status' => 'Selesai',
        ]);
        OrderItem::create([
            'order_id' => $paidOrder->id,
            'product_id' => $outOfStock->id,
            'product_name' => $outOfStock->name,
            'platform' => $outOfStock->platform,
            'quantity' => 5,
            'unit_price' => $outOfStock->price,
            'subtotal' => 100000,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Katalog Game & Aksesoris')
            ->assertDontSee('Rekomendasi Game (SAW)');
        $this->get('/recommendations')->assertNotFound();

        $response = $this->actingAs($employee)->get(route('dashboard'));
        $response
            ->assertOk()
            ->assertSee('Stok tersedia')
            ->assertSee('37 unit')
            ->assertSee('Prioritas Pembaruan Stok (SAW)')
            ->assertSee('Data Produk')
            ->assertSee('Tambah Produk')
            ->assertSee('product-data', false)
            ->assertSee('product-create', false)
            ->assertSee('stok saat ini (cost) 60%')
            ->assertSee('penjualan lunas (benefit) 40%')
            ->assertSee('Z Aksesoris Habis')
            ->assertSee('A Aksesoris Stok Rendah')
            ->assertSee('C Aksesoris Stok Aman');

        preg_match('/<h4 class="text-white">Prioritas Pembaruan Stok \(SAW\)<\/h4>(.*?)<\/table>/s', $response->getContent(), $priorityTable);
        $this->assertCount(2, $priorityTable);
        $this->assertStringNotContainsString('Jasa Repair', $priorityTable[1]);
        $this->assertStringNotContainsString('B Game', $priorityTable[1]);
        $this->assertLessThan(
            strpos($priorityTable[1], 'A Aksesoris Stok Rendah'),
            strpos($priorityTable[1], 'Z Aksesoris Habis'),
        );
        $this->assertLessThan(
            strpos($priorityTable[1], 'C Aksesoris Stok Aman'),
            strpos($priorityTable[1], 'A Aksesoris Stok Rendah'),
        );

        $this->actingAs($employee)
            ->post(route('products.stock', $outOfStock), ['change' => 5])
            ->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $outOfStock->id, 'stock' => 5]);
        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $outOfStock->id,
            'before_stock' => 0,
            'change' => 5,
            'after_stock' => 5,
        ]);
        $this->actingAs($employee)
            ->post(route('products.stock', $service), ['change' => 1])
            ->assertNotFound();
        $this->actingAs($employee)
            ->post(route('products.stock', $game), ['change' => 1])
            ->assertNotFound();

        $customer = User::create([
            'name' => 'Pelanggan Test',
            'email' => 'pelanggan-saw@example.test',
            'staff_id' => 'CUSSAW',
            'password' => 'TestPassword123!',
            'role' => 'customer',
            'active' => true,
        ]);
        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Prioritas Pembaruan Stok (SAW)');
    }

    public function test_staff_can_sign_in_with_email_and_demo_accounts_are_shown(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.test',
            'staff_id' => 'ADMIN001',
            'password' => 'TestPassword123!',
            'role' => 'admin',
            'active' => true,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('ADMIN001')
            ->assertSee('admin@rexel.local')
            ->assertSee('KRY001')
            ->assertSee('karyawan@rexel.local');

        $this->post(route('login.post'), [
            'staff_id' => $user->email,
            'password' => 'TestPassword123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_employee_cannot_see_revenue_but_can_edit_product(): void
    {
        $employee = User::create([
            'name' => 'Karyawan Test',
            'email' => 'karyawan@example.test',
            'staff_id' => 'KRYTEST',
            'password' => 'TestPassword123!',
            'role' => 'employee',
            'active' => true,
        ]);
        $product = Product::create([
            'name' => 'Nama Lama',
            'platform' => 'PS4',
            'category' => 'Action',
            'description' => 'Deskripsi lama',
            'price' => 10000,
            'stock' => 2,
        ]);

        $this->actingAs($employee)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Omzet lunas');

        $this->actingAs($employee)->put(route('products.edit', $product), [
            'name' => 'Nama Baru',
            'platform' => 'Aksesoris',
            'category' => 'Stik / Controller',
            'price' => 25000,
            'stock' => 5,
            'description' => 'Deskripsi baru',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nama Baru',
            'platform' => 'Aksesoris',
            'price' => 25000,
            'stock' => 5,
        ]);
    }

    public function test_only_admin_can_print_paid_receipts(): void
    {
        $admin = User::create([
            'name' => 'Admin Struk',
            'email' => 'admin-struk@example.test',
            'staff_id' => 'ADMINSTRUK',
            'password' => 'TestPassword123!',
            'role' => 'admin',
            'active' => true,
        ]);
        $employee = User::create([
            'name' => 'Karyawan Struk',
            'email' => 'employee-struk@example.test',
            'staff_id' => 'EMPSTRUK',
            'password' => 'TestPassword123!',
            'role' => 'employee',
            'active' => true,
        ]);
        $paidOrder = Order::create([
            'number' => 'RX-PAID-PRINT',
            'customer_name' => 'Pelanggan Lunas',
            'whatsapp' => '08111111111',
            'total' => 45000,
            'payment_method' => 'COD',
            'payment_status' => 'Lunas',
            'status' => 'Selesai',
        ]);
        $unpaidOrder = Order::create([
            'number' => 'RX-UNPAID-PRINT',
            'customer_name' => 'Pelanggan Belum Lunas',
            'whatsapp' => '08222222222',
            'total' => 45000,
            'payment_method' => 'COD',
            'payment_status' => 'Belum Dibayar',
            'status' => 'Menunggu',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('orders.receipt.print', $paidOrder))
            ->assertDontSee(route('orders.receipt.print', $unpaidOrder));
        $this->actingAs($admin)
            ->get(route('orders.receipt.print', $paidOrder))
            ->assertOk()
            ->assertSee('Cetak struk')
            ->assertSee($paidOrder->number);
        $this->actingAs($admin)
            ->get(route('orders.receipt.print', $unpaidOrder))
            ->assertForbidden();
        $this->actingAs($employee)
            ->get(route('orders.receipt.print', $paidOrder))
            ->assertForbidden();
    }

    public function test_customer_can_view_but_not_print_receipt(): void
    {
        $customer = User::create([
            'name' => 'Pelanggan Struk',
            'email' => 'customer-struk@example.test',
            'staff_id' => 'CUSTOMERSTRUK',
            'password' => 'TestPassword123!',
            'role' => 'customer',
            'active' => true,
        ]);
        $order = Order::create([
            'number' => 'RX-CUSTOMER-PRINT',
            'customer_name' => $customer->name,
            'whatsapp' => '08333333333',
            'total' => 45000,
            'payment_method' => 'COD',
            'payment_status' => 'Lunas',
            'status' => 'Selesai',
            'user_id' => $customer->id,
        ]);

        $this->actingAs($customer)
            ->get(route('receipt', $order))
            ->assertOk()
            ->assertSee('Struk pembelian')
            ->assertSee($order->number)
            ->assertSee('Struk hanya dapat dicetak oleh admin setelah pembayaran lunas.')
            ->assertDontSee('Cetak struk');
        $this->actingAs($customer)
            ->get(route('orders.receipt.print', $order))
            ->assertForbidden();
    }

    public function test_qris_demo_payment_shows_fake_code_before_receipt(): void
    {
        $order = Order::create([
            'number' => 'RX-QRIS-DEMO',
            'customer_name' => 'Pelanggan QRIS',
            'whatsapp' => '08111111111',
            'total' => 50000,
            'payment_method' => 'QRIS (DEMO)',
            'payment_status' => 'Belum Dibayar',
            'status' => 'Menunggu',
        ]);

        $this->get(route('payment', $order))
            ->assertOk()
            ->assertSee('QRIS PALSU - DEMO')
            ->assertSee('Lanjut ke struk');
    }

    public function test_virtual_account_demo_shows_fake_number_and_can_be_marked_paid(): void
    {
        $admin = User::create([
            'name' => 'Admin Demo',
            'email' => 'admin-demo@example.test',
            'staff_id' => 'DEMO01',
            'password' => 'TestPassword123!',
            'role' => 'admin',
            'active' => true,
        ]);
        $order = Order::create([
            'number' => 'RX-VA-DEMO',
            'customer_name' => 'Pelanggan VA',
            'whatsapp' => '08111111111',
            'total' => 50000,
            'payment_method' => 'Virtual Account (DEMO)',
            'payment_status' => 'Belum Dibayar',
            'status' => 'Menunggu',
        ]);

        $this->get(route('payment', $order))
            ->assertOk()
            ->assertSee('8808')
            ->assertSee('Nomor Virtual Account palsu');

        $this->actingAs($admin)->post(route('orders.status', $order), [
            'status' => 'Selesai',
            'payment_status' => 'Lunas',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'Selesai',
            'payment_status' => 'Lunas',
        ]);
    }

    public function test_service_can_be_carted_without_price_and_deleted_from_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin Jasa',
            'email' => 'admin-jasa@example.test',
            'staff_id' => 'JASA01',
            'password' => 'TestPassword123!',
            'role' => 'admin',
            'active' => true,
        ]);
        $service = Product::create([
            'name' => 'Jasa Benarkan Test',
            'platform' => 'Jasa',
            'category' => 'Repair',
            'description' => 'Jasa perbaikan',
            'price' => 0,
            'stock' => 2147483647,
        ]);

        $this->post(route('guest'))->assertRedirect(route('home'));
        $this->post(route('cart.add', $service))->assertRedirect();
        $this->assertSame(1, session('cart')[$service->id]);

        $this->actingAs($admin)
            ->delete(route('products.delete', $service))
            ->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $service->id]);
    }
}
