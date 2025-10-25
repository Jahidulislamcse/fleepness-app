<?php

use Rector\Config\RectorConfig;
use RectorLaravel\Set\LaravelSetList;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.\DIRECTORY_SEPARATOR.'app',
        __DIR__.\DIRECTORY_SEPARATOR.'bootstrap',
        __DIR__.\DIRECTORY_SEPARATOR.'config',
        __DIR__.\DIRECTORY_SEPARATOR.'public',
        __DIR__.\DIRECTORY_SEPARATOR.'resources',
        __DIR__.\DIRECTORY_SEPARATOR.'routes',
        __DIR__.\DIRECTORY_SEPARATOR.'tests',
    ])

    // here we can define, what prepared sets of rules will be applied
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withSkip([
        __DIR__.\DIRECTORY_SEPARATOR.'ide-helper.php',
        __DIR__.\DIRECTORY_SEPARATOR.'_ide_helper.php',
        __DIR__.\DIRECTORY_SEPARATOR.'_ide_helper_models.php',
        __DIR__.\DIRECTORY_SEPARATOR.'_ide_helper_intelephense.php',
        __DIR__.\DIRECTORY_SEPARATOR.'.phpstorm.meta.php',
        __DIR__.\DIRECTORY_SEPARATOR.'rector.php',

    ])
    ->withPhpSets()
    ->withAttributesSets()
    ->withSetProviders(LaravelSetProvider::class)
    ->withComposerBased(laravel: true/** other options */)
    ->withSets([
        LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
        LaravelSetList::LARAVEL_FACTORIES,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_LEGACY_FACTORIES_TO_CLASSES,
        LaravelSetList::LARAVEL_TESTING,
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
    ]);
