<?php

namespace App\Repositories;

use App\Interfaces\IUserProfileRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserProfileRepository implements IUserProfileRepository
{
    public function getUserProfile(int $userId): ?User
    {
        return User::find($userId);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->update($data);
    }

    public function changePassword(int $userId, string $newPassword): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->update(['password' => Hash::make($newPassword)]);
    }
}
