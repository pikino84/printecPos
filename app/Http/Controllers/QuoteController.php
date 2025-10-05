<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\CartSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listar cotizaciones del usuario
     */
    public function index(Request $request)
    {
        $query = Quote::with(['items.variant.product', 'partner'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $quotes = $query->paginate(15);

        return view('quotes.index', compact('quotes'));
    }

    /**
     * Crear cotización desde el carrito
     */
    public function createFromCart(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'customer_notes' => 'nullable|string|max:1000',
            'short_description' => 'nullable|string|max:255', // NUEVO
            'valid_days' => 'nullable|integer|min:1|max:90',
        ]);

        $cartItems = CartSession::where('user_id', Auth::id())
            ->with('variant.product')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'El carrito está vacío');
        }

        try {
            DB::beginTransaction();

            $quote = Quote::create([
                'user_id' => Auth::id(),
                'partner_id' => Auth::user()->partner_id,
                'quote_number' => Quote::generateQuoteNumber(),
                'status' => 'draft',
                'notes' => $request->notes,
                'customer_notes' => $request->customer_notes,
                'short_description' => $request->short_description, // NUEVO
                'valid_until' => now()->addDays($request->valid_days ?? 15),
            ]);

            foreach ($cartItems as $cartItem) {
                $price = $cartItem->variant->price ?? $cartItem->variant->product->price;

                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'variant_id' => $cartItem->variant_id,
                    'warehouse_id' => $cartItem->warehouse_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $price,
                ]);
            }

            $quote->calculateTotals();
            CartSession::where('user_id', Auth::id())->delete();

            DB::commit();

            return redirect()->route('quotes.show', $quote)
                ->with('success', 'Cotización creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cart.index')
                ->with('error', 'Error al crear cotización: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de cotización
     */
    public function show(Quote $quote)
    {
        // Verificar que la cotización pertenece al usuario o es admin
        if ($quote->user_id !== Auth::id() && !Auth::user()->hasRole(['super admin', 'admin'])) {
            abort(403);
        }

        $quote->load(['items.variant.product.productCategory', 'items.warehouse', 'partner', 'user']);

        return view('quotes.show', compact('quote'));
    }

    /**
     * Enviar cotización por email
     */
    public function send(Request $request, Quote $quote)
    {
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string|max:1000',
        ]);

        if (!$quote->canBeSent()) {
            return back()->with('error', 'Esta cotización no puede ser enviada');
        }

        try {
            // Generar PDF
            $pdf = $this->generatePDF($quote);
            $pdfOutput = $pdf->output();
            
            $customMessage = $request->message;

            \Log::info('Intentando enviar cotización', [
                'quote_number' => $quote->quote_number,
                'to_email' => $request->email,
                'pdf_size' => strlen($pdfOutput)
            ]);

            // Enviar email
            Mail::send('emails.quote', [
                'quote' => $quote,
                'customMessage' => $customMessage,
            ], function($mail) use ($quote, $request, $pdfOutput) {
                $mail->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($request->email)
                    ->subject("Cotización {$quote->quote_number} - Printec")
                    ->attachData($pdfOutput, "cotizacion-{$quote->quote_number}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            });

            // ELIMINAR LA VERIFICACIÓN DE Mail::failures()
            // Mail::send() lanza excepción automáticamente si falla

            \Log::info('Cotización enviada exitosamente', [
                'quote_number' => $quote->quote_number,
                'to_email' => $request->email
            ]);

            // Actualizar estado
            $quote->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_to_email' => $request->email,
            ]);

            \Log::info('Estado actualizado', [
                'quote_number' => $quote->quote_number,
                'new_status' => $quote->fresh()->status
            ]);

            return redirect()->route('quotes.show', $quote)
                ->with('success', 'Cotización enviada exitosamente a ' . $request->email);

        } catch (\Exception $e) {
            \Log::error('Error al enviar cotización', [
                'quote_number' => $quote->quote_number,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            return back()->with('error', 'Error al enviar cotización: ' . $e->getMessage());
        }
    }

    /**
     * Descargar PDF de la cotización
     */
    public function downloadPdf(Quote $quote)
    {
        if ($quote->user_id !== Auth::id() && !Auth::user()->hasRole(['super admin', 'admin'])) {
            abort(403);
        }

        $pdf = $this->generatePDF($quote);

        return $pdf->download("cotizacion-{$quote->quote_number}.pdf");
    }

    /**
     * Generar PDF de la cotización
     */
    private function generatePDF(Quote $quote)
    {
        $quote->load(['items.variant.product', 'items.warehouse', 'partner.defaultEntity', 'user']);

        $pdf = PDF::loadView('quotes.pdf', compact('quote'));
        
        return $pdf;
    }

    /**
     * Eliminar cotización
     */
    public function destroy(Quote $quote)
    {
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$quote->canBeEdited()) {
            return back()->with('error', 'Esta cotización no puede ser eliminada');
        }

        $quote->delete();

        return redirect()->route('quotes.index')
            ->with('success', 'Cotización eliminada exitosamente');
    }

    /**
    * Clonar cotización al carrito
    */
    public function cloneToCart(Quote $quote)
    {
        // Verificar permisos
        if ($quote->user_id !== Auth::id() && !Auth::user()->hasRole(['super admin', 'admin'])) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Limpiar carrito actual (opcional - puedes comentar esta línea si quieres agregar en lugar de reemplazar)
            CartSession::where('user_id', Auth::id())->delete();

            // Clonar items de la cotización al carrito
            foreach ($quote->items as $item) {
                CartSession::create([
                    'user_id' => Auth::id(),
                    'variant_id' => $item->variant_id,
                    'warehouse_id' => $item->warehouse_id,
                    'quantity' => $item->quantity,
                ]);
            }

            DB::commit();

            return redirect()->route('cart.index')
                ->with('success', "Cotización {$quote->quote_number} clonada al carrito exitosamente");

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error al clonar cotización', [
                'quote_number' => $quote->quote_number,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al clonar cotización: ' . $e->getMessage());
        }
    }

    /**
     * Editar cotización borrador (moverla al carrito y eliminarla)
     */
    public function editToCart(Quote $quote)
    {
        // Verificar permisos
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        // Solo se pueden editar borradores
        if (!$quote->canBeEdited()) {
            return back()->with('error', 'Solo se pueden editar cotizaciones en estado borrador');
        }

        try {
            DB::beginTransaction();

            // Limpiar carrito actual
            CartSession::where('user_id', Auth::id())->delete();

            // Mover items al carrito
            foreach ($quote->items as $item) {
                CartSession::create([
                    'user_id' => Auth::id(),
                    'variant_id' => $item->variant_id,
                    'warehouse_id' => $item->warehouse_id,
                    'quantity' => $item->quantity,
                ]);
            }

            // Eliminar la cotización borrador
            $quoteNumber = $quote->quote_number;
            $quote->delete();

            DB::commit();

            return redirect()->route('cart.index')
                ->with('success', "Cotización {$quoteNumber} movida al carrito para edición");

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error al editar cotización', [
                'quote_number' => $quote->quote_number,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al editar cotización: ' . $e->getMessage());
        }
    }
}