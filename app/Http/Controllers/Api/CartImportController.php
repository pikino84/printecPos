<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartSession;
use App\Models\Partner;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartImportController extends Controller
{
    /**
     * Importar carrito desde JSON del widget externo
     *
     * Este endpoint recibe el JSON exportado por el widget del catálogo
     * y crea los items en el carrito del usuario autenticado
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'version' => 'required|string',
            'partner_api_key' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Datos inválidos',
                'details' => $validator->errors()
            ], 422);
        }

        // Verificar que el API key corresponda al partner del usuario
        $user = $request->user();
        $partner = Partner::where('api_key', $request->partner_api_key)
            ->where('is_active', true)
            ->first();

        if (!$partner) {
            return response()->json([
                'success' => false,
                'error' => 'API key del partner inválida'
            ], 401);
        }

        // Verificar que el usuario pertenezca al partner o sea admin
        if ($user->partner_id !== $partner->id && !$user->hasAnyRole(['super admin', 'Asociado Administrador'])) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes permiso para importar carritos de este partner'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Limpiar carrito actual del usuario (opcional, según preferencia)
            $clearCart = $request->boolean('clear_existing', true);
            if ($clearCart) {
                CartSession::where('user_id', $user->id)->delete();
            }

            $importedCount = 0;
            $skippedItems = [];
            $importedItems = [];

            foreach ($request->items as $item) {
                // Verificar que la variante existe
                $variant = ProductVariant::with('product')->find($item['variant_id']);

                if (!$variant) {
                    $skippedItems[] = [
                        'variant_id' => $item['variant_id'],
                        'reason' => 'Variante no encontrada',
                        'name' => $item['name'] ?? 'Desconocido'
                    ];
                    continue;
                }

                // Verificar stock disponible
                $totalStock = $variant->stocks->sum('stock');
                $isPrintecPartner = $partner->slug === 'printec';

                // Solo validar stock si no es partner Printec
                if (!$isPrintecPartner && $totalStock < $item['quantity']) {
                    $skippedItems[] = [
                        'variant_id' => $item['variant_id'],
                        'reason' => "Stock insuficiente. Disponible: {$totalStock}",
                        'name' => $variant->product->name ?? $item['name'] ?? 'Desconocido'
                    ];
                    continue;
                }

                // Buscar si ya existe en el carrito
                $existingItem = CartSession::where('user_id', $user->id)
                    ->where('variant_id', $item['variant_id'])
                    ->first();

                if ($existingItem && !$clearCart) {
                    // Si no se limpió el carrito, sumar la cantidad
                    $existingItem->quantity += $item['quantity'];
                    $existingItem->save();
                } else {
                    // Crear nuevo item
                    CartSession::create([
                        'user_id' => $user->id,
                        'variant_id' => $item['variant_id'],
                        'warehouse_id' => null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }

                $importedItems[] = [
                    'variant_id' => $item['variant_id'],
                    'name' => $variant->product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price']
                ];
                $importedCount++;
            }

            DB::commit();

            $cartTotal = CartSession::getCartTotal($user->id);
            $cartCount = CartSession::getCartCount($user->id);

            return response()->json([
                'success' => true,
                'message' => "Se importaron {$importedCount} productos al carrito",
                'imported_count' => $importedCount,
                'skipped_count' => count($skippedItems),
                'skipped_items' => $skippedItems,
                'imported_items' => $importedItems,
                'cart_total' => number_format($cartTotal, 2),
                'cart_count' => $cartCount,
                'redirect_url' => route('cart.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Error al importar el carrito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar JSON del carrito sin importarlo
     * Útil para preview antes de la importación
     */
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'version' => 'required|string',
            'partner_api_key' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'error' => 'Estructura JSON inválida',
                'details' => $validator->errors()
            ], 422);
        }

        // Verificar API key
        $partner = Partner::where('api_key', $request->partner_api_key)
            ->where('is_active', true)
            ->first();

        if (!$partner) {
            return response()->json([
                'valid' => false,
                'error' => 'API key del partner inválida'
            ], 401);
        }

        $validItems = [];
        $invalidItems = [];

        foreach ($request->items as $item) {
            $variant = ProductVariant::with(['product', 'stocks'])->find($item['variant_id']);

            if (!$variant) {
                $invalidItems[] = [
                    'variant_id' => $item['variant_id'],
                    'reason' => 'Variante no encontrada',
                    'name' => $item['name'] ?? 'Desconocido'
                ];
                continue;
            }

            $totalStock = $variant->stocks->sum('stock');
            $isPrintecPartner = $partner->slug === 'printec';

            if (!$isPrintecPartner && $totalStock < $item['quantity']) {
                $invalidItems[] = [
                    'variant_id' => $item['variant_id'],
                    'reason' => "Stock insuficiente. Solicitado: {$item['quantity']}, Disponible: {$totalStock}",
                    'name' => $variant->product->name
                ];
                continue;
            }

            $validItems[] = [
                'variant_id' => $item['variant_id'],
                'name' => $variant->product->name,
                'color' => $variant->color,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'] ?? $variant->price,
                'stock_available' => $totalStock
            ];
        }

        return response()->json([
            'valid' => count($invalidItems) === 0,
            'partner_name' => $partner->name,
            'valid_items' => $validItems,
            'valid_count' => count($validItems),
            'invalid_items' => $invalidItems,
            'invalid_count' => count($invalidItems),
            'totals' => $request->totals ?? null
        ]);
    }
}
