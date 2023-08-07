<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleInformation;

/**
 * @author Jachim Coudenys <jachimcoudenys@gmail.com>
 */
class DomainLocaleMap
{
    /**
     * @param array<string, string|null> $domainLocaleMap eg. [version.tld => locale, sub.version2.tld => locale]
     */
    public function __construct(private readonly array $domainLocaleMap = [])
    {
    }

    /**
     * Get the locale for a given domain.
     */
    public function getLocale(string $domain): ?string
    {
        if (isset($this->domainLocaleMap[$domain]) && $this->domainLocaleMap[$domain]) {
            return $this->domainLocaleMap[$domain];
        }

        return null;
    }
}
