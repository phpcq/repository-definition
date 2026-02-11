<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\Class_\AddTestsVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withRules([
        FinalizeTestCaseClassRector::class,
        AddTestsVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_81,
    ]);
