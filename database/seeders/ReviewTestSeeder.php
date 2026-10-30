<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Local-only test data for review moderation: three customers, each with
 * one delivered order of the first product and a review on it — one plain,
 * one already replied to, one hidden. Log in as any of them (password
 * below) to check that deleting their review from admin → Reviews brings
 * the "Write a Review" form back.
 *
 *   php artisan db:seed --class=ReviewTestSeeder
 *
 * Safe to re-run: customers are matched by email and a customer's order
 * and review are only created once.
 */
class ReviewTestSeeder extends Seeder
{
    private const PASSWORD = 'review-test-123';

    public function run(): void
    {
        $product = Product::first();

        if (!$product) {
            $this->command?->warn('No products found — add a product first.');
            return;
        }

        $customers = [
            [
                'name' => 'Ayesha Malik',
                'email' => 'ayesha.review@example.test',
                'rating' => 5,
                'review' => "Beautiful fabric and the fit is spot on. I keep getting compliments.",
            ],
            [
                'name' => 'Bilal Ahmed',
                'email' => 'bilal.review@example.test',
                'rating' => 4,
                'review' => "Lovely quality, but delivery took a couple of days longer than expected.",
                'admin_reply' => "Thank you for the kind words, Bilal! Sorry about the delay — we've since added a faster courier for your area.",
            ],
            [
                'name' => 'Sara Khan',
                'email' => 'sara.review@example.test',
                'rating' => 1,
                'review' => "Not what I expected at all. Would not buy again.",
                'is_hidden' => true,
            ],
        ];

        foreach ($customers as $i => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'phone' => '0300000000' . $i]
            );

            if ($user->wasRecentlyCreated) {
                $user->password = Hash::make(self::PASSWORD);
                $user->is_active = 1;
                $user->save();
            }

            $order = Order::where('user_id', $user->id)->first();

            if (!$order) {
                $order = new Order();
                $order->user_id = $user->id;
                $order->uid = config('store.order_prefix') . '-' . strtoupper(Str::random(8));
                $order->total_amount = $product->price;
                $order->delivery_charges = 0;
                $order->status = 'Delivered';
                $order->payment_method = 'Cash on Delivery';
                $order->save();

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $product->id,
                    'qty' => 1,
                    'item_total' => $product->price,
                ]);
            }

            Review::firstOrCreate(
                ['order_id' => $order->id, 'product_id' => $product->id],
                [
                    'user_id' => $user->id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'rating' => $data['rating'],
                    'review' => $data['review'],
                    'media' => [],
                    'is_hidden' => $data['is_hidden'] ?? false,
                    'admin_reply' => $data['admin_reply'] ?? null,
                    'replied_at' => isset($data['admin_reply']) ? now() : null,
                ]
            );
        }

        $this->command?->info("Seeded 3 test reviews on \"{$product->name}\" (customer password is in ReviewTestSeeder::PASSWORD).");
    }
}
