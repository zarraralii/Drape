<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Product;
use App\Models\SiteInfo;
use App\Models\User;
use App\Support\Cart;
use App\Support\Inventory;
use App\Support\OrderException;
use App\Support\OrderPlacer;
use App\Traits\FileManagerTrait;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use FileManagerTrait;

    public function orders()
    {
        $data['agents'] = Agent::orderBy('name')->get(['id', 'name']);
        $data['coupons'] = Coupon::orderBy('code')->pluck('code');
        $data['statuses'] = Order::whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view('admin.pages.allOrder', $data);
    }

    /**
     * Agent/coupon/status all live as plain columns directly on `orders`
     * (agent_id, coupon_code, status) rather than needing a whereHas —
     * see Order::agent() and CheckoutController::store(), which is where
     * those columns get set at checkout.
     */
    public function allOrder(Request $request)
    {
        $orders = Order::withCount('items')
            ->with('user:id,name,phone,email,profile_image')
            ->when($request->filled('agent_id'), fn ($q) => $q->where('agent_id', $request->agent_id))
            ->when($request->filled('coupon_code'), fn ($q) => $q->where('coupon_code', $request->coupon_code))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->get();

        $formattedOrders = [];

        foreach ($orders as $order) {
            $formattedOrders[] = [
                'id' => $order->id,
                'uid' => $order->uid,
                'user_name' => $order->contact_name,
                'user_phone' => $order->contact_phone,
                'created_at' => $order->created_at?->format('d M Y, g:ia'),
                'user_profile_image' => $order->user->profile_image ?? null,
                'status' => $order->status,
                'item_counts' => $order->items_count ?? '-',
                'total_amount' => $order->total_amount ?? '-',
            ];
        }

        $data['orders'] = $formattedOrders;

        return response()->json($data);
    }

    public function viewOrder(Request $request)
    {
        $data = null;
        if ($request->id) {
            $order = Order::with(['items.item', 'user', 'agent', 'tracking', 'trails', 'delivery'])->findOrFail($request->id);
            $data['order'] = $order;
        }

        return view('admin.pages.viewOrder')->with(['data' => $data]);
    }

    /**
     * Manual order entry (phone/WhatsApp/walk-in sales). Goes through the
     * same OrderPlacer as the storefront checkout — same pricing, stock
     * and coupon rules — but the admin sets status, payment, delivery
     * charge and an optional extra discount. Each item row picks a
     * product, or one size/colour of it ("productId:variationId").
     */
    public function createOrder(Request $request, OrderPlacer $placer)
    {
        if (!$request->isMethod('post')) {
            return view('admin.pages.addOrder', [
                'products' => Product::isActive()->with('variations')->orderBy('name')->get(),
                'agents' => Agent::where('is_active', true)->orderBy('name')->get(['id', 'name', 'commission_rate']),
                'coupons' => Coupon::orderBy('code')->get()->filter->isValid()->values(),
                'statuses' => Order::STATUSES,
                'defaultDeliveryCharge' => SiteInfo::deliveryCharge(),
            ]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'regex:/^\d+(:\d+)?$/'],
            'items.*.qty' => 'required|integer|min:1',
            'agent_id' => 'nullable|exists:agents,id',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'admin_discount_type' => 'required|in:fixed,percent',
            'admin_discount_value' => 'nullable|numeric|min:0' . ($request->input('admin_discount_type') === 'percent' ? '|max:100' : ''),
            'delivery_charges' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'payment_method' => 'required|string|max:50',
            'remarks' => 'nullable|string|max:1000',
        ], [
            'items.required' => 'Add at least one product to the order.',
            'items.*.product_id.regex' => 'Pick a product for every row.',
        ]);

        $lines = array_map(function (array $row) {
            [$productId, $variationId] = Cart::parseKey($row['product_id']);

            return ['product_id' => $productId, 'variation_id' => $variationId, 'qty' => (int) $row['qty']];
        }, $data['items']);

        // Linked to an existing customer account with this phone, if any —
        // the admin is vouching for the customer here — but never creates one.
        $user = User::where('phone', $data['phone'])->first();

        try {
            $order = $placer->place(
                $lines,
                ['name' => $data['name'], 'phone' => $data['phone'], 'email' => $data['email'] ?? null],
                $data,
                [
                    'user_id' => $user?->id,
                    'coupon' => !empty($data['coupon_code']) ? Coupon::where('code', $data['coupon_code'])->first() : null,
                    'agent' => !empty($data['agent_id']) ? Agent::find($data['agent_id']) : null,
                    'delivery_charges' => round((float) ($data['delivery_charges'] ?? 0), 2),
                    'admin_discount_type' => $data['admin_discount_type'],
                    'admin_discount_value' => $data['admin_discount_value'] ?? null,
                    'status' => $data['status'],
                    'payment_method' => $data['payment_method'],
                    'remarks' => $data['remarks'] ?? null,
                    'placed_by' => 'Admin',
                ],
            );
        } catch (OrderException $e) {
            return back()->withErrors([$e->field === 'coupon_code' ? 'coupon_code' : 'items' => $e->getMessage()])->withInput();
        }

        if ($order->status === 'Cancelled') {
            Inventory::changeStatus($order, 'Cancelled');
        }

        return redirect()->route('admin.view-order', ['id' => $order->id])->with('success', 'Order ' . $order->uid . ' created successfully');
    }

    /**
     * Cancelling puts the items back in stock; reopening a cancelled order
     * takes them again (refused if they've sold out meanwhile).
     */
    public function updateOrderStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->status === $request->status) {
            return response()->json(['success' => true, 'message' => 'Order is already ' . $request->status . '.']);
        }

        try {
            Inventory::changeStatus($order, $request->status);
        } catch (OrderException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Failed to update order status.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'trail' => $order->trails()->latest('id')->first(),
        ]);
    }

    public function addTracking(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'tracking_number' => 'required|string',
            'courier' => 'required|string',
            'courier_link' => 'required|url',
        ]);

        $tracking = new OrderTracking();
        $tracking->order_id = $request->order_id;
        $tracking->tracking_number = $request->tracking_number;
        $tracking->courier = $request->courier;
        $tracking->courier_link = $request->courier_link;
        $tracking->save();

        return response()->json(['status' => 'success', 'message' => 'Tracking info saved.', 'tracking' => $tracking]);
    }

    public function addProof(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'proof_of_delivery' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $order = Order::find($request->order_id);

        if ($request->hasFile('proof_of_delivery')) {
            $file = $this->upload('upload/orders/deliveries/', $request->file('proof_of_delivery'));
            $order->proof_of_delivery = $file;
        }

        $order->save();

        return response()->json(['status' => 'success', 'message' => 'Proof of delivery uploaded.', 'image_url' => $order->proof_of_delivery]);
    }
}
