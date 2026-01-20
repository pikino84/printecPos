<?php

namespace App\Http\Controllers;

use App\Models\CartSession;
use App\Models\ProductVariant;
use App\Models\Partner;
use App\Models\PartnerEntity;
use App\Models\Client;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

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

        // Obtener entidades del partner del usuario para el selector de razón social emisora
        $user = Auth::user();
        $partnerEntities = collect();
        $defaultEntityId = null;

        if ($user->partner) {
            $partnerEntities = $user->partner->entities()->active()->get();
            $defaultEntityId = $user->partner->default_entity_id;
        }

        return view('cart.index', compact('cartItems', 'subtotal', 'partnerEntities', 'defaultEntityId'));
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

            // Verificar si el usuario es del partner Printec (no aplica validación de stock)
            $user = Auth::user();
            $partner = Partner::find($user->partner_id);
            $isPrintecPartner = $partner && $partner->slug === 'printec';

            // Verificar stock disponible (excepto para partner Printec)
            $totalStock = $variant->stocks->sum('stock');
            if (!$isPrintecPartner && $totalStock < $request->quantity) {
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

                if (!$isPrintecPartner && $totalStock < $newQuantity) {
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
     * Calcular precio para el usuario actual según su tier y markup de ganancia
     * Incluye el porcentaje de ganancia del distribuidor (markup_percentage de /mi-ganancia)
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
        // Productos propios (is_own_product = true) siempre usan precio directo sin markup
        $isPrintecProduct = !$product->is_own_product;

        // Usar calculateSalePrice para incluir el markup del partner (porcentaje de ganancia)
        return $partnerPricing->calculateSalePrice($variant->price, $isPrintecProduct);
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

        // Verificar si el usuario es del partner Printec (no aplica validación de stock)
        $user = Auth::user();
        $partner = Partner::find($user->partner_id);
        $isPrintecPartner = $partner && $partner->slug === 'printec';

        // Verificar stock (excepto para partner Printec)
        $totalStock = $item->variant->stocks->sum('stock');
        if (!$isPrintecPartner && $totalStock < $request->quantity) {
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

    /**
     * Preview del PDF de cotización (sin crear la cotización)
     */
    public function previewPdf(Request $request)
    {
        $user = Auth::user();
        $cartItems = CartSession::getCartItems($user->id);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'El carrito está vacío');
        }

        // Obtener partner y entidad
        $partnerId = $user->partner_id;
        $partnerEntityId = $request->partner_entity_id ?: ($user->partner->default_entity_id ?? null);

        // Calcular subtotal
        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->effective_price;
        });

        // Calcular cargo por urgencia si aplica
        $isUrgent = $request->boolean('is_urgent');
        $urgencyFee = 0;
        $urgencyPercentage = null;

        if ($isUrgent && $partnerEntityId) {
            $entity = PartnerEntity::find($partnerEntityId);
            if ($entity && $entity->hasUrgentConfig()) {
                $urgencyFee = $entity->calculateUrgencyFee($subtotal);
                $urgencyPercentage = $entity->urgent_fee_percentage;
            }
        }

        // Calcular IVA y total
        $taxRate = \App\Models\PricingSetting::get('tax_rate', 16) / 100;
        $baseForTax = $subtotal + $urgencyFee;
        $tax = $baseForTax * $taxRate;
        $total = $subtotal + $urgencyFee + $tax;

        // Crear objeto Quote temporal (sin guardar en BD)
        $quote = new Quote();
        $quote->quote_number = 'PREVIEW-' . now()->format('YmdHis');
        $quote->created_at = now();
        $quote->valid_until = now()->addDays(15);
        $quote->status = 'draft';
        $quote->subtotal = $subtotal;
        $quote->tax = $tax;
        $quote->total = $total;
        $quote->is_urgent = $isUrgent;
        $quote->urgency_fee = $urgencyFee;
        $quote->urgency_percentage = $urgencyPercentage;
        $quote->customer_notes = $request->notes;

        // Asignar relaciones manualmente
        $quote->setRelation('partner', $user->partner);
        $quote->setRelation('user', $user);

        // Cargar entidad
        if ($partnerEntityId) {
            $partnerEntity = PartnerEntity::with('bankAccounts')->find($partnerEntityId);
            $quote->setRelation('partnerEntity', $partnerEntity);
        } else {
            $quote->setRelation('partnerEntity', $user->partner->defaultEntity);
        }

        // Crear items temporales
        $tempItems = $cartItems->map(function ($cartItem) {
            $item = new \stdClass();
            $item->variant = $cartItem->variant;
            $item->product = $cartItem->product;
            $item->warehouse = $cartItem->warehouse;
            $item->quantity = $cartItem->quantity;
            $item->unit_price = $cartItem->effective_price;
            $item->subtotal = $cartItem->quantity * $cartItem->effective_price;
            return $item;
        });

        $quote->setRelation('items', $tempItems);

        // Generar PDF
        $pdf = PDF::loadView('quotes.pdf', compact('quote'));

        return $pdf->stream("preview-cotizacion.pdf");
    }

    /**
     * Mostrar la vista para importar carrito desde JSON
     */
    public function showImport()
    {
        return view('cart.import');
    }

    /**
     * Procesar la importación del carrito desde JSON
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'json_file' => 'required_without:json_data|file|mimes:json,txt|max:2048',
            'json_data' => 'required_without:json_file|nullable|string',
            'clear_existing' => 'boolean'
        ]);

        try {
            // Obtener datos JSON del archivo o del campo de texto
            if ($request->hasFile('json_file')) {
                $jsonContent = file_get_contents($request->file('json_file')->getRealPath());
            } else {
                $jsonContent = $request->json_data;
            }

            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'El archivo JSON no es válido: ' . json_last_error_msg());
            }

            // Validar estructura
            if (!isset($data['version']) || !isset($data['items']) || !is_array($data['items'])) {
                return back()->with('error', 'El formato del JSON no es válido. Asegúrate de usar un archivo exportado del widget.');
            }

            // Verificar API key del partner
            $user = Auth::user();
            $partner = Partner::where('api_key', $data['partner_api_key'] ?? '')
                ->where('is_active', true)
                ->first();

            if (!$partner) {
                return back()->with('error', 'API key del partner inválida o partner inactivo.');
            }

            // Verificar permisos
            if ($user->partner_id !== $partner->id && !$user->hasRole('admin')) {
                return back()->with('error', 'No tienes permiso para importar carritos de este partner.');
            }

            // Limpiar carrito si se solicitó
            if ($request->boolean('clear_existing', true)) {
                CartSession::where('user_id', $user->id)->delete();
            }

            $isPrintecPartner = $partner->slug === 'printec';
            $importedCount = 0;
            $skippedItems = [];

            foreach ($data['items'] as $item) {
                $variant = ProductVariant::with(['product', 'stocks'])->find($item['variant_id']);

                if (!$variant) {
                    $skippedItems[] = ($item['name'] ?? "Variante {$item['variant_id']}") . ' - Producto no encontrado';
                    continue;
                }

                // Verificar stock
                $totalStock = $variant->stocks->sum('stock');
                if (!$isPrintecPartner && $totalStock < $item['quantity']) {
                    $skippedItems[] = ($item['name'] ?? $variant->product->name) . " - Stock insuficiente (disponible: {$totalStock})";
                    continue;
                }

                // Buscar si ya existe en el carrito
                $existingItem = CartSession::where('user_id', $user->id)
                    ->where('variant_id', $item['variant_id'])
                    ->first();

                if ($existingItem) {
                    $existingItem->quantity += $item['quantity'];
                    $existingItem->save();
                } else {
                    CartSession::create([
                        'user_id' => $user->id,
                        'variant_id' => $item['variant_id'],
                        'warehouse_id' => null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }

                $importedCount++;
            }

            $message = "Se importaron {$importedCount} productos al carrito.";
            if (count($skippedItems) > 0) {
                $message .= ' ' . count($skippedItems) . ' productos fueron omitidos.';
                session()->flash('skipped_items', $skippedItems);
            }

            return redirect()->route('cart.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar la importación: ' . $e->getMessage());
        }
    }
}