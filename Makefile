PHP ?= php

.PHONY: all check test phpstan cs-fixer cs-fix clear-stan

all: check

## Lance toutes les vérifications (phpstan + cs-fixer dry-run + phpunit)
check: phpstan cs-fixer test

## Tests PHPUnit
test:
	$(PHP) vendor/bin/phpunit

## Analyse statique PHPStan (level 8, cf. phpstan.neon)
phpstan:
	$(PHP) vendor/bin/phpstan analyse --no-progress

## Vérification du style sans modification (comme la CI)
cs-fixer:
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run --diff

## Corrige automatiquement le style
cs-fix:
	$(PHP) vendor/bin/php-cs-fixer fix

## Vide le cache PHPStan (résultats incohérents)
clear-stan:
	$(PHP) vendor/bin/phpstan clear-result-cache
