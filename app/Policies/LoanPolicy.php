<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool { return true; }

    public function create(User $user): bool {
        return $user->hasPermissionTo('realizar prestamos');
    }

    public function update(User $user, Loan $loan): bool
    {
        return $user->id === $loan->user_id || $user->hasRole('bibliotecario');
    }
}