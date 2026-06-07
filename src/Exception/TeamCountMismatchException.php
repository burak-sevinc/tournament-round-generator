<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Exception;

use InvalidArgumentException;

class TeamCountMismatchException extends InvalidArgumentException
{
    public static function create(int $expected, int $actual): self
    {
        return new self(sprintf('Expected %d teams, got %d.', $expected, $actual));
    }
}
