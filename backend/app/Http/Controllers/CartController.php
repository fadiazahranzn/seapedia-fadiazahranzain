<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartController extends Controller
{
    private function ensureBuyer(): ?JsonResponse
    {
        if (JWTAuth::parseToken()->getPayload()->get('active_role') !== 'buyer') {
            return response()->json(['message' => 'Hanya Buyer yang bisa mengakses keranjang.'], 403);
        }
        return null;
    }

    public function show(Request $request): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        $cart = $request->user()->cart()->with(['store', 'items.product.store'])->first();

        return response()->json(['data' => $cart]);
    }

    public function addItem(Request $request): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::with('store')->findOrFail($data['product_id']);

        if ($product->stock < $data['quantity']) {
            return response()->json(['message' => 'Stok tidak mencukupi.'], 422);
        }

        $cart = $request->user()->cart;

        DB::transaction(function () use ($cart, $product, $data) {
            // Single-store rule
            if ($cart->store_id && $cart->store_id !== $product->store_id) {
                return; // handled below
            }

            if (! $cart->store_id) {
                $cart->update(['store_id' => $product->store_id]);
            }

            $existing = $cart->items()->where('product_id', $product->id)->first();

            if ($existing) {
                $newQty = $existing->quantity + $data['quantity'];
                if ($newQty > $product->stock) {
                    throw new \Exception('Stok tidak mencukupi.');
                }
                $existing->update(['quantity' => $newQty]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $data['quantity'],
                ]);
            }
        });

        // Check single-store conflict after transaction
        $cart->refresh();
        if ($cart->store_id && $cart->store_id !== $product->store_id) {
            return response()->json([
                'message'        => 'Keranjang kamu sudah berisi produk dari toko lain. Kosongkan keranjang terlebih dahulu.',
                'conflict'       => true,
                'current_store'  => $cart->store->name,
            ], 422);
        }

        $cart->load(['store', 'items.product']);
        return response()->json(['message' => 'Produk ditambahkan ke keranjang.', 'data' => $cart]);
    }

    public function updateItem(Request $request, CartItem $item): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bukan item kamu.'], 403);
        }

        $data = $request->validate(['quantity' => 'required|integer|min:1']);

        if ($item->product->stock < $data['quantity']) {
            return response()->json(['message' => 'Stok tidak mencukupi.'], 422);
        }

        $item->update($data);

        return response()->json(['message' => 'Jumlah diperbarui.']);
    }

    public function removeItem(Request $request, CartItem $item): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bukan item kamu.'], 403);
        }

        $cart = $item->cart;
        $item->delete();

        if ($cart->items()->count() === 0) {
            $cart->update(['store_id' => null]);
        }

        return response()->json(['message' => 'Item dihapus dari keranjang.']);
    }

    public function clear(Request $request): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        $cart = $request->user()->cart;
        $cart->items()->delete();
        $cart->update(['store_id' => null]);

        return response()->json(['message' => 'Keranjang dikosongkan.']);
    }
}
