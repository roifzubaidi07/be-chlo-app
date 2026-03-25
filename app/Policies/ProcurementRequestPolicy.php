<?php

namespace App\Policies;

use App\Models\ProcurementRequest;
use App\Models\User;

class ProcurementRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProcurementRequest $procurementRequest): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['requester', 'purchasing', 'approver', 'warehouse'], true);
    }
}
