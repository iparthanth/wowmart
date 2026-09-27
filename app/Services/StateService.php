<?php

namespace App\Services;

use App\Models\State;
use Illuminate\Support\Facades\DB;

/** Every write to a shipping state. Input is validated already (StateRequest). */
class StateService
{
    public function create(array $data): State
    {
        return DB::transaction(fn () => State::create($data));
    }

    public function update(State $state, array $data): State
    {
        return DB::transaction(fn () => tap($state)->update($data));
    }

    /** @throws \DomainException while the state still has cities below it */
    public function delete(State $state): void
    {
        $cities = $state->cities()->count();

        if ($cities > 0) {
            throw new \DomainException(sprintf(
                '%s still has %s below it. Delete them first.',
                $state->name,
                $cities === 1 ? '1 city' : $cities.' cities',
            ));
        }

        $state->delete();
    }
}
