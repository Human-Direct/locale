<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LocaleAllowed extends Constraint
{
    public string $message = 'The locale "%string%" is not allowed by application configuration.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'human_direct_locale.validator.locale_allowed';
    }
}
