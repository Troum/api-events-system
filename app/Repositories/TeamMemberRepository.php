<?php

namespace App\Repositories;

use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TeamMemberRepository extends BaseRepository implements TeamMemberRepositoryInterface
{
    public function __construct(TeamMember $model)
    {
        $this->model = $model;
    }

    public function getWithEvents(): Collection
    {
        return $this->model->with('events')->get();
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function findByEmail(string $email): ?TeamMember
    {
        return $this->model->where('email', $email)->first();
    }

    public function getByRole(string $role): Collection
    {
        return $this->model->where('role', $role)->get();
    }
}

