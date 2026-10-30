<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderDiscountTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.cache_store' => 'array', 'cache.limiter' => 'array']);
        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');

        // 2 × 1000 = subtotal 2000
        $this->product = Product::forceCreate([
            'name' => 'Rose', 'slug' => 'rose', 'type' => 'simple', 'price' => 1000,
            'qty' => 10, 'is_active' => 1, 'status' => 'Approved',
        ]);
    }

    private function order(array $extra): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('admin.create-order'), $extra + [
            'name' => 'Walk-in', 'phone' => '03000000001', 'address_line_1' => 'Street 1',
            'city' => 'Lahore', 'country' => 'Pakistan', 'status' => 'Pending', 'payment_method' => 'Cash',
            'delivery_charges' => 200,
            'items' => [['product_id' => $this->product->id, 'qty' => 2]],
        ]);
    }

    public function test_fixed_admin_discount(): void
    {
        $this->order(['admin_discount_type' => 'fixed', 'admin_discount_value' => 300])->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertEquals(300, $order->admin_discount);
        $this->assertEquals(300, $order->discount_amount);
        $this->assertEquals(2000 - 300 + 200, $order->total_amount);

        $this->get(route('admin.view-order', ['id' => $order->id]))->assertSee('Admin Discount');
    }

    public function test_percent_discount_applies_after_coupon(): void
    {
        Coupon::forceCreate(['code' => 'SAVE500', 'type' => 'fixed', 'value' => 500, 'is_active' => 1, 'used_count' => 0]);

        $this->order(['coupon_code' => 'SAVE500', 'admin_discount_type' => 'percent', 'admin_discount_value' => 10])
            ->assertSessionHasNoErrors();

        // 2000 − 500 coupon = 1500; 10% of 1500 = 150.
        $order = Order::firstOrFail();
        $this->assertEquals(150, $order->admin_discount);
        $this->assertEquals(650, $order->discount_amount);
        $this->assertEquals(1500 - 150 + 200, $order->total_amount);
    }

    public function test_discount_is_capped_at_subtotal_and_percent_at_100(): void
    {
        $this->order(['admin_discount_type' => 'fixed', 'admin_discount_value' => 99999])->assertSessionHasNoErrors();
        $this->assertEquals(200, Order::firstOrFail()->total_amount); // only delivery left

        $this->order(['admin_discount_type' => 'percent', 'admin_discount_value' => 150])
            ->assertSessionHasErrors('admin_discount_value');
    }

    public function test_no_discount_by_default(): void
    {
        $this->order(['admin_discount_type' => 'fixed', 'admin_discount_value' => ''])->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertEquals(0, $order->admin_discount);
        $this->assertNull($order->admin_discount_type);
        $this->assertEquals(2200, $order->total_amount);
    }
}
