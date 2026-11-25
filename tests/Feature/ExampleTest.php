<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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
            'platform' => 'PS4',
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
