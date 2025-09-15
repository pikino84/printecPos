<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-own-products') || $user->can('manage-own-products');
    }

    public function view(User $user, Product $product): bool
    {
        // Si es producto propio, usar la lógica existente
        if ($product->is_own_product) {
            return $product->canBeViewedBy($user);
        }
        
        // Si es producto de proveedor, todos pueden verlo
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('manage-own-products');
    }

    public function update(User $user, Product $product): bool
    {
        if (!$product->is_own_product) {
            return false; // Solo productos propios pueden editarse aquí
        }
        
        return $user->can('manage-own-products') && (
            $product->partner_id === $user->partner_id || 
            $user->partner_id === 1 // Printec puede editar todo
        );
    }

    public function delete(User $user, Product $product): bool
    {
        if (!$product->is_own_product) {
            return false;
        }
        
        return $user->can('manage-own-products') && (
            $product->partner_id === $user->partner_id || 
            $user->partner_id === 1
        );
    }
}