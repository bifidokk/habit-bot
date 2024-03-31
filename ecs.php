<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
use Symplify\EasyCodingStandard\Config\ECSConfig;


return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withRules([
        NoUnusedImportsFixer::class,
    ])
    ->withConfiguredRule(
        ArraySyntaxFixer::class, [
            'syntax' => 'short',
        ]
    )
    ->withConfiguredRule(
        ConcatSpaceFixer::class, [
            'spacing' => 'none',
        ]
    )
    ->withConfiguredRule(
        YodaStyleFixer::class, [
            'equal' => false,
            'identical' => false,
            'always_move_variable' => false,
        ]
    )
    ->withConfiguredRule(
        BlankLineBeforeStatementFixer::class, [
            'statements' => [
                'case',
                'continue',
                'declare',
                'default',
                'do',
                'exit',
                'for',
                'foreach',
                'if',
                'switch',
                'throw',
                'try',
                'while',
                'yield',
            ]
        ]
    )
    ->withSets(
        [
            SetList::SPACES,
            SetList::ARRAY,
            SetList::DOCBLOCK,
            SetList::NAMESPACES,
            SetList::CONTROL_STRUCTURES,
            SetList::CLEAN_CODE,
            SetList::PSR_12,
            SetList::DOCTRINE_ANNOTATIONS,
            SetList::STRICT,
            SetList::PHPUNIT,
            SetList::COMMON,
        ]
    )
;
