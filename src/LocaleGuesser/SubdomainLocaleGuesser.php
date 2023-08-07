<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Locale Guesser for detecting the locale from the subdomain.
 *
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class SubdomainLocaleGuesser extends AbstractLocaleGuesser
{
    public function __construct(
        private readonly MetaValidator $metaValidator,
        private readonly ?string $regionSeparator = '_'
    ) {
    }

    /**
     * Guess the locale based on the subdomain.
     */
    public function guess(Request $request): bool
    {
        $subdomain = mb_strstr($request->getHost(), '.', true);
        if (\is_string($subdomain)
            && $this->regionSeparator
            && '_' !== $this->regionSeparator) {
            $subdomain = str_replace($this->regionSeparator, '_', $subdomain);
        }

        if (false !== $subdomain && $this->metaValidator->isAllowed($subdomain)) {
            /** @var string $subdomain */
            $this->identifiedLocale = $subdomain;

            return true;
        }

        return false;
    }
}
