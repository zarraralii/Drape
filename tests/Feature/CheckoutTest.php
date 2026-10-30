<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\SiteInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Product $shirt;

    private ProductVariation $blackM;

    private ProductVariation $whiteL;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.cache_store' => 'array', 'cache.limiter' => 'array']);
        SiteInfo::forceCreate(['brand_name' => 'Drape', 'delivery_charges' => 250, 'free_shipping_threshold' => 5000]);

        $category = Category::forceCreate(['name' => 'Men', 'is_active' => 1]);
        $this->shirt = Product::forceCreate([
            'name' => 'Linen Shirt', 'slug' => 'linen-shirt', 'type' => 'variable', 'price' => 2000,
            'qty' => 0, 'is_active' => 1, 'category_id' => $category->id,
        ]);
        $this->blackM = ProductVariation::create(['product_id' => $this->shirt->id, 'attributes' => ['Color' => 'Black', 'Size' => 'M'], 'sku' => 'LS-BLK-M', 'quantity' => 3]);
        $this->whiteL = ProductVariation::create(['product_id' => $this->shirt->id, 'attributes' => ['Color' => 'White', 'Size' => 'L'], 'sku' => 'LS-WHT-L', 'quantity' => 0, 'price' => 2500]);
        $this->shirt->syncStockFromVariants();
    }

    private function addToBag(array $data): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(route('cart.add'), $data + ['product_id' => $this->shirt->id]);
    }

    private function checkout(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('checkout.store'), $overrides + [
            'name' => 'Ayesha Khan', 'phone' => '0300 1234567', 'address_line_1' => 'House 1',
            'city' => 'Lahore', 'country' => 'Pakistan',
        ]);
    }

    public function test_variable_products_need_an_in_stock_variant(): void
    {
        $this->addToBag([])->assertStatus(422);
        $this->addToBag(['variation_id' => $this->whiteL->id])->assertStatus(422)->assertJsonPath('message', 'Linen Shirt — White / L is sold out.');
        $this->addToBag(['variation_id' => $this->blackM->id, 'qty' => 2])->assertOk()->assertJsonPath('count', 2);

        // Capped at stock (3), not 2 + 5.
        $this->addToBag(['variation_id' => $this->blackM->id, 'qty' => 5])->assertOk()->assertJsonPath('count', 3);
    }

    public function test_guest_checkout_places_order_takes_stock_and_never_touches_accounts(): void
    {
        $existing = User::forceCreate(['name' => 'Someone Else', 'email' => 'x@example.test', 'phone' => '0300 1234567']);
        $this->addToBag(['variation_id' => $this->blackM->id, 'qty' => 2]);

        $response = $this->checkout()->assertSessionHasNoErrors();

        $order = Order::with('items')->firstOrFail();
        $response->assertRedirect(route('checkout.thank-you', $order->uid));
        $this->assertNull($order->user_id);
        $this->assertSame('Ayesha Khan', $order->customer_name);
        $this->assertSame('Black / M', $order->items->first()->variant_label);
        $this->assertEquals(4000, $order->items->first()->item_total);
        $this->assertEquals(4000 + 250, $order->total_amount);   // under the free-shipping threshold
        $this->assertSame(1, $this->blackM->fresh()->quantity);
        $this->assertSame(1, $this->shirt->fresh()->qty);
        $this->assertSame('Someone Else', $existing->fresh()->name);
        $this->assertSame(1, User::count());

        $this->get(route('checkout.thank-you', $order->uid))->assertOk()->assertSee('Thank you, Ayesha!');
    }

    public function test_signed_in_checkout_saves_to_account_without_rewriting_profile(): void
    {
        $user = User::forceCreate(['name' => 'Zara', 'email' => 'zara@example.test', 'phone' => '03211234567']);
        $this->actingAs($user);
        $this->addToBag(['variation_id' => $this->blackM->id]);

        $this->checkout(['name' => 'Gift for Mum', 'phone' => '03330000000']);

        $this->assertSame($user->id, Order::firstOrFail()->user_id);
        $this->assertSame('Zara', $user->fresh()->name);
        $this->assertSame('03211234567', $user->fresh()->phone);
    }

    public function test_cannot_buy_more_than_is_left(): void
    {
        $this->addToBag(['variation_id' => $this->blackM->id, 'qty' => 3]);
        // Someone else buys two in the meantime.
        $this->blackM->update(['quantity' => 1]);

        $this->checkout()->assertSessionHasErrors('order');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $this->blackM->fresh()->quantity);
    }

    public function test_coupon_usage_limit_is_enforced_at_checkout(): void
    {
        Coupon::forceCreate(['code' => 'ONCE', 'type' => 'percent', 'value' => 10, 'usage_limit' => 1, 'used_count' => 0, 'is_active' => 1]);
        $this->addToBag(['variation_id' => $this->blackM->id]);
        $this->postJson(route('cart.coupon.apply'), ['code' => 'once'])->assertOk();

        // The last use is taken by another shopper before this one pays.
        Coupon::where('code', 'ONCE')->update(['used_count' => 1]);

        $this->checkout()->assertSessionHasErrors('coupon_code');
        $this->assertSame(0, Order::count());
    }

    public function test_free_shipping_threshold_applies_after_discount(): void
    {
        $this->blackM->update(['quantity' => 10]);
        $this->addToBag(['variation_id' => $this->blackM->id, 'qty' => 3]);   // 6000

        $this->checkout();

        $this->assertEquals(0, Order::firstOrFail()->delivery_charges);
    }

    public function test_cancelling_restocks_and_reopening_takes_stock_again(): void
    {
        $this->addToBag(['variation_id' => $this->blackM->id, 'qty' => 2]);
        $this->checkout();
        $order = Order::firstOrFail();
        $this->assertSame(1, $this->blackM->fresh()->quantity);

        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');

        $this->post(route('admin.order.update-status'), ['order_id' => $order->id, 'status' => 'Shipped; DROP TABLE'])->assertSessionHasErrors('status');

        $this->postJson(route('admin.order.update-status'), ['order_id' => $order->id, 'status' => 'Cancelled'])->assertOk();
        $this->assertSame(3, $this->blackM->fresh()->quantity);

        // Cancelling twice doesn't double the stock.
        $this->postJson(route('admin.order.update-status'), ['order_id' => $order->id, 'status' => 'Cancelled'])->assertOk();
        $this->assertSame(3, $this->blackM->fresh()->quantity);

        $this->postJson(route('admin.order.update-status'), ['order_id' => $order->id, 'status' => 'Processing'])->assertOk();
        $this->assertSame(1, $this->blackM->fresh()->quantity);
    }

    public function test_admin_order_with_a_variant(): void
    {
        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');

        $this->post(route('admin.create-order'), [
            'name' => 'Walk-in', 'phone' => '03000000001', 'address_line_1' => 'Shop', 'city' => 'Lahore', 'country' => 'Pakistan',
            'status' => 'Pending', 'payment_method' => 'Cash', 'admin_discount_type' => 'fixed', 'delivery_charges' => 0,
            'items' => [['product_id' => $this->shirt->id . ':' . $this->blackM->id, 'qty' => 1]],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Black / M', Order::firstOrFail()->items()->first()->variant_label);
        $this->assertSame(2, $this->blackM->fresh()->quantity);
    }
}
