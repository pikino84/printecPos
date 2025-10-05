<?php

namespace App\Http\Controllers;

use App\Models\CartSession;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar el carrito
     */
    public function index()
    {
        $cartItems = CartSession::where('user_id', Auth::id())
            ->with(['variant.product.productCategory', 'variant.stocks.warehouse', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->get();

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $price = $item->variant->price ?? $item->variant->product->price;
            $subtotal += $item->quantity * $price;
        }

        return view('cart.index', compact('cartItems', 'subtotal'));
    }

    /**
     * Agregar producto al carrito (AJAX)
     */
    public function add(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'warehouse_id' => 'nullable|exists:product_warehouses,id',
        ]);

        try {
            // Verificar que la variante existe y tiene stock
            $variant = ProductVariant::with('product', 'stocks')->findOrFail($request->variant_id);
            
            // Verificar stock disponible
            $totalStock = $variant->stocks->sum('stock');
            if ($totalStock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente. Disponible: {$totalStock}"
                ], 400);
            }

            // Buscar si ya existe en el carrito
            $cartItem = CartSession::where('user_id', Auth::id())
                ->where('variant_id', $request->variant_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();

            if ($cartItem) {
                // Si ya existe, sumar la cantidad
                $newQuantity = $cartItem->quantity + $request->quantity;
                
                // Verificar que no exceda el stock
                if ($totalStock < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente. Ya tienes {$cartItem->quantity} en el carrito. Disponible: {$totalStock}"
                    ], 400);
                }
                
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                // Crear nuevo item
                $cartItem = CartSession::create([
                    'user_id' => Auth::id(),
                    'variant_id' => $request->variant_id,
                    'warehouse_id' => $request->warehouse_id,
                    'quantity' => $request->quantity,
                ]);
            }

            $cartCount = CartSession::getCartCount(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'cart_count' => $cartCount,
                'variant_name' => $variant->product->name,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar al carrito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar cantidad de un item (AJAX)
     */
    public function update(Request $request, CartSession $item)
    {
        // Verificar que el item pertenece al usuario
        if ($item->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Verificar stock
        $totalStock = $item->variant->stocks->sum('stock');
        if ($totalStock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => "Stock insuficiente. Disponible: {$totalStock}"
            ], 400);
        }

        $item->update(['quantity' => $request->quantity]);

        $price = $item->variant->price ?? $item->variant->product->price;
        $itemTotal = $item->quantity * $price;
        $cartTotal = CartSession::getCartTotal(Auth::id());

        return response()->json([
            'success' => true,
            'item_total' => number_format($itemTotal, 2),
            'cart_total' => number_format($cartTotal, 2),
        ]);
    }

    /**
     * Eliminar item del carrito (AJAX)
     */
    public function destroy(CartSession $item)
    {
        if ($item->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $item->delete();

        $cartCount = CartSession::getCartCount(Auth::id());
        $cartTotal = CartSession::getCartTotal(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'cart_count' => $cartCount,
            'cart_total' => number_format($cartTotal, 2),
        ]);
    }

    /**
     * Vaciar todo el carrito
     */
    public function clear()
    {
        CartSession::where('user_id', Auth::id())->delete();

        return redirect()->route('cart.index')
            ->with('success', 'Carrito vaciado correctamente');
    }

    /**
     * Obtener contador del carrito (AJAX)
     */
    public function count()
    {
        $count = CartSession::getCartCount(Auth::id());
        return response()->json(['count' => $count]);
    }
}