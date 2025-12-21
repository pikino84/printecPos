<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quote;
use App\Models\PartnerEntity;
use App\Services\CFDI\CFDIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected CFDIService $cfdiService;

    public function __construct(CFDIService $cfdiService)
    {
        $this->middleware('auth');
        $this->cfdiService = $cfdiService;
    }

    /**
     * Listar facturas
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Invoice::with(['quote', 'partnerEntity'])
            ->whereHas('quote', function ($q) use ($user) {
                $q->where('partner_id', $user->partner_id);
            })
            ->orderByDesc('created_at');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhere('receptor_rfc', 'like', "%{$search}%")
                    ->orWhere('receptor_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Mostrar detalle de factura
     */
    public function show(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        $invoice->load(['items', 'quote', 'partnerEntity']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Formulario para crear factura(s) desde cotización
     */
    public function createFromQuote(Quote $quote)
    {
        $this->authorizeQuote($quote);

        if (!$quote->canGenerateInvoices()) {
            return back()->with('error', 'Esta cotización no puede generar facturas.');
        }

        if ($quote->isFullyInvoiced()) {
            return back()->with('error', 'Esta cotización ya está completamente facturada.');
        }

        // Obtener entidades fiscales del partner
        $entities = PartnerEntity::where('partner_id', $quote->partner_id)
            ->where('is_active', true)
            ->get();

        // Si no hay entidades, mostrar vista con modal de configuración
        if ($entities->isEmpty()) {
            return view('invoices.no-fiscal-config', compact('quote'));
        }

        return view('invoices.create-from-quote', compact('quote', 'entities'));
    }

    /**
     * Generar factura(s) desde cotización
     */
    public function storeFromQuote(Request $request, Quote $quote)
    {
        $this->authorizeQuote($quote);

        $request->validate([
            'partner_entity_id' => 'required|exists:partner_entities,id',
            'payment_split' => 'required|in:full,split',
            'payment_form' => 'required|string|max:3',
            'cfdi_use' => 'required|string|max:5',
        ]);

        if (!$quote->canGenerateInvoices()) {
            return back()->with('error', 'Esta cotización no puede generar facturas.');
        }

        $entity = PartnerEntity::findOrFail($request->partner_entity_id);

        if (!$entity->canIssueInvoices()) {
            return back()->with('error', 'La entidad fiscal seleccionada no tiene la configuración completa para facturar.');
        }

        try {
            DB::beginTransaction();

            $invoices = [];

            if ($request->payment_split === 'full') {
                // Una sola factura por el 100%
                $invoice = Invoice::createFromQuote($quote, $entity, 1, 1, 100);
                $invoice->update([
                    'payment_form' => $request->payment_form,
                    'cfdi_use' => $request->cfdi_use,
                ]);
                $invoices[] = $invoice;
            } else {
                // Dos facturas del 50% cada una
                for ($i = 1; $i <= 2; $i++) {
                    $invoice = Invoice::createFromQuote($quote, $entity, $i, 2, 50);
                    $invoice->update([
                        'payment_form' => $request->payment_form,
                        'cfdi_use' => $request->cfdi_use,
                    ]);
                    $invoices[] = $invoice;
                }
            }

            DB::commit();

            $count = count($invoices);
            $message = $count === 1
                ? 'Factura creada exitosamente.'
                : "{$count} facturas creadas exitosamente.";

            return redirect()
                ->route('invoices.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear facturas', [
                'quote_id' => $quote->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al crear las facturas: ' . $e->getMessage());
        }
    }

    /**
     * Timbrar factura
     */
    public function stamp(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        if (!$invoice->canBeStamped()) {
            return back()->with('error', 'Esta factura no puede ser timbrada.');
        }

        $result = $this->cfdiService->stamp($invoice);

        if ($result->isSuccess()) {
            return back()->with('success', 'Factura timbrada exitosamente. UUID: ' . $result->getUuid());
        }

        return back()->with('error', 'Error al timbrar: ' . $result->getError());
    }

    /**
     * Descargar XML
     */
    public function downloadXml(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        if (empty($invoice->xml_content)) {
            return back()->with('error', 'Esta factura no tiene XML disponible.');
        }

        $filename = "{$invoice->series}-{$invoice->folio}.xml";

        return response($invoice->xml_content, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Descargar PDF
     */
    public function downloadPdf(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        $invoice->load(['items', 'partnerEntity']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice'));

        return $pdf->download("{$invoice->series}-{$invoice->folio}.pdf");
    }

    /**
     * Formulario de cancelación
     */
    public function cancelForm(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        if (!$invoice->canBeCancelled()) {
            return back()->with('error', 'Esta factura no puede ser cancelada.');
        }

        return view('invoices.cancel', compact('invoice'));
    }

    /**
     * Cancelar factura
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        $request->validate([
            'cancellation_reason' => 'required|in:01,02,03,04',
            'replacement_uuid' => 'required_if:cancellation_reason,01|nullable|uuid',
        ]);

        if (!$invoice->canBeCancelled()) {
            return back()->with('error', 'Esta factura no puede ser cancelada.');
        }

        $result = $this->cfdiService->cancel(
            $invoice,
            $request->cancellation_reason,
            $request->replacement_uuid
        );

        if ($result->isSuccess()) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Factura cancelada exitosamente.');
        }

        return back()->with('error', 'Error al cancelar: ' . $result->getError());
    }

    /**
     * Verificar autorización para la factura
     */
    protected function authorizeInvoice(Invoice $invoice): void
    {
        $user = auth()->user();

        if ($invoice->quote->partner_id !== $user->partner_id && !$user->hasRole('admin')) {
            abort(403, 'No tienes permiso para acceder a esta factura.');
        }
    }

    /**
     * Verificar autorización para la cotización
     */
    protected function authorizeQuote(Quote $quote): void
    {
        $user = auth()->user();

        if ($quote->partner_id !== $user->partner_id && !$user->hasRole('admin')) {
            abort(403, 'No tienes permiso para acceder a esta cotización.');
        }
    }
}
