<?php

namespace App\Domain\Catalog\Actions;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * The exact function whitelist `GenerateRequirements` evaluates
 * `condition_expr`/`quantity_expression` against — extracted so the admin
 * requirement-template editor validates against the same whitelist rather
 * than a second, potentially-drifting guess at it. Never add function
 * access beyond this whitelist (architecture §6.2: not eval()).
 */
class BuildRequirementExpressionLanguage
{
    public function __invoke(): ExpressionLanguage
    {
        $language = new ExpressionLanguage();

        foreach (['ceil', 'floor', 'round', 'min', 'max'] as $function) {
            $language->addFunction(ExpressionFunction::fromPhp($function));
        }

        return $language;
    }
}
