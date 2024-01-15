
# Tournament Round Generator


[![Packagist](https://img.shields.io/packagist/v/burak-sevinc/tournament-round-generator)](https://packagist.org/packages/burak-sevinc/tournament-round-generator)
[![Licence](https://img.shields.io/packagist/l/burak-sevinc/tournament-round-generator)](https://packagist.org/packages/burak-sevinc/tournament-round-generator)

A PHP library for generating tournament rounds with helpful calculations

## Getting Started


  ```sh
  composer require burak-sevinc/tournament-round-generator
  ```


## Usage

Single elimination examples:

```php
use BurakSevinc\TournamentRoundGenerator\SingleElimination;
```
```php
$teamCount         = 8;
$singleElimination = new SingleElimination($teamCount);

$roundCount      = $singleElimination->getRoundCount();
$roundsArr       = $singleElimination->getRounds();
$matches         = $singleElimination->getMatches();
$roundOne        = $singleElimination->getMatchesByRoundId(1);
$withNextMatchId = $singleElimination->addNextMatchId();
```


## Roadmap

- [x]   Single elimination
- [ ]   Double elimination

## Contact

Burak Sevinç - [@buraksevincdev](https://twitter.com/buraksevincdev) - info@buraksevinc.dev