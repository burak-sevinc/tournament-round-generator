
# Tournament Round Generator


[![Packagist](https://img.shields.io/packagist/v/burak-sevinc/tournament-round-generator)](https://packagist.org/packages/burak-sevinc/tournament-round-generator)
[![Licence](https://img.shields.io/packagist/l/burak-sevinc/tournament-round-generator)](https://packagist.org/packages/burak-sevinc/tournament-round-generator)

A PHP library for generating tournament rounds with helpful calculations

## Getting Started


  ```sh
  composer require burak-sevinc/tournament-round-generator
  ```


## Usage

### Basic bracket skeleton

```php
use BurakSevinc\TournamentRoundGenerator\SingleElimination;

$singleElimination = new SingleElimination(8);

$roundCount = $singleElimination->getRoundCount();
$roundsArr = $singleElimination->getRounds();
$matches = $singleElimination->getMatches();
$roundOne = $singleElimination->getMatchesByRoundId(1);
```

### Teams, members, and seeding

```php
use BurakSevinc\TournamentRoundGenerator\Member;
use BurakSevinc\TournamentRoundGenerator\SingleElimination;
use BurakSevinc\TournamentRoundGenerator\Team;

$teams = [
    new Team(1, 'Alpha', 1, [new Member(101, 'Player A')]),
    new Team(2, 'Beta', 2, [new Member(102, 'Player B')]),
    // ...
];

$bracket = SingleElimination::fromTeams($teams);
// or
$bracket = (new SingleElimination(8))->setTeams($teams);

$team = $bracket->getTeamById(1);
$members = $bracket->getMembersByTeamId(1);
$teamsInMatch = $bracket->getTeamsInMatch(1);
```

### Bracket links for frontend line drawing

```php
$bracket->linkMatches();

$match = $bracket->getMatchById(5);
$match->getPrevMatchIds(); // [1, 2]
$match->getNextMatchId();  // 7

$feeders = $bracket->getFeederMatches(5);
$nextMatch = $bracket->getNextMatch(5);
```

Each match array includes `positionInRound`, `prevMatchIds`, and `nextMatchId` so a frontend can draw bracket connectors.

### Match URLs

```php
use BurakSevinc\TournamentRoundGenerator\Support\TemplateMatchUrlGenerator;

$bracket
    ->setMatchUrlGenerator(new TemplateMatchUrlGenerator('/tournament/match/{matchId}'))
    ->applyMatchUrls();

$url = $bracket->getMatchById(1)?->getUrl();
```

## Roadmap

- [x]   Single elimination
- [ ]   Double elimination
- [ ]   Round Robin

## Contact

Burak Sevinç - [X](https://twitter.com/buraksevincdev) - [LinkedIn](https://www.linkedin.com/in/buraksevinc-dev/) - info@buraksevinc.dev
