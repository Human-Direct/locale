<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\LocaleInformation;

/**
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class TopLevelDomainLocaleMap
{
    /**
     * @param array<string, string|null> $tldLocaleMap eg. [tld => locale]
     */
    public function __construct(private readonly array $tldLocaleMap = [])
    {
    }

    public function getLocale(string $tld): bool|string|null
    {
        if (isset($this->tldLocaleMap[$tld]) && $this->tldLocaleMap[$tld]) {
            return $this->tldLocaleMap[$tld];
        }

        return false;
    }
}
