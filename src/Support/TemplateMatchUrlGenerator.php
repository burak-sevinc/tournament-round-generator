<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Support;

use BurakSevinc\TournamentRoundGenerator\Contract\MatchUrlGenerator;
use BurakSevinc\TournamentRoundGenerator\TournamentMatch;

class TemplateMatchUrlGenerator implements MatchUrlGenerator
{
    private string $template;

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    public function generate(TournamentMatch $match): string
    {
        return str_replace(
            ['{matchId}', '{roundId}'],
            [(string) $match->getMatchId(), (string) $match->getRoundId()],
            $this->template
        );
    }
}
