<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use Laravel\Jetstream\Contracts\DeletesTeams;

class DeleteTeam implements DeletesTeams
{
    public function delete(Team $team): void
    {
        $team->purge();
    }
}
