<?php

namespace App\Repositories\Contracts;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Collection;

interface TeamMemberRepositoryInterface extends RepositoryInterface
{
    /**
     * Get team members with their events
     */
    public function getWithEvents(): Collection;

    /**
     * Get active team members
     */
    public function getActive(): Collection;

    /**
     * Find by email
     */
    public function findByEmail(string $email): ?TeamMember;

    /**
     * Get team members by role
     */
    public function getByRole(string $role): Collection;
}

