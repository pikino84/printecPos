<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\User;
use App\Models\PricingTier;
use App\Mail\PartnerRegistrationReceived;
use App\Mail\NewPartnerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PartnerRegistrationController extends Controller
{
    /**
     * Mostrar formulario de registro
     */
    public function showRegistrationForm()
    {
        return view('auth.partner-register');
    }

    /**
     * Procesar registro de partner
     */
    public function register(Request $request)
    {
        // Validar reCAPTCHA
        $recaptchaResponse = $this->validateRecaptcha($request->input('g-recaptcha-response'));
        
        if (!$recaptchaResponse['success']) {
            return back()
                ->withInput()
                ->withErrors(['recaptcha' => 'Por favor, completa la verificación de seguridad.']);
        }

        // Validar datos
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
            ],
        ], [
            'business_name.required' => 'El nombre del negocio es obligatorio.',
            'contact_name.required' => 'El nombre de contacto es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.mixed_case' => 'La contraseña debe contener mayúsculas y minúsculas.',
            'password.numbers' => 'La contraseña debe contener al menos un número.',
        ]);

        try {
            DB::beginTransaction();

            // Generar slug único
            $slug = $this->generateUniqueSlug($validated['business_name']);

            // Crear Partner
            $partner = Partner::create([
                'name' => $validated['business_name'],
                'slug' => $slug,
                'type' => 'Asociado',
                'is_active' => false,
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['email'],
            ]);

            // Crear Usuario
            $user = User::create([
                'name' => $validated['contact_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'partner_id' => $partner->id,
                'is_active' => false,
            ]);

            // Asignar rol
            $user->assignRole('Asociado Administrador');

            // Crear configuración de pricing
            $lowestTier = PricingTier::active()->ordered()->first();
            
            $partner->pricing()->create([
                'markup_percentage' => 0,
                'current_tier_id' => $lowestTier?->id,
                'last_month_purchases' => 0,
                'current_month_purchases' => 0,
                'manual_tier_override' => false,
            ]);

            DB::commit();

            // Enviar emails (en cola)
            Mail::to($user->email)->queue(new PartnerRegistrationReceived($partner, $user));
            Mail::to('ebutron@printec.mx')->queue(new NewPartnerNotification($partner, $user));

            return redirect()->route('partner.registration.success');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error al procesar tu registro. Por favor, intenta de nuevo.']);
        }
    }

    /**
     * Página de registro exitoso
     */
    public function success()
    {
        return view('auth.partner-register-success');
    }

    /**
     * Generar slug único
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 2;

        while (Partner::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Validar reCAPTCHA
     */
    private function validateRecaptcha(?string $response): array
    {
        if (empty($response)) {
            return ['success' => false];
        }

        $secretKey = config('services.recaptcha.secret_key');
        
        $verifyResponse = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $response
        );

        return json_decode($verifyResponse, true);
    }
}