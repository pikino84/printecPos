<?php

namespace App\Http\Controllers;

use App\Models\CartSession;
use App\Models\ProductVariant;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $cartItems = CartSession::getCartItems(Auth::id());
        $subtotal = CartSession::getCartTotal(Auth::id());

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
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        try {
            $variant = ProductVariant::with('product', 'stocks')->findOrFail($request->variant_id);
            
            // Verificar stock disponible
            $totalStock = $variant->stocks->sum('stock');
            if ($totalStock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente. Disponible: {$totalStock}"
                ], 400);
            }

            // Calcular precio si no viene en el request
            $unitPrice = $request->unit_price;
            if ($unitPrice === null) {
                $unitPrice = $this->calculatePriceForUser($variant);
            }

            // Buscar si ya existe en el carrito
            $cartItem = CartSession::where('user_id', Auth::id())
                ->where('variant_id', $request->variant_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();

            if ($cartItem) {
                // Si ya existe, sumar la cantidad
                $newQuantity = $cartItem->quantity + $request->quantity;
                
                if ($totalStock < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente. Ya tienes {$cartItem->quantity} en el carrito. Disponible: {$totalStock}"
                    ], 400);
                }
                
                $cartItem->quantity = $newQuantity;
                // Actualizar precio por si cambió el tier
                $cartItem->unit_price = $unitPrice;
                $cartItem->save();
            } else {
                // Crear nuevo item
                $cartItem = CartSession::create([
                    'user_id' => Auth::id(),
                    'variant_id' => $request->variant_id,
                    'warehouse_id' => $request->warehouse_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $unitPrice,
                ]);
            }

            $cartCount = CartSession::getCartCount(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'cart_count' => $cartCount,
                'variant_name' => $variant->product->name,
                'unit_price' => number_format($unitPrice, 2),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar al carrito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular precio para el usuario actual según su tier
     */
    private function calculatePriceForUser(ProductVariant $variant)
    {
        $user = Auth::user();
        $partner = Partner::find($user->partner_id);
        
        if (!$partner) {
            return $variant->price;
        }

        $partnerPricing = $partner->getPricingConfig();
        $product = $variant->product;
        
        // Determinar si es producto de Printec/proveedor o producto propio
        $isPrintecProduct = !$product->is_own_product || $product->partner_id != $user->partner_id;
        
        return $partnerPricing->calculateCostPrice($variant->price, $isPrintecProduct);
    }

    /**
     * Actualizar cantidad de un item (AJAX)
     */
    public function update(Request $request, CartSession $item)
    {
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

        return response()->json([
            'success' => true,
            'item_total' => number_format($item->item_total, 2),
            'cart_total' => number_format(CartSession::getCartTotal(Auth::id()), 2),
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

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'cart_count' => CartSession::getCartCount(Auth::id()),
            'cart_total' => number_format(CartSession::getCartTotal(Auth::id()), 2),
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
        return response()->json([
            'count' => CartSession::getCartCount(Auth::id())
        ]);
    }

    /**
     * Recalcular precios del carrito según tier actual
     * Útil si el tier del partner cambia
     */
    public function recalculatePrices()
    {
        $cartItems = CartSession::where('user_id', Auth::id())
            ->with('variant.product')
            ->get();

        foreach ($cartItems as $item) {
            $newPrice = $this->calculatePriceForUser($item->variant);
            $item->update(['unit_price' => $newPrice]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Precios recalculados',
            'cart_total' => number_format(CartSession::getCartTotal(Auth::id()), 2),
        ]);
    }
}