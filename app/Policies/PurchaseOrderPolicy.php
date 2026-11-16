<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Who may do what with purchase orders.
 *
 *   admin      everything
 *   manager    create, submit, approve (orders below the admin threshold), send, cancel
 *   staff      create and edit their own drafts
 *   warehouse  receive goods
 */
class PurchaseOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasRole(...User::ROLES);
    }

    public function view(User $user, PurchaseOrder $purchaseOrder)
    {
        return $user->hasRole(...User::ROLES);
    }

    public function create(User $user)
    {
        return $user->hasRole('admin', 'manager', 'staff');
    }

    /** Only draft orders, and only by their creator or an admin. */
    public function update(User $user, PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder->canBeEdited()
            && ($user->isAdmin() || (int) $purchaseOrder->created_by === (int) $user->id);
    }

    /** Move a draft into the approval queue. */
    public function submit(User $user, PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder->canBeSubmitted()
            && ($user->hasRole('admin', 'manager') || (int) $purchaseOrder->created_by === (int) $user->id);
    }

    /** Only draft orders, and only by an admin. */
    public function delete(User $user, PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder->status === PurchaseOrder::DRAFT && $user->isAdmin();
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder->canBeCancelled() && $user->hasRole('admin', 'manager');
    }

    /**
     * Managers and admins can approve/reject pending orders.
     * Multi-level rule: orders at or above the configured threshold need an administrator.
     */
    public function approve(User $user, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeApproved() || !$user->hasRole('admin', 'manager')) {
            return false;
        }
        if ($purchaseOrder->requiresAdminApproval() && !$user->isAdmin()) {
            return $this->deny(sprintf(
                'Orders of %s%s or more require administrator approval.',
                config('procurement.currency'),
                number_format(config('procurement.admin_approval_threshold'))
            ));
        }

        return true;
    }

    public function send(User $user, PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder->canBeSent() && $user->hasRole('admin', 'manager');
    }

    /** Warehouse staff and admins record goods received. */
    public function receive(User $user, PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder->canBeReceived() && $user->hasRole('admin', 'warehouse');
    }
}
