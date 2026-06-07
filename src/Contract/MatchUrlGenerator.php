<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Contract;

use BurakSevinc\TournamentRoundGenerator\TournamentMatch;

interface MatchUrlGenerator
{
    public function generate(TournamentMatch $match): string;
}
