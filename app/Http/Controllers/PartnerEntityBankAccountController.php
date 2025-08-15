<?php

namespace App\Http\Controllers;

use App\Models\PartnerEntity;
use App\Models\PartnerEntityBankAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerEntityBankAccountController extends Controller
{
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

        // Normaliza moneda
        $data['currency'] = strtoupper($data['currency'] ?? 'MXN');

        // Solo una como principal por razón social
        if ($request->boolean('is_default')) {
            $partner_entity->bankAccounts()->update(['is_default' => false]);
            $data['is_default'] = true;
        } elseif ($partner_entity->bankAccounts()->count() === 0) {
            $data['is_default'] = true; // la primera queda principal
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

        // Si borraste la principal, marca otra
        if ($wasDefault && $entity->bankAccounts()->exists()) {
            $entity->bankAccounts()->first()->update(['is_default' => true]);
        }

        return redirect()
            ->route('partner-entities.bank-accounts.index', $entity)
            ->with('success', 'Cuenta bancaria eliminada.');
    }
}
