<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PartnerWebsiteController extends Controller
{
    // ========================================================================
    // SUPER ADMIN
    // ========================================================================

    public function edit(Partner $partner)
    {
        return view('partners.website.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        return $this->handleUpdate($request, $partner, 'partners.website.edit');
    }

    public function preview(Partner $partner)
    {
        return view('partners.website.preview', compact('partner'));
    }

    // ========================================================================
    // ASOCIADO ADMINISTRADOR
    // ========================================================================

    public function myEdit()
    {
        $partner = Auth::user()->partner;
        return view('partners.website.edit', [
            'partner' => $partner,
            'isMyView' => true,
        ]);
    }

    public function myUpdate(Request $request)
    {
        $partner = Auth::user()->partner;
        return $this->handleUpdate($request, $partner, 'my-website.edit');
    }

    public function myPreview()
    {
        $partner = Auth::user()->partner;
        return view('partners.website.preview', compact('partner'));
    }

    // ========================================================================
    // LOGICA COMPARTIDA
    // ========================================================================

    protected function handleUpdate(Request $request, Partner $partner, string $redirectRoute)
    {
        $request->validate([
            'logo' => ['nullable', 'image', 'max:5120'],
            'hero_desktop' => ['nullable', 'image', 'max:5120'],
            'hero_mobile' => ['nullable', 'image', 'max:5120'],
            'contact_info' => ['nullable', 'string', 'max:10000'],
            'site_primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'site_secondary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'site_accent_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'site_header_footer_bg' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'site_catalog_bg' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $data = $request->only([
            'contact_info',
            'site_primary_color',
            'site_secondary_color',
            'site_accent_color',
            'site_header_footer_bg',
            'site_catalog_bg',
        ]);

        // Handle image uploads
        foreach (['logo', 'hero_desktop', 'hero_mobile'] as $field) {
            if ($request->hasFile($field)) {
                // Delete old image
                if ($partner->$field) {
                    Storage::disk('public')->delete($partner->$field);
                }
                $data[$field] = $request->file($field)->store("partners/{$partner->id}/website", 'public');
            }

            // Handle image removal
            if ($request->boolean("remove_{$field}")) {
                if ($partner->$field) {
                    Storage::disk('public')->delete($partner->$field);
                }
                $data[$field] = null;
            }
        }

        $partner->update($data);

        return redirect()->route($redirectRoute, $redirectRoute === 'my-website.edit' ? [] : $partner)
            ->with('success', 'Configuracion del sitio web actualizada correctamente.');
    }
}
