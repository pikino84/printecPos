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
        // Si NO es producto propio, todos pueden verlo
        if (!$product->is_own_product) {
            return true;
        }
        
        // Si ES producto propio, verificar permisos
        if (!$user->can('view-own-products') && !$user->can('manage-own-products')) {
            return false;
        }
        
        // Printec (partner_id = 1) puede ver TODOS los productos propios
        if ($user->partner_id === 1) {
            return true;
        }
        
        // Los demás asociados solo pueden ver SUS propios productos
        return $product->partner_id === $user->partner_id;
    }

    public function create(User $user): bool
    {
        return $user->can('manage-own-products');
    }

    public function update(User $user, Product $product): bool
    {
        if (!$product->is_own_product) {
            return false;
        }
        
        if (!$user->can('manage-own-products')) {
            return false;
        }
        
        if ($user->partner_id === 1) {
            return true;
        }
        
        return $product->partner_id === $user->partner_id;
    }

    public function delete(User $user, Product $product): bool
    {
        if (!$product->is_own_product) {
            return false;
        }
        
        if (!$user->can('manage-own-products')) {
            return false;
        }
        
        if ($user->partner_id === 1) {
            return true;
        }
        
        return $product->partner_id === $user->partner_id;
    }
}