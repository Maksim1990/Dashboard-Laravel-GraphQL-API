<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $this->isUserOrAdmin($user);
    }

    public function useCache(User $user): bool
    {
        return $this->isUserOrAdmin($user);
    }

    public function adminAction(User $user): bool
    {
        dump($user);
        return $user->role === UserRoles::ADMIN;
    }

    private function isUserOrAdmin(User $user): bool
    {
        return $user->role === UserRoles::ADMIN || $user->role === UserRoles::USER;
    }
}
