<?php

namespace App\Services;

use App\Interfaces\IUserProfileRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserProfileService
{
    public function __construct(
        private IUserProfileRepository $userProfileRepository
    ) {}

    public function getProfile(int $userId): ?User
    {
        return $this->userProfileRepository->getUserProfile($userId);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        return $this->userProfileRepository->updateProfile($userId, $data);
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userProfileRepository->getUserProfile($userId);

        if (!$user || !Hash::check($currentPassword, $user->password)) {
            return false;
        }

        return $this->userProfileRepository->changePassword($userId, $newPassword);
    }
}
