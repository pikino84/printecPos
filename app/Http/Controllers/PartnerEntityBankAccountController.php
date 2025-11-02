<?php

namespace App\Http\Controllers;

use App\Models\PartnerEntity;
use App\Models\PartnerEntityBankAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class PartnerEntityBankAccountController extends Controller
{
    // ========================================================================
    // MÉTODOS PARA SUPER ADMIN (Partners -> Entities -> Bank Accounts)
    // ========================================================================
    
    /**
     * GET /partner-entities/{partner_entity}/bank-accounts
     */
    public function index(PartnerEntity $partner_entity)
    {
        $accounts = $partner_entity->bankAccounts()->latest()->get();

        return view('partners.entities.bank_accounts.index', [
            'entity'   => $partner_entity,
            'accounts' => $accounts,
        ]);
    }

    /**
     * GET /partner-entities/{partner_entity}/bank-accounts/create
     */
    public function create(PartnerEntity $partner_entity)
    {
        return view('partners.entities.bank_accounts.create', [
            'entity' => $partner_entity,
        ]);
    }

    /**
     * POST /partner-entities/{partner_entity}/bank-accounts
     */
    public function store(Request $request, PartnerEntity $partner_entity)
    {
        $rules = [
            'bank_name'      => ['required','string','max:120'],
            'alias'          => ['nullable','string','max:50'],
            'account_holder' => ['nullable','string','max:120'],
            'account_number' => ['nullable','string','max:40',
                Rule::unique('partner_entity_bank_accounts')
                    ->where(fn($q) => $q->where('partner_entity_id', $partner_entity->id)),
            ],
            'clabe'          => ['nullable','digits:18',
                Rule::unique('partner_entity_bank_accounts')
                    ->where(fn($q) => $q->where('partner_entity_id', $partner_entity->id)),
            ],
            'swift'          => ['nullable','string','max:20'],
            'iban'           => ['nullable','string','max:34'],
            'currency'       => ['nullable','string','size:3'],
            'is_default'     => ['sometimes','boolean'],
            'is_active'      => ['sometimes','boolean'],
        ];

        $data = $request->validate($rules);
        $data['currency'] = strtoupper($data['currency'] ?? 'MXN');

        if ($request->boolean('is_default')) {
            $partner_entity->bankAccounts()->update(['is_default' => false]);
            $data['is_default'] = true;
        } elseif ($partner_entity->bankAccounts()->count() === 0) {
            $data['is_default'] = true;
        }

        $partner_entity->bankAccounts()->create($data);

        return redirect()
            ->route('partner-entities.bank-accounts.index', $partner_entity)
            ->with('success', 'Cuenta bancaria agregada.');
    }

    /**
     * GET /bank-accounts/{bank_account}/edit   (shallow)
     */
    public function edit(PartnerEntityBankAccount $bank_account)
    {
        return view('partners.entities.bank_accounts.edit', [
            'entity'      => $bank_account->entity,
            'bankAccount' => $bank_account,
        ]);
    }

    /**
     * PUT /bank-accounts/{bank_account}   (shallow)
     */
    public function update(Request $request, PartnerEntityBankAccount $bank_account)
    {
        $entityId = $bank_account->partner_entity_id;

        $rules = [
            'bank_name'      => ['required','string','max:120'],
            'alias'          => ['nullable','string','max:50'],
            'account_holder' => ['nullable','string','max:120'],
            'account_number' => ['nullable','string','max:40',
                Rule::unique('partner_entity_bank_accounts')
                    ->ignore($bank_account->id)
                    ->where(fn($q) => $q->where('partner_entity_id', $entityId)),
            ],
            'clabe'          => ['nullable','digits:18',
                Rule::unique('partner_entity_bank_accounts')
                    ->ignore($bank_account->id)
                    ->where(fn($q) => $q->where('partner_entity_id', $entityId)),
            ],
            'swift'          => ['nullable','string','max:20'],
            'iban'           => ['nullable','string','max:34'],
            'currency'       => ['nullable','string','size:3'],
            'is_default'     => ['sometimes','boolean'],
            'is_active'      => ['sometimes','boolean'],
        ];

        $data = $request->validate($rules);
        $data['currency'] = strtoupper($data['currency'] ?? $bank_account->currency ?? 'MXN');

        if ($request->boolean('is_default')) {
            $bank_account->entity
                ->bankAccounts()
                ->where('id', '!=', $bank_account->id)
                ->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $bank_account->update($data);

        return redirect()
            ->route('partner-entities.bank-accounts.index', $bank_account->entity)
            ->with('success', 'Cuenta bancaria actualizada.');
    }

    /**
     * DELETE /bank-accounts/{bank_account}   (shallow)
     */
    public function destroy(PartnerEntityBankAccount $bank_account)
    {
        $entity = $bank_account->entity;
        $wasDefault = (bool) $bank_account->is_default;

        $bank_account->delete();

        if ($wasDefault && $entity->bankAccounts()->exists()) {
            $entity->bankAccounts()->first()->update(['is_default' => true]);
        }

        return redirect()
            ->route('partner-entities.bank-accounts.index', $entity)
            ->with('success', 'Cuenta bancaria eliminada.');
    }

    // ========================================================================
    // MÉTODOS PARA ASOCIADOS (Mis Cuentas Bancarias)
    // ========================================================================
    
    /**
     * Lista de TODAS las cuentas bancarias del partner del usuario
     */
    public function myIndex()
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        if (!$partner) {
            abort(403, 'No tienes un partner asignado.');
        }

        // Obtener todas las entidades del partner con sus cuentas
        $entities = $partner->entities()
            ->with('bankAccounts')
            ->latest()
            ->get();

        return view('my-bank-accounts.index', compact('partner', 'entities'));
    }

    /**
     * Formulario para crear cuenta bancaria (asociado)
     */
    public function myCreate()
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        if (!$partner) {
            abort(403, 'No tienes un partner asignado.');
        }

        // Obtener razones sociales del partner para seleccionar
        $entities = $partner->entities()->where('is_active', true)->get();

        return view('my-bank-accounts.create', compact('partner', 'entities'));
    }

    /**
     * Guardar cuenta bancaria (asociado)
     */
    /**
     * Guardar cuenta bancaria (asociado)
     */
    public function myStore(Request $request)
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        if (!$partner) {
            abort(403, 'No tienes un partner asignado.');
        }

        $rules = [
            'partner_entity_id' => ['required', 'exists:partner_entities,id',
                function ($attribute, $value, $fail) use ($partner) {
                    $entity = PartnerEntity::find($value);
                    if ($entity->partner_id !== $partner->id) {
                        $fail('La razón social no pertenece a tu partner.');
                    }
                }
            ],
            'bank_name'      => ['required','string','max:120'],
            'alias'          => ['nullable','string','max:50'],
            'account_holder' => ['nullable','string','max:120'],
            'account_number' => ['nullable','string','max:40',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->partner_entity_id) {
                        $exists = PartnerEntityBankAccount::where('partner_entity_id', $request->partner_entity_id)
                            ->where('account_number', $value)
                            ->exists();
                        if ($exists) {
                            $fail('Este número de cuenta ya está registrado para esta razón social.');
                        }
                    }
                }
            ],
            'clabe' => ['nullable','digits:18',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->partner_entity_id) {
                        $exists = PartnerEntityBankAccount::where('partner_entity_id', $request->partner_entity_id)
                            ->where('clabe', $value)
                            ->exists();
                        if ($exists) {
                            $fail('Esta CLABE ya está registrada para esta razón social.');
                        }
                    }
                }
            ],
            'swift'          => ['nullable','string','max:20'],
            'iban'           => ['nullable','string','max:34'],
            'currency'       => ['nullable','string','size:3'],
            'is_default'     => ['sometimes','boolean'],
        ];

        $data = $request->validate($rules);
        $data['currency'] = strtoupper($data['currency'] ?? 'MXN');
        $data['is_active'] = true;

        $entity = PartnerEntity::findOrFail($data['partner_entity_id']);

        if ($request->boolean('is_default')) {
            $entity->bankAccounts()->update(['is_default' => false]);
            $data['is_default'] = true;
        } elseif ($entity->bankAccounts()->count() === 0) {
            $data['is_default'] = true;
        }

        try {
            $entity->bankAccounts()->create($data);
            
            return redirect()
                ->route('my-bank-accounts.index')
                ->with('success', 'Cuenta bancaria agregada exitosamente.');
                
        } catch (\Exception $e) {
            \Log::error('Error creating bank account', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Error al guardar la cuenta bancaria. Por favor verifica los datos.');
        }
    }

    /**
     * Formulario para editar cuenta bancaria (asociado)
     */
    public function myEdit($id)
    {
        $user = Auth::user();
        $bankAccount = PartnerEntityBankAccount::findOrFail($id);
        
        // Verificar que la cuenta pertenezca a una entidad del partner del usuario
        if ($bankAccount->entity->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para editar esta cuenta bancaria.');
        }

        $partner = $user->partner;
        $entities = $partner->entities()->where('is_active', true)->get();

        return view('my-bank-accounts.edit', compact('partner', 'entities', 'bankAccount'));
    }

    /**
     * Actualizar cuenta bancaria (asociado)
     */
    public function myUpdate(Request $request, $id)
    {
        $user = Auth::user();
        $bankAccount = PartnerEntityBankAccount::findOrFail($id);
        
        if ($bankAccount->entity->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para editar esta cuenta bancaria.');
        }

        $partner = $user->partner;

        $rules = [
            'partner_entity_id' => ['required', 'exists:partner_entities,id',
                function ($attribute, $value, $fail) use ($partner) {
                    $entity = PartnerEntity::find($value);
                    if ($entity->partner_id !== $partner->id) {
                        $fail('La razón social no pertenece a tu partner.');
                    }
                }
            ],
            'bank_name'      => ['required','string','max:120'],
            'alias'          => ['nullable','string','max:50'],
            'account_holder' => ['nullable','string','max:120'],
            'account_number' => ['nullable','string','max:40',
                function ($attribute, $value, $fail) use ($request, $bankAccount) {
                    if ($value && $request->partner_entity_id) {
                        $exists = PartnerEntityBankAccount::where('partner_entity_id', $request->partner_entity_id)
                            ->where('account_number', $value)
                            ->where('id', '!=', $bankAccount->id)
                            ->exists();
                        if ($exists) {
                            $fail('Este número de cuenta ya está registrado para esta razón social.');
                        }
                    }
                }
            ],
            'clabe' => ['nullable','digits:18',
                function ($attribute, $value, $fail) use ($request, $bankAccount) {
                    if ($value && $request->partner_entity_id) {
                        $exists = PartnerEntityBankAccount::where('partner_entity_id', $request->partner_entity_id)
                            ->where('clabe', $value)
                            ->where('id', '!=', $bankAccount->id)
                            ->exists();
                        if ($exists) {
                            $fail('Esta CLABE ya está registrada para esta razón social.');
                        }
                    }
                }
            ],
            'swift'          => ['nullable','string','max:20'],
            'iban'           => ['nullable','string','max:34'],
            'currency'       => ['nullable','string','size:3'],
            'is_default'     => ['sometimes','boolean'],
        ];

        $data = $request->validate($rules);
        $data['currency'] = strtoupper($data['currency'] ?? $bankAccount->currency ?? 'MXN');

        if ($data['partner_entity_id'] != $bankAccount->partner_entity_id) {
            $data['is_default'] = false;
        }

        if ($request->boolean('is_default')) {
            $entity = PartnerEntity::findOrFail($data['partner_entity_id']);
            $entity->bankAccounts()
                ->where('id', '!=', $bankAccount->id)
                ->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        try {
            $bankAccount->update($data);

            return redirect()
                ->route('my-bank-accounts.index')
                ->with('success', 'Cuenta bancaria actualizada exitosamente.');
                
        } catch (\Exception $e) {
            \Log::error('Error updating bank account', [
                'error' => $e->getMessage(),
                'bank_account_id' => $bankAccount->id
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la cuenta bancaria. Por favor verifica los datos.');
        }
    }

    /**
     * Eliminar cuenta bancaria (asociado - solo Asociado Administrador)
     */
    public function myDestroy($id)
    {
        $user = Auth::user();
        $bankAccount = PartnerEntityBankAccount::findOrFail($id);
        
        // Verificar que la cuenta pertenezca a una entidad del partner del usuario
        if ($bankAccount->entity->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para eliminar esta cuenta bancaria.');
        }

        $entity = $bankAccount->entity;
        $wasDefault = (bool) $bankAccount->is_default;

        $bankAccount->delete();

        // Si borraste la principal, marca otra
        if ($wasDefault && $entity->bankAccounts()->exists()) {
            $entity->bankAccounts()->first()->update(['is_default' => true]);
        }

        return redirect()
            ->route('my-bank-accounts.index')
            ->with('success', 'Cuenta bancaria eliminada exitosamente.');
    }
}