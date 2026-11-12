<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorOrder;
use App\Support\ProductPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class CartController extends Controller
{
    private function getCartForUser(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    private function getCartWithItems(User $user): Cart
    {
        $cart = $this->getCartForUser($user);

        return $cart->load([
            'items.product.vendor',
            'items.product.images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order'),
        ]);
    }

    private function cartSubtotal(Cart $cart): float
    {
        return (float) $cart->items->sum(
            fn (CartItem $item): float => ((float) $item->unit_price) * $item->quantity
        );
    }

    private function assertCartItemOwnership(User $user, CartItem $cartItem): void
    {
        $cart = $this->getCartForUser($user);
        if ($cartItem->cart_id !== $cart->id) {
            abort(403);
        }
    }

    public function index(Request $request): View
    {
        $cart = $this->getCartWithItems($request->user());

        return view('cart.index', [
            'cart' => $cart,
            'subtotal' => $this->cartSubtotal($cart),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        if ($product->status !== 'active' || !$product->vendor || !$product->vendor->is_approved) {
            return back()->with('error', 'This product is not available.');
        }

        if (! ProductPricing::canPurchase($product)) {
            return back()->with('error', 'Contact the seller to confirm current price and availability.');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'redirect' => ['nullable', 'in:back,cart,checkout'],
        ]);

        $cart = $this->getCartForUser($request->user());
        $quantity = (int) $validated['quantity'];
        $item = $cart->items()->where('product_id', $product->id)->first();
        $existingQty = $item ? $item->quantity : 0;
        $newQty = min($product->stock, $existingQty + $quantity);

        if ($newQty < 1) {
            return back()->with('error', 'This product is currently out of stock.');
        }

        if ($item) {
            $item->update([
                'quantity' => $newQty,
                'unit_price' => $product->price,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $newQty,
                'unit_price' => $product->price,
            ]);
        }

        $redirect = $validated['redirect'] ?? 'back';

        if ($redirect === 'checkout') {
            return redirect()->route('checkout.form')->with('success', 'Product added to cart.');
        }

        if ($redirect === 'cart') {
            return redirect()->route('cart.index')->with('success', 'Product added to cart.');
        }

        return back()->with('success', 'Product added to cart.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $user = $request->user();
        $this->assertCartItemOwnership($user, $cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $quantity = (int) $validated['quantity'];

        if ($quantity === 0) {
            $cartItem->delete();
            return back()->with('success', 'Item removed from cart.');
        }

        $product = $cartItem->product;
        if (!$product) {
            $cartItem->delete();
            return back()->with('error', 'Product no longer exists and was removed from cart.');
        }

        if (! ProductPricing::canPurchase($product)) {
            $cartItem->delete();

            return back()->with('error', 'This product needs current price or availability confirmation and was removed from cart.');
        }

        $cartItem->update([
            'quantity' => min($quantity, $product->stock),
            'unit_price' => $product->price,
        ]);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->assertCartItemOwnership($request->user(), $cartItem);
        $cartItem->delete();

        return back()->with('success', 'Item removed from cart.');
    }

    public function checkoutForm(Request $request): View|RedirectResponse
    {
        $cart = $this->getCartWithItems($request->user());
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        return view('checkout.index', [
            'cart' => $cart,
            'subtotal' => $this->cartSubtotal($cart),
            'user' => $request->user(),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'shipping_name' => ['required', 'string', 'max:150'],
            'shipping_email' => ['required', 'email', 'max:190'],
            'shipping_phone' => ['required', 'string', 'max:50'],
            'shipping_address' => ['required', 'string', 'max:255'],
        ]);

        $cart = $this->getCartWithItems($user);
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        try {
            $orderNumber = DB::transaction(function () use ($cart, $user, $data): string {
                $productIds = $cart->items->pluck('product_id')->all();
                $products = Product::query()
                    ->with('vendor')
                    ->whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $vendorTotals = [];
                $total = 0.0;

                foreach ($cart->items as $item) {
                    /** @var Product|null $product */
                    $product = $products->get($item->product_id);

                    if (!$product || $product->status !== 'active' || ! $product->vendor || ! $product->vendor->is_approved) {
                        throw new \RuntimeException('Some products in your cart are no longer available.');
                    }

                    if (! ProductPricing::canPurchase($product)) {
                        throw new \RuntimeException('Some products need current price or availability confirmation before checkout.');
                    }

                    if ($item->quantity > $product->stock) {
                        throw new \RuntimeException('Some products are out of stock for requested quantity.');
                    }

                    $lineTotal = (float) $product->price * $item->quantity;
                    $total += $lineTotal;
                    $vendorTotals[$product->vendor_id] = ($vendorTotals[$product->vendor_id] ?? 0) + $lineTotal;
                }

                do {
                    $orderNumber = 'ALM-' . now()->format('Ymd-His') . '-' . random_int(1000, 9999);
                } while (Order::where('order_number', $orderNumber)->exists());

                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'status' => 'pending',
                    'total_amount' => $total,
                    'shipping_name' => $data['shipping_name'],
                    'shipping_email' => strtolower($data['shipping_email']),
                    'shipping_phone' => $data['shipping_phone'],
                    'shipping_address' => $data['shipping_address'],
                    'payment_method' => 'cash_on_delivery',
                ]);

                foreach ($cart->items as $item) {
                    /** @var Product $product */
                    $product = $products->get($item->product_id);
                    $lineTotal = (float) $product->price * $item->quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'vendor_id' => $product->vendor_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $product->price,
                        'line_total' => $lineTotal,
                    ]);

                    $product->decrement('stock', $item->quantity);
                }

                foreach ($vendorTotals as $vendorId => $subtotal) {
                    VendorOrder::create([
                        'vendor_id' => $vendorId,
                        'order_id' => $order->id,
                        'subtotal' => $subtotal,
                        'status' => 'new',
                    ]);
                }

                $cart->items()->delete();

                return $orderNumber;
            });
        } catch (Throwable $throwable) {
            return back()->withInput()->with('error', $throwable->getMessage());
        }

        return redirect()->route('checkout.success', $orderNumber)->with('success', 'Order placed successfully.');
    }

    public function success(Request $request, string $orderNumber): View
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return view('checkout.success', [
            'order' => $order,
        ]);
    }
}
