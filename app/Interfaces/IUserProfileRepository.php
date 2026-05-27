<?php

namespace App\Interfaces;

use App\Models\User;

interface IUserProfileRepository
{
    public function getUserProfile(int $userId): ?User;

    public function updateProfile(int $userId, array $data): bool;

    public function changePassword(int $userId, string $newPassword): bool;
}
