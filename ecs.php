<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([
        SetList::PSR_12,
        SetList::SYMPLIFY,
        SetList::COMMON,
        SetList::CLEAN_CODE
    ]);

    $ecsConfig->rules([
        GlobalNamespaceImportFixer::class,
        NoLeadingImportSlashFixer::class,
        FullyQualifiedStrictTypesFixer::class,
    ]);
};
