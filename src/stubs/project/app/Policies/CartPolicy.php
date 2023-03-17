<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\User;

class CartPolicy
{
    public function before(User $user, $ability) {
        // @HOOK_POLICY_BEFORE
        if($user->hasRole('Super Admin', 'admin') )
            return true;
    }

    public function view(User $user) {
        // @HOOK_POLICY_VIEW
        return $user->hasPermissionTo('orders.view', request()->whereIam());
    }

    public function create(User $user) {
        // @HOOK_POLICY_CREATE
        return $user->hasPermissionTo('order.create', request()->whereIam());
    }

    public function update(User $user, Cart $chOrder) {
        // @HOOK_POLICY_UPDATE
        if( !$user->hasPermissionTo('order.update', request()->whereIam()) )
            return false;
        return true;
    }

    public function delete(User $user, Cart $chOrder) {
        // @HOOK_POLICY_DELETE
        if( !$user->hasPermissionTo('order.delete', request()->whereIam()) )
            return false;
        return true;
    }

    // @HOOK_POLICY_END


}
