<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Locale extends Constraint
{
    public string $message = 'The locale "%string%" is not a valid locale';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'human_direct_locale.validator.locale';
    }
}
