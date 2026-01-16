<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\CartSession;
use App\Models\Client;
use App\Models\Partner;
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
     * Verificar si el usuario puede acceder a la cotización
     * - super admin: puede acceder a todas
     * - otros usuarios: solo a cotizaciones de su mismo partner
     */
    private function canAccessQuote(Quote $quote): bool
    {
        $user = Auth::user();

        // Super admin puede acceder a todas
        if ($user->hasRole('super admin')) {
            return true;
        }

        // Usuarios del mismo partner pueden acceder
        if ($user->partner_id && $quote->partner_id === $user->partner_id) {
            return true;
        }

        return false;
    }

    /**
     * Listar cotizaciones
     * - super admin: ve todas con filtro por partner
     * - otros usuarios: ven todas las cotizaciones de su partner
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        $query = Quote::with(['items.variant.product', 'partner', 'user', 'client'])
            ->orderBy('created_at', 'desc');

        // Super admin ve todas, con opción de filtrar por partner
        if ($isSuperAdmin) {
            // Obtener lista de partners tipo Asociado y Mixto para el filtro
            $partners = Partner::asociadosYMixtos()->orderBy('name')->get();

            // Filtrar por partner si se selecciona
            if ($request->filled('partner_id')) {
                $query->where('partner_id', $request->partner_id);
            }
        } else {
            // Otros usuarios solo ven cotizaciones de su partner
            $partners = collect();
            if ($user->partner_id) {
                $query->where('partner_id', $user->partner_id);
            } else {
                // Si no tiene partner, solo ve las suyas
                $query->where('user_id', $user->id);
            }
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $quotes = $query->paginate(15);

        return view('quotes.index', compact('quotes', 'isSuperAdmin', 'partners'));
    }


    /**
     * Ver detalle de cotización
     */
    public function show(Quote $quote)
    {
        if (!$this->canAccessQuote($quote)) {
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
        if (!$this->canAccessQuote($quote)) {
            abort(403);
        }

        $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string|max:1000',
        ]);

        if (!$quote->canBeSent()) {
            return back()->with('error', 'Esta cotización no puede ser enviada');
        }

        // Cargar relaciones necesarias para la entidad y sus cuentas bancarias
        $quote->load([
            'partnerEntity.bankAccounts',
            'partner.defaultEntity.bankAccounts',
        ]);

        // Obtener la entidad emisora (partnerEntity o defaultEntity)
        $entity = $quote->partnerEntity ?? $quote->partner->defaultEntity;

        // Verificar si la entidad tiene configuración de correo
        if (!$entity || !$entity->hasMailConfig()) {
            return back()->with('error', 'La razón social no tiene configuración de correo. Por favor configura el correo SMTP en la sección de Razones Sociales antes de enviar cotizaciones.');
        }

        try {
            // Generar PDF
            $pdf = $this->generatePDF($quote);
            $pdfOutput = $pdf->output();

            $customMessage = $request->message;

            \Log::info('Intentando enviar cotización', [
                'quote_number' => $quote->quote_number,
                'to_email' => $request->email,
                'from_entity' => $entity->razon_social,
                'pdf_size' => strlen($pdfOutput)
            ]);

            // Configurar mailer dinámico con los datos del partner
            $mailerName = 'entity_' . $entity->id;
            config([
                "mail.mailers.{$mailerName}" => [
                    'transport' => 'smtp',
                    'host' => $entity->smtp_host,
                    'port' => $entity->smtp_port,
                    'encryption' => $entity->smtp_encryption === 'none' ? null : $entity->smtp_encryption,
                    'username' => $entity->smtp_username,
                    'password' => $entity->smtp_password_decrypted,
                ],
            ]);

            // Preparar correos CC
            $ccEmails = $entity->getMailCcArray();
            $fromAddress = $entity->mail_from_address;
            $fromName = $entity->mail_from_name ?: $entity->razon_social;

            // Enviar email usando el mailer del partner
            Mail::mailer($mailerName)->send('emails.quote', [
                'quote' => $quote,
                'customMessage' => $customMessage,
            ], function($mail) use ($quote, $request, $pdfOutput, $fromAddress, $fromName, $ccEmails, $entity) {
                $mail->from($fromAddress, $fromName)
                    ->to($request->email)
                    ->subject("Cotización {$quote->quote_number} - {$entity->razon_social}")
                    ->attachData($pdfOutput, "cotizacion-{$quote->quote_number}.pdf", [
                        'mime' => 'application/pdf',
                    ]);

                // Agregar CC si hay correos configurados
                if (!empty($ccEmails)) {
                    $mail->cc($ccEmails);
                }
            });

            \Log::info('Cotización enviada exitosamente', [
                'quote_number' => $quote->quote_number,
                'to_email' => $request->email,
                'from' => $fromAddress,
                'cc' => $ccEmails
            ]);

            // Actualizar estado
            $quote->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_to_email' => $request->email,
            ]);

            // Registrar compra en el sistema de pricing
            $partner = $quote->partner;
            if ($partner && $partner->pricing) {
                $partner->pricing->addPurchase($quote->total);
            }

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
        if (!$this->canAccessQuote($quote)) {
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
        $quote->load([
            'items.variant.product',
            'items.warehouse',
            'partner.defaultEntity.bankAccounts',
            'partnerEntity.bankAccounts',
            'user'
        ]);

        $pdf = PDF::loadView('quotes.pdf', compact('quote'));

        return $pdf;
    }

    /**
     * Eliminar cotización
     */
    public function destroy(Quote $quote)
    {
        if (!$this->canAccessQuote($quote)) {
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
     * Clonar cotización al carrito (mantiene precios originales)
     */
    public function cloneToCart(Quote $quote)
    {
        if (!$this->canAccessQuote($quote)) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Limpiar carrito actual
            CartSession::where('user_id', Auth::id())->delete();

            // Clonar items de la cotización al carrito CON el precio original
            foreach ($quote->items as $item) {
                CartSession::create([
                    'user_id' => Auth::id(),
                    'variant_id' => $item->variant_id,
                    'warehouse_id' => $item->warehouse_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price, // Mantener precio original de la cotización
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
        if (!$this->canAccessQuote($quote)) {
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

            // Mover items al carrito CON el precio original
            foreach ($quote->items as $item) {
                CartSession::create([
                    'user_id' => Auth::id(),
                    'variant_id' => $item->variant_id,
                    'warehouse_id' => $item->warehouse_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price, // Mantener precio original de la cotización
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

    /**
     * Aceptar cotización (cambiar status de sent a accepted)
     */
    public function accept(Quote $quote)
    {
        if (!$this->canAccessQuote($quote)) {
            abort(403);
        }

        if (!$quote->canBeAccepted()) {
            return back()->with('error', 'Esta cotización no puede ser aceptada. Debe estar en estado "Enviada" y no estar expirada.');
        }

        if ($quote->accept()) {
            return back()->with('success', 'Cotización aceptada exitosamente. Ahora puede generar las facturas.');
        }

        return back()->with('error', 'Error al aceptar la cotización.');
    }

    /**
     * Rechazar cotización
     */
    public function reject(Quote $quote)
    {
        if (!$this->canAccessQuote($quote)) {
            abort(403);
        }

        if ($quote->status !== 'sent') {
            return back()->with('error', 'Solo se pueden rechazar cotizaciones en estado "Enviada".');
        }

        if ($quote->reject()) {
            return back()->with('success', 'Cotización rechazada.');
        }

        return back()->with('error', 'Error al rechazar la cotización.');
    }

    /**
     * Crear cotización desde el carrito
     */
    public function createFromCart(Request $request)
    {
        $request->validate([
            'client_email' => 'required|email',
            'client_name' => 'nullable|string|max:255',
            'client_rfc' => 'nullable|string|max:13',
            'client_razon_social' => 'nullable|string|max:255',
            'partner_entity_id' => 'nullable|exists:partner_entities,id',
            'notes' => 'nullable|string|max:1000',
            'customer_notes' => 'nullable|string|max:1000',
            'short_description' => 'nullable|string|max:255',
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

            $user = Auth::user();
            $partnerId = $user->partner_id;

            // 1. Buscar o crear cliente
            $clientId = null;
            $clientData = [];

            $existingClient = Client::where('email', $request->client_email)->first();

            if ($existingClient) {
                $clientId = $existingClient->id;

                // Agregar relación con este partner si no existe
                if (!$existingClient->hasContactWith($partnerId)) {
                    $existingClient->addPartner($partnerId);
                }
            } else {
                // Si tenemos datos completos, crear cliente
                if ($request->filled('client_name')) {
                    $nameParts = explode(' ', trim($request->client_name), 2);

                    $newClient = Client::create([
                        'nombre' => $nameParts[0],
                        'apellido' => $nameParts[1] ?? '',
                        'email' => $request->client_email,
                        'rfc' => $request->client_rfc,
                        'razon_social' => $request->client_razon_social,
                    ]);

                    $newClient->partners()->attach($partnerId, [
                        'first_contact_at' => now(),
                    ]);

                    $clientId = $newClient->id;
                } else {
                    // Guardar datos en campos separados
                    $clientData = [
                        'client_email' => $request->client_email,
                        'client_name' => $request->client_name,
                        'client_rfc' => $request->client_rfc,
                        'client_razon_social' => $request->client_razon_social,
                    ];
                }
            }

            // 2. Determinar la entidad emisora (partner_entity_id)
            $partnerEntityId = $request->partner_entity_id;
            if (!$partnerEntityId && $user->partner) {
                $partnerEntityId = $user->partner->default_entity_id;
            }

            // 3. Crear cotización
            $quote = Quote::create([
                'user_id' => $user->id,
                'partner_id' => $partnerId,
                'partner_entity_id' => $partnerEntityId,
                'client_id' => $clientId,
                'client_email' => $clientData['client_email'] ?? null,
                'client_name' => $clientData['client_name'] ?? null,
                'client_rfc' => $clientData['client_rfc'] ?? null,
                'client_razon_social' => $clientData['client_razon_social'] ?? null,
                'quote_number' => Quote::generateQuoteNumber(),
                'status' => 'draft',
                'notes' => $request->notes,
                'customer_notes' => $request->customer_notes,
                'short_description' => $request->short_description,
                'valid_until' => now()->addDays($request->valid_days ?? 15),
            ]);

            // 4. Crear items usando el precio del carrito (ya calculado según tier)
            foreach ($cartItems as $cartItem) {
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'variant_id' => $cartItem->variant_id,
                    'warehouse_id' => $cartItem->warehouse_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->effective_price, // Usar precio del carrito (ya tiene tier aplicado)
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
}
