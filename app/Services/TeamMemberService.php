<?php

namespace App\Services;

use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TeamMemberService
{
    public function __construct(
        private TeamMemberRepositoryInterface $teamMemberRepository
    ) {}

    /**
     * Get paginated team members
     */
    public function getPaginated(int $perPage = 15, array $withRelations = []): LengthAwarePaginator
    {
        return $this->teamMemberRepository->paginate($perPage, ['*'], $withRelations);
    }

    /**
     * Get all team members
     */
    public function getAll(array $withRelations = []): Collection
    {
        return $this->teamMemberRepository->all(['*'], $withRelations);
    }

    /**
     * Get team member by ID
     */
    public function getById(int $id, array $withRelations = []): ?TeamMember
    {
        return $this->teamMemberRepository->find($id, ['*'], $withRelations);
    }

    /**
     * Get active team members
     */
    public function getActive(): Collection
    {
        return $this->teamMemberRepository->getActive();
    }

    /**
     * Get team members by role
     */
    public function getByRole(string $role): Collection
    {
        return $this->teamMemberRepository->getByRole($role);
    }

    /**
     * Find by email
     */
    public function findByEmail(string $email): ?TeamMember
    {
        return $this->teamMemberRepository->findByEmail($email);
    }

    /**
     * Create new team member
     */
    public function create(array $data): TeamMember
    {
        return $this->teamMemberRepository->create($data);
    }

    /**
     * Update team member
     */
    public function update(int $id, array $data): bool
    {
        return $this->teamMemberRepository->update($id, $data);
    }

    /**
     * Delete team member
     */
    public function delete(int $id): bool
    {
        return $this->teamMemberRepository->delete($id);
    }

    /**
     * Get team members with their events
     */
    public function getWithEvents(): Collection
    {
        return $this->teamMemberRepository->getWithEvents();
    }
}

