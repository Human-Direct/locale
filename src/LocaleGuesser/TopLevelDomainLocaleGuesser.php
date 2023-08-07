<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleInformation\TopLevelDomainLocaleMap;
use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Locale Guesser for detecting the locale from the toplevel domain.
 *
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class TopLevelDomainLocaleGuesser extends AbstractLocaleGuesser
{
    public function __construct(
        private readonly MetaValidator $metaValidator,
        private readonly TopLevelDomainLocaleMap $localeMap
    ) {
    }

    /**
     * Guess the locale based on the TLD.
     */
    public function guess(Request $request): bool
    {
        $substring = mb_strrchr($request->getHost(), '.');
        $tld = false !== $substring ? mb_substr($substring, 1) : '';
        $locale = $tld;

        // see if we have some additional mappings
        if ($tld && $this->localeMap->getLocale($tld)) {
            $locale = $this->localeMap->getLocale($tld);
        }

        if (\is_string($locale) && $this->metaValidator->isAllowed($locale)) {
            $this->identifiedLocale = $locale;

            return true;
        }

        return false;
    }
}
