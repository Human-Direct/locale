<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleInformation\DomainLocaleMap;
use HumanDirect\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Locale Guesser for detecting the locale from the domain
 *
 * @author Jachim Coudenys <jachimcoudenys@gmail.com>
 */
class DomainLocaleGuesser extends AbstractLocaleGuesser
{
    public function __construct(
        private readonly MetaValidator $metaValidator,
        private readonly DomainLocaleMap $domainLocaleMap
    ) {
    }

    public function guess(Request $request): bool
    {
        $domainParts = array_reverse(explode('.', $request->getHost()));

        $domain = null;
        foreach ($domainParts as $domainPart) {
            if (!$domain) {
                $domain = $domainPart;
            } else {
                $domain = $domainPart . '.' . $domain;
            }

            $locale = $this->domainLocaleMap->getLocale($domain);
            if ($locale && $this->metaValidator->isAllowed($locale)) {
                $this->identifiedLocale = $locale;

                return true;
            }
        }

        return false;
    }
}
