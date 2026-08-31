<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function countCustomers(): int;

    /**
     * @param array $attributes the credentials of the user
     */
    public function create(array $attributes): User;

    public function findByGoogleIdOrEmail(string $id, string $email): ?User;

    /**
     * all customers with count of their total and expired warranties and a last inquiry status
     */
    public function paginateCustomers(array $filters): LengthAwarePaginator;

    public function getStaffAndTechnicians(array $filters): Collection;
    
    public function createFromGoogle(string $name, string $email, string $googleId): User;

    public function linkGoogleAccount(User $user, string $googleId): User;

    public function findByEmail(string $email): ?User;

    public function updateRole(User $user, string $role): User;

    public function delete(User $user): bool;
}
