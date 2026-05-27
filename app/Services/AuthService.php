<?php

namespace App\Services;

use App\Interfaces\IUserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private IUserRepository $userRepository
    ) {}

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        if (!in_array($data['role'] ?? '', User::roles(), true)) {
            $data['role'] = User::ROLE_EXTERNO;
        }

        return $this->userRepository->create($data);
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function loginUser(User $user): void
    {
        auth()->login($user);
    }

    public function logoutUser(): void
    {
        auth()->logout();
    }
}
