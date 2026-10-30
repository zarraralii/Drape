<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Traits\FileManagerTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.cache_store' => 'array', 'cache.limiter' => 'array']);
    }

    public function test_registering_with_an_existing_phone_does_not_take_over_the_account(): void
    {
        $victim = User::forceCreate(['name' => 'Victim', 'email' => 'victim@example.test', 'phone' => '03001234567', 'password' => 'original-pass']);

        $this->post(route('register.store'), [
            'name' => 'Attacker',
            'phone' => '03001234567',
            'email' => 'attacker@example.test',
            'password' => 'attacker-pass',
            'password_confirmation' => 'attacker-pass',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
        $victim->refresh();
        $this->assertSame('victim@example.test', $victim->email);
        $this->assertTrue(\Hash::check('original-pass', $victim->password));
        $this->assertSame(1, User::count());
    }

    public function test_deactivated_customer_cannot_log_in(): void
    {
        User::forceCreate(['name' => 'Old', 'email' => 'old@example.test', 'phone' => '03001112222', 'password' => 'secret-pass', 'is_active' => 0]);

        $this->post(route('login.store'), ['identity' => 'old@example.test', 'password' => 'secret-pass'])
            ->assertSessionHas('fail');

        $this->assertGuest();
    }

    public function test_google_link_drops_a_password_set_before_the_email_was_proven(): void
    {
        config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);
        $squatter = User::forceCreate(['name' => 'Squatter', 'email' => 'sara@gmail.com', 'phone' => '03009998888', 'password' => 'squatter-pass']);

        $google = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $google->shouldReceive('getId')->andReturn('g-1');
        $google->shouldReceive('getEmail')->andReturn('sara@gmail.com');
        $google->shouldReceive('getName')->andReturn('Sara');
        $google->user = ['email_verified' => true];
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver->user')->andReturn($google);

        $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($squatter);
        $this->assertNull($squatter->fresh()->password);
    }

    public function test_uploads_are_stored_with_the_extension_of_their_real_contents(): void
    {
        $uploader = new class { use FileManagerTrait; };

        // Real temp files: Laravel's fakes report a type based on their name.
        $jpeg = tempnam(sys_get_temp_dir(), 'up');
        imagejpeg(imagecreatetruecolor(20, 20), $jpeg);
        $script = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($script, '<?php echo "pwned";');

        // A genuine JPEG named .html must not land in public/ as an .html page.
        $stored = $uploader->upload('upload/test-uploads', new UploadedFile($jpeg, 'innocent.html', null, null, true));
        $this->assertStringEndsWith('.jpg', $stored);
        @unlink(public_path('upload/test-uploads/' . $stored));
        @rmdir(public_path('upload/test-uploads'));

        // A script dressed up as an image is refused outright.
        $this->expectException(ValidationException::class);
        $uploader->upload('upload/test-uploads', new UploadedFile($script, 'photo.jpg', null, null, true));
    }

    public function test_review_redirect_only_goes_to_this_site(): void
    {
        $this->assertSame('/fallback', safe_redirect_url('https://evil.example/phish', '/fallback'));
        $this->assertSame('/fallback', safe_redirect_url('//evil.example/phish', '/fallback'));
        $this->assertSame('/account', safe_redirect_url('/account', '/fallback'));
        $this->assertSame(url('/account'), safe_redirect_url(url('/account'), '/fallback'));

        $this->assertSame('/fallback', safe_link('javascript:alert(1)', '/fallback'));
        $this->assertSame('https://instagram.com/drape', safe_link('https://instagram.com/drape', '/fallback'));

        $user = User::forceCreate(['name' => 'Ali', 'email' => 'ali@example.test', 'phone' => '03004445555']);
        $product = Product::forceCreate(['name' => 'Linen Shirt', 'slug' => 'linen-shirt', 'type' => 'simple', 'price' => 1000, 'qty' => 5, 'is_active' => 1]);
        $order = Order::forceCreate(['user_id' => $user->id, 'uid' => 'DR-TEST0001', 'status' => 'Delivered', 'total_amount' => 1000]);
        OrderItem::forceCreate(['order_id' => $order->id, 'item_id' => $product->id, 'qty' => 1, 'item_total' => 1000]);

        $this->actingAs($user)->post(route('reviews.store', $product->slug), [
            'name' => 'Ali', 'email' => 'ali@example.test', 'rating' => 5, 'review' => 'Great fit.',
            'order_id' => $order->id, 'redirect_to' => 'https://evil.example/phish',
        ])->assertRedirect(route('products.show', $product->slug) . '#reviews');
    }

    public function test_order_pages_need_proof_of_ownership(): void
    {
        $order = Order::forceCreate(['uid' => 'DR-SECRET01', 'status' => 'Pending', 'total_amount' => 1000, 'customer_name' => 'Hina', 'customer_phone' => '0300 7654321']);

        $this->get(route('checkout.thank-you', $order->uid))->assertNotFound();

        $this->get(route('order.track', ['order_uid' => $order->uid]))->assertOk()->assertDontSee('Hina');
        $this->get(route('order.track', ['order_uid' => $order->uid, 'phone' => '03111111111']))->assertDontSee('DR-SECRET01 ', false)->assertSee("couldn't find", false);
        $this->get(route('order.track', ['order_uid' => $order->uid, 'phone' => '+92 300 7654321']))->assertSee('class="order-card"', false);
    }

    public function test_unauthenticated_maintenance_url_is_gone_and_no_default_admin_exists(): void
    {
        $this->get('/clear-config')->assertNotFound();
        $this->assertSame(0, Admin::count());
    }

    public function test_admin_deletes_need_a_post_with_csrf(): void
    {
        $this->actingAs(Admin::forceCreate(['name' => 'Admin', 'email' => 'admin@example.test']), 'admin');
        $product = Product::forceCreate(['name' => 'Tee', 'slug' => 'tee', 'price' => 10, 'is_active' => 1]);

        $this->get(route('admin.delete-product', ['id' => $product->id]))->assertStatus(405);
        $this->assertNotSoftDeleted($product);

        $this->post(route('admin.delete-product'), ['id' => $product->id])->assertOk();
        $this->assertSoftDeleted($product);
    }

    public function test_inactive_admin_cannot_log_in(): void
    {
        Admin::forceCreate(['name' => 'Ex', 'email' => 'ex@example.test', 'password' => bcrypt('admin-pass-123'), 'is_active' => 0]);

        $this->post(route('auth.login'), ['email' => 'ex@example.test', 'password' => 'admin-pass-123'])->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }
}
