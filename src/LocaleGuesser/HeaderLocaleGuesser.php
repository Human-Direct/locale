<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

class HeaderLocaleGuesser extends AbstractLocaleGuesser
{
    private const HEADER_ATTR_NAME = 'X-LOCALE';

    public function __construct(private readonly MetaValidator $metaValidator)
    {
    }

    public function guess(Request $request): bool
    {
        if (!$request->headers->has(self::HEADER_ATTR_NAME)) {
            return false;
        }

        $locale = $request->headers->get(self::HEADER_ATTR_NAME);
        if (!\is_string($locale) || !$this->metaValidator->isAllowed($locale)) {
            return false;
        }

        $this->identifiedLocale = $locale;

        return true;
    }
}
