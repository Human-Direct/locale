<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This MetaValidator uses the LocaleAllowed and Locale validators for checks inside a guesser.
 */
class MetaValidator
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    /**
     * Checks if a locale is allowed and valid.
     */
    public function isAllowed(string $locale): bool
    {
        $errorListLocale = $this->validator->validate($locale, new Locale());
        $errorListLocaleAllowed = $this->validator->validate($locale, new LocaleAllowed());

        return !$errorListLocale->count() && !$errorListLocaleAllowed->count();
    }
}
