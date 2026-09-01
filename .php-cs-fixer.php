<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude(__DIR__ . '/vendor')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP83Migration' => true,
        '@Symfony' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'php_unit_strict' => true,
        'phpdoc_order' => true,
        'single_line_throw' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
