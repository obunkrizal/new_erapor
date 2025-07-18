<?php

namespace App\Policies;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KelasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isGuru();
    }

    public function view(User $user, Kelas $kelas): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGuru()) {
            return $kelas->guru_id === $user->guru?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Kelas $kelas): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Kelas $kelas): bool
    {
        return $user->isAdmin();
    }
}
