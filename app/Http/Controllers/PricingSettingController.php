<?php

namespace App\Http\Controllers;

use App\Models\PricingSetting;
use Illuminate\Http\Request;

class PricingSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super admin');
    }

    /**
     * Mostrar formulario de configuración
     */
    public function index()
    {
        $settings = [
            'printec_markup' => PricingSetting::get('printec_markup', 52),
            'tax_rate' => PricingSetting::get('tax_rate', 16),
        ];

        return view('pricing-settings.index', compact('settings'));
    }

    /**
     * Actualizar configuración
     */
    public function update(Request $request)
    {
        $request->validate([
            'printec_markup' => 'required|numeric|min:0|max:100',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Actualizar o crear los settings
        $this->updateOrCreateSetting('printec_markup', $request->printec_markup, 'decimal', 'Markup Printec', 'Porcentaje de ganancia de Printec sobre precio base');
        $this->updateOrCreateSetting('tax_rate', $request->tax_rate, 'decimal', 'IVA', 'Porcentaje de impuesto');

        return redirect()->route('pricing-settings.index')
            ->with('success', 'Configuración de pricing actualizada correctamente.');
    }

    /**
     * Helper para actualizar o crear un setting
     */
    private function updateOrCreateSetting($key, $value, $type, $label, $description)
    {
        PricingSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'label' => $label,
                'description' => $description,
                'group' => 'pricing',
                'is_editable' => true,
            ]
        );

        // Limpiar caché
        \Illuminate\Support\Facades\Cache::forget("pricing_setting_{$key}");
    }
}
