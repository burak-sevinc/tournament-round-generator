<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Exception;

use InvalidArgumentException;

class InvalidTeamCountException extends InvalidArgumentException
{
    public static function notPowerOfTwo(int $teamCount): self
    {
        return new self(sprintf('Team count must be a power of 2. Got: %d', $teamCount));
    }
}
