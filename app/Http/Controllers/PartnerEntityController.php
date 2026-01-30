<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerEntity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PartnerEntityController extends Controller
{
    // ========================================================================
    // MÉTODOS PARA SUPER ADMIN (Partners -> Entities)
    // ========================================================================
    
    public function index(Partner $partner)
    {
        $entities = $partner->entities()->latest()->get();
        return view('partners.entities.index', compact('partner','entities'));
    }

    public function create(Partner $partner)
    {
        return view('partners.entities.create', compact('partner'));
    }

    public function store(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'razon_social'    => ['required','string',
                Rule::unique('partner_entities')->where(fn($q)=>$q->where('partner_id',$partner->id))
            ],
            'rfc'             => ['nullable','string','max:20'],
            'telefono'        => ['nullable','string'],
            'correo_contacto' => ['nullable','email'],
            'direccion'       => ['nullable','string'],
            'payment_terms'   => ['nullable','string'],
            'urgent_fee_percentage' => ['nullable','numeric','min:0','max:100'],
            'urgent_days_limit'     => ['nullable','integer','min:1','max:365'],
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'brand_color'     => ['nullable','string','max:7','regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_default'      => ['sometimes','boolean'],
            'is_active'       => ['sometimes','boolean'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public');
        }

        // Asegura una sola default por partner
        $setAsDefault = false;
        if ($request->boolean('is_default')) {
            $partner->entities()->update(['is_default' => false]);
            $data['is_default'] = true;
            $setAsDefault = true;
        } elseif ($partner->entities()->count() === 0) {
            $data['is_default'] = true;
            $setAsDefault = true;
        }

        $entity = $partner->entities()->create($data);

        // Asignar como entidad por defecto del partner
        if ($setAsDefault) {
            $partner->update(['default_entity_id' => $entity->id]);
        }

        return redirect()->route('partners.entities.index', $partner)
            ->with('success','Razón social creada.');
    }

    public function edit(PartnerEntity $entity)
    {
        $partner = $entity->partner;
        return view('partners.entities.edit', compact('partner','entity'));
    }

    public function update(Request $request, PartnerEntity $entity)
    {
        $partner = $entity->partner;

        $data = $request->validate([
            'razon_social'    => ['required','string',
                Rule::unique('partner_entities')
                    ->ignore($entity->id)
                    ->where(fn($q)=>$q->where('partner_id',$partner->id))
            ],
            'rfc'             => ['nullable','string','max:20'],
            'telefono'        => ['nullable','string'],
            'correo_contacto' => ['nullable','email'],
            'direccion'       => ['nullable','string'],
            'payment_terms'   => ['nullable','string'],
            'urgent_fee_percentage' => ['nullable','numeric','min:0','max:100'],
            'urgent_days_limit'     => ['nullable','integer','min:1','max:365'],
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'brand_color'     => ['nullable','string','max:7','regex:/^#[0-9A-Fa-f]{6}$/'],
            'remove_logo'     => 'sometimes|boolean',
            'is_default'      => ['sometimes','boolean'],
            'is_active'       => ['sometimes','boolean'],
        ]);

        if ($request->hasFile('logo')) {
            if ($entity->logo_path) {
                Storage::disk('public')->delete($entity->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public');
        }

        if ($request->boolean('remove_logo') && $entity->logo_path) {
            Storage::disk('public')->delete($entity->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->boolean('is_default')) {
            $partner->entities()->where('id','!=',$entity->id)->update(['is_default' => false]);
            $data['is_default'] = true;
            // Asignar como entidad por defecto del partner
            $partner->update(['default_entity_id' => $entity->id]);
        }

        $entity->update($data);

        return redirect()->route('partners.entities.index', $partner)
            ->with('success','Razón social actualizada.');
    }

    public function destroy(PartnerEntity $entity)
    {
        $partner = $entity->partner;
        
        if ($entity->logo_path) {
            Storage::disk('public')->delete($entity->logo_path);
        }
        
        $entity->delete();

        // Si borraste la default, marca otra como default
        if (!$partner->defaultEntity()->exists() && $partner->entities()->exists()) {
            $partner->entities()->first()->update(['is_default' => true]);
        }

        return redirect()->route('partners.entities.index', $partner)
            ->with('success','Razón social eliminada.');
    }

    // ========================================================================
    // MÉTODOS PARA ASOCIADOS (Mis Razones Sociales)
    // ========================================================================
    
    /**
     * Lista de razones sociales del partner del usuario autenticado
     */
    public function myIndex()
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        if (!$partner) {
            abort(403, 'No tienes un partner asignado.');
        }

        $entities = $partner->entities()->latest()->get();
        
        return view('my-entities.index', compact('partner', 'entities'));
    }

    /**
     * Formulario para crear razón social (asociado)
     */
    public function myCreate()
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        if (!$partner) {
            abort(403, 'No tienes un partner asignado.');
        }

        return view('my-entities.create', compact('partner'));
    }

    /**
     * Guardar razón social (asociado)
     */
    public function myStore(Request $request)
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        if (!$partner) {
            abort(403, 'No tienes un partner asignado.');
        }

        $data = $request->validate([
            'razon_social'    => ['required','string',
                Rule::unique('partner_entities')->where(fn($q)=>$q->where('partner_id',$partner->id))
            ],
            'rfc'             => ['nullable','string','max:20'],
            'telefono'        => ['nullable','string'],
            'correo_contacto' => ['nullable','email'],
            'direccion'       => ['nullable','string'],
            'payment_terms'   => ['nullable','string'],
            'urgent_fee_percentage' => ['nullable','numeric','min:0','max:100'],
            'urgent_days_limit'     => ['nullable','integer','min:1','max:365'],
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'brand_color'     => ['nullable','string','max:7','regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_default'      => ['sometimes','boolean'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public');
        }

        // Asegura una sola default por partner
        $setAsDefault = false;
        if ($request->boolean('is_default')) {
            $partner->entities()->update(['is_default' => false]);
            $data['is_default'] = true;
            $setAsDefault = true;
        } elseif ($partner->entities()->count() === 0) {
            $data['is_default'] = true;
            $setAsDefault = true;
        }

        $data['is_active'] = true; // Siempre activo cuando lo crea el asociado

        $entity = $partner->entities()->create($data);

        // Asignar como entidad por defecto del partner
        if ($setAsDefault) {
            $partner->update(['default_entity_id' => $entity->id]);
        }

        return redirect()->route('my-entities.index')
            ->with('success','Razón social creada exitosamente.');
    }

    /**
     * Formulario para editar razón social (asociado)
     */
    public function myEdit($id)
    {
        $user = Auth::user();
        $entity = PartnerEntity::findOrFail($id);
        
        // Verificar que la entidad pertenezca al partner del usuario
        if ($entity->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para editar esta razón social.');
        }

        $partner = $user->partner;

        return view('my-entities.edit', compact('partner', 'entity'));
    }

    /**
     * Actualizar razón social (asociado)
     */
    public function myUpdate(Request $request, $id)
    {
        $user = Auth::user();
        $entity = PartnerEntity::findOrFail($id);
        
        // Verificar que la entidad pertenezca al partner del usuario
        if ($entity->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para editar esta razón social.');
        }

        $partner = $user->partner;

        $data = $request->validate([
            'razon_social'    => ['required','string',
                Rule::unique('partner_entities')
                    ->ignore($entity->id)
                    ->where(fn($q)=>$q->where('partner_id',$partner->id))
            ],
            'rfc'             => ['nullable','string','max:20'],
            'telefono'        => ['nullable','string'],
            'correo_contacto' => ['nullable','email'],
            'direccion'       => ['nullable','string'],
            'payment_terms'   => ['nullable','string'],
            'urgent_fee_percentage' => ['nullable','numeric','min:0','max:100'],
            'urgent_days_limit'     => ['nullable','integer','min:1','max:365'],
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'brand_color'     => ['nullable','string','max:7','regex:/^#[0-9A-Fa-f]{6}$/'],
            'remove_logo'     => 'sometimes|boolean',
            'is_default'      => ['sometimes','boolean'],
        ]);

        if ($request->hasFile('logo')) {
            if ($entity->logo_path) {
                Storage::disk('public')->delete($entity->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public');
        }

        if ($request->boolean('remove_logo') && $entity->logo_path) {
            Storage::disk('public')->delete($entity->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->boolean('is_default')) {
            $partner->entities()->where('id','!=',$entity->id)->update(['is_default' => false]);
            $data['is_default'] = true;
            // Asignar como entidad por defecto del partner
            $partner->update(['default_entity_id' => $entity->id]);
        }

        $entity->update($data);

        return redirect()->route('my-entities.index')
            ->with('success','Razón social actualizada exitosamente.');
    }

    /**
     * Eliminar razón social (asociado - solo Asociado Administrador)
     */
    public function myDestroy($id)
    {
        $user = Auth::user();
        $entity = PartnerEntity::findOrFail($id);
        
        // Verificar que la entidad pertenezca al partner del usuario
        if ($entity->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para eliminar esta razón social.');
        }

        $partner = $user->partner;
        
        if ($entity->logo_path) {
            Storage::disk('public')->delete($entity->logo_path);
        }
        
        $entity->delete();

        // Si borraste la default, marca otra como default
        if (!$partner->defaultEntity()->exists() && $partner->entities()->exists()) {
            $partner->entities()->first()->update(['is_default' => true]);
        }

        return redirect()->route('my-entities.index')
            ->with('success','Razón social eliminada exitosamente.');
    }

    // ========================================================================
    // MÉTODOS PARA CONFIGURACIÓN DE CORREO
    // ========================================================================

    /**
     * Mostrar formulario de configuración de correo
     */
    public function mailConfig($id)
    {
        $user = Auth::user();
        $entity = PartnerEntity::findOrFail($id);

        // Verificar que la entidad pertenezca al partner del usuario
        if ($entity->partner_id !== $user->partner_id && !$user->hasRole('super admin')) {
            abort(403, 'No tienes permiso para configurar esta razón social.');
        }

        return view('my-entities.mail-config', compact('entity'));
    }

    /**
     * Actualizar configuración de correo
     */
    public function mailConfigUpdate(Request $request, $id)
    {
        $user = Auth::user();
        $entity = PartnerEntity::findOrFail($id);

        // Verificar permisos
        if ($entity->partner_id !== $user->partner_id && !$user->hasRole('super admin')) {
            abort(403, 'No tienes permiso para configurar esta razón social.');
        }

        $rules = [
            'smtp_host' => 'required_if:mail_configured,1|nullable|string|max:255',
            'smtp_port' => 'required_if:mail_configured,1|nullable|integer|min:1|max:65535',
            'smtp_username' => 'required_if:mail_configured,1|nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl,none',
            'mail_from_address' => 'required_if:mail_configured,1|nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'mail_cc_addresses' => 'nullable|string|max:1000',
            'mail_configured' => 'sometimes|boolean',
        ];

        // Solo requerir contraseña si no existe una previa
        if (!$entity->smtp_password) {
            $rules['smtp_password'] = 'required_if:mail_configured,1|nullable|string|max:255';
        } else {
            $rules['smtp_password'] = 'nullable|string|max:255';
        }

        $data = $request->validate($rules);

        // Si no se envió contraseña nueva, no actualizar
        if (empty($data['smtp_password'])) {
            unset($data['smtp_password']);
        }

        // Asegurar que mail_configured sea boolean
        $data['mail_configured'] = $request->boolean('mail_configured');

        // Validar formato de correos CC
        if (!empty($data['mail_cc_addresses'])) {
            $emails = array_map('trim', explode(',', $data['mail_cc_addresses']));
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return back()->withErrors(['mail_cc_addresses' => "El correo '{$email}' no es válido."])->withInput();
                }
            }
        }

        $entity->update($data);

        return redirect()->route('my-entities.mail-config', $entity->id)
            ->with('success', 'Configuración de correo actualizada exitosamente.');
    }

    /**
     * Enviar correo de prueba
     */
    public function mailConfigTest(Request $request, $id)
    {
        $user = Auth::user();
        $entity = PartnerEntity::findOrFail($id);

        // Verificar permisos
        if ($entity->partner_id !== $user->partner_id && !$user->hasRole('super admin')) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            // Obtener datos del formulario
            $host = $request->smtp_host;
            $port = $request->smtp_port;
            $username = $request->smtp_username;
            $password = $request->smtp_password ?: $entity->smtp_password_decrypted;
            $encryption = $request->smtp_encryption === 'none' ? null : $request->smtp_encryption;
            $fromAddress = $request->mail_from_address;
            $fromName = $request->mail_from_name ?: $entity->razon_social;

            if (!$host || !$port || !$username || !$password || !$fromAddress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Completa todos los campos requeridos antes de enviar la prueba.'
                ]);
            }

            // Configurar mailer temporal
            config([
                'mail.mailers.entity_test' => [
                    'transport' => 'smtp',
                    'host' => $host,
                    'port' => $port,
                    'encryption' => $encryption,
                    'username' => $username,
                    'password' => $password,
                ],
            ]);

            // Enviar correo de prueba
            \Illuminate\Support\Facades\Mail::mailer('entity_test')
                ->send([], [], function ($message) use ($fromAddress, $fromName, $user, $entity) {
                    $message->from($fromAddress, $fromName)
                        ->to($user->email)
                        ->subject('Prueba de configuración de correo - ' . $entity->razon_social)
                        ->html("
                            <h2>Configuración exitosa</h2>
                            <p>Este es un correo de prueba para verificar la configuración SMTP de <strong>{$entity->razon_social}</strong>.</p>
                            <p>Si recibes este mensaje, la configuración es correcta y puedes activar el envío de cotizaciones.</p>
                            <br>
                            <p style='color: #666; font-size: 12px;'>Este correo fue enviado automáticamente desde el sistema de cotizaciones.</p>
                        ");
                });

            return response()->json([
                'success' => true,
                'message' => "Correo de prueba enviado a {$user->email}. Revisa tu bandeja de entrada."
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en prueba de correo SMTP', [
                'entity_id' => $entity->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar: ' . $e->getMessage()
            ]);
        }
    }
}